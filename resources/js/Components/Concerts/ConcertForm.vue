<script setup>
import { ref, inject, computed, watch, nextTick, onMounted } from "vue";
import DropdownSelector from "@/Components/DropdownSelector.vue";
import DatePicker from "@/Components/DatePicker.vue";
import TimePicker from "@/Components/TimePicker.vue";
import AnimatedRangeInput from "@/Components/AnimatedRangeInput.vue";
import ProvinceDropdown from "@/Components/ProvinceDropdown.vue";
import ArtistDropdown from "@/Components/ArtistDropdown.vue";
import {
    XMarkIcon,
    HeartIcon,
    LinkIcon,
    PlusIcon,
    TicketIcon,
    CalendarIcon,
    ClockIcon,
    BanknotesIcon,
    MapPinIcon,
    MapIcon,
    FolderArrowDownIcon,
} from "@heroicons/vue/24/outline";
import { ArrowTurnLeftUpIcon } from "@heroicons/vue/24/solid";
import L from "leaflet";
import "leaflet/dist/leaflet.css";

const props = defineProps({
    form: { type: Object, required: true },
    artists: { type: Array, required: true, default: () => [] },
    concert: { type: Object, default: null },
});

const emit = defineEmits(["submit"]);
const isDarkMode = inject("isDarkMode");

const isEditMode = computed(() => !!props.concert);

const eventTypes = [
    { value: "music_festival", name: "Music Festival" },
    { value: "concert", name: "Concert" },
    { value: "club", name: "Club / Pub" },
    { value: "fan_meeting", name: "Fan Meeting" },
    { value: "folk", name: "Folk Music" },
    { value: "other", name: "Other" },
];

const genres = [
    { value: "pop", name: "Pop" },
    { value: "rock", name: "Rock" },
    { value: "hiphop", name: "Hip Hop" },
    { value: "jazz", name: "Jazz" },
    { value: "classical", name: "Classical" },
    { value: "country", name: "Country" },
    { value: "edm", name: "EDM" },
    { value: "other", name: "Other" },
];

const fieldLabels = {
    name: "Concert Name",
    event_type: "Event Type",
    genre: "Genre",
    ticket_link: "Ticket Link",
    start_sale_date: "Start Sale Date",
    end_sale_date: "End Sale Date",
    start_show_date: "Start Show Date",
    end_show_date: "End Show Date",
    start_show_time: "Start Show Time",
    end_show_time: "End Show Time",
    price_min: "Minimum Price",
    price_max: "Maximum Price",
    latitude: "Latitude",
    longitude: "Longitude",
    province_id: "Province",
    venue_name: "Venue Name",
    artist_ids: "Artists",
    description: "Description",
    picture_url: "Picture",
};

const errorSummary = ref(null);

const submit = () => {
    emit("submit");
};

watch(
    () => props.form.errors,
    (newErrors) => {
        if (newErrors && Object.keys(newErrors).length > 0) {
            nextTick(() => {
                if (errorSummary.value) {
                    errorSummary.value.scrollIntoView({
                        behavior: "smooth",
                        block: "start",
                    });
                }
            });
        }
    },
    { deep: true },
);

const allProvinces = ref([]);

onMounted(() => {
    fetch("/api/provinces")
        .then((response) => {
            if (!response.ok) {
                throw new Error("Network response was not ok");
            }
            return response.json();
        })
        .then((data) => {
            allProvinces.value = data;
            if (props.form.province_id) {
                const initialProvince = allProvinces.value.find(
                    (p) => p.id === props.form.province_id,
                );
                if (initialProvince)
                    selectedProvinceName.value = initialProvince.name_th;
            }
        })
        .catch((error) => console.error("Error fetching provinces:", error));
});

const uploadedPhotoUrl = ref(null);
const photoInput = ref(null);

const initialImageUrl = computed(() => {
    if (props.concert && props.concert.picture_url) {
        return props.concert.picture_url.startsWith("http")
            ? props.concert.picture_url
            : `/storage/${props.concert.picture_url}`;
    }
    return null;
});

const photoPreview = computed(() => {
    if (uploadedPhotoUrl.value) return uploadedPhotoUrl.value;
    if (props.form.picture_url && typeof props.form.picture_url !== "string") {
        return URL.createObjectURL(props.form.picture_url);
    }
    if (initialImageUrl.value) return initialImageUrl.value;

    return isDarkMode.value
        ? "https://placehold.co/800x1200/1c1423/ffffff80?text=Upload%5CnPicture"
        : "https://placehold.co/800x1200/e5e7eb/00000080?text=Upload%5CnPicture";
});

const selectNewPhoto = () => photoInput.value.click();

const updatePhotoPreview = (event) => {
    const file = event.target.files[0];
    if (!file) return;
    props.form.picture_url = file;
    const reader = new FileReader();
    reader.onload = (e) => {
        uploadedPhotoUrl.value = e.target.result;
    };
    reader.readAsDataURL(file);
};

const tempArtistId = ref(null);

const getArtistPicture = computed(() => {
    return (artist) => {
        if (artist && artist.picture_url) {
            if (artist.picture_url.startsWith("http")) {
                return artist.picture_url;
            }
            return `/storage/${artist.picture_url}`;
        }

        const name = artist?.name || "Artist";
        return isDarkMode.value
            ? `https://ui-avatars.com/api/?name=${encodeURIComponent(
                name,
            )}&background=ff1493&color=ffffff`
            : `https://ui-avatars.com/api/?name=${encodeURIComponent(
                name,
            )}&background=008be6&color=ffffff`;
    };
});

const selectedArtists = computed(() => {
    if (!Array.isArray(props.form.artist_ids) || !Array.isArray(props.artists))
        return [];
    return props.form.artist_ids
        .map((id) => props.artists.find((artist) => artist.id === id))
        .filter(Boolean);
});

const availableArtists = computed(() => {
    if (!Array.isArray(props.artists)) return [];
    const selectedIds = Array.isArray(props.form.artist_ids)
        ? props.form.artist_ids
        : [];

    return props.artists
        .filter((artist) => !selectedIds.includes(artist.id))
        .map((artist) => ({
            name: artist.name,
            value: artist.id,
            picture_url:
                artist.picture_url && artist.picture_url.startsWith("http")
                    ? artist.picture_url
                    : artist.picture_url
                        ? `/storage/${artist.picture_url}`
                        : getArtistPicture.value(artist),
        }));
});

watch(tempArtistId, (newId) => {
    if (newId) {
        if (!Array.isArray(props.form.artist_ids)) props.form.artist_ids = [];
        if (!props.form.artist_ids.includes(newId))
            props.form.artist_ids.push(newId);
    }
    nextTick(() => {
        tempArtistId.value = null;
    });
});

function removeArtist(artistId) {
    if (!Array.isArray(props.form.artist_ids)) return;
    props.form.artist_ids = props.form.artist_ids.filter(
        (id) => id !== artistId,
    );
}

const selectedProvinceName = ref(null);
const showMapModal = ref(false);
const mapInstance = ref(null);
const selectedLocation = ref(null);
const mapMarker = ref(null);
const isGeocoding = ref(false);

const locationMode = ref(
    props.form.latitude && props.form.longitude ? "map" : "manual",
);

const selectedCoordinates = computed(() => {
    if (props.form.latitude && props.form.longitude) {
        return `${Number(props.form.latitude).toFixed(6)}, ${Number(props.form.longitude).toFixed(6)}`;
    }
    return null;
});

const modalSelectedCoordinates = computed(() => {
    if (selectedLocation.value) {
        return `${selectedLocation.value.lat.toFixed(6)}, ${selectedLocation.value.lng.toFixed(6)}`;
    }
    return null;
});

function toggleLocationMode() {
    locationMode.value = locationMode.value === "manual" ? "map" : "manual";
    if (locationMode.value === "manual") {
        props.form.latitude = null;
        props.form.longitude = null;
    } else {
        props.form.province_id = null;
        props.form.venue_name = null;
    }
}

const openMap = () => {
    showMapModal.value = true;
    selectedLocation.value = null;
    selectedProvinceName.value = null;

    if (props.form.province_id && allProvinces.value.length > 0) {
        const initialProvince = allProvinces.value.find(
            (p) => p.id === props.form.province_id,
        );
        if (initialProvince)
            selectedProvinceName.value = initialProvince.name_th;
    }
};

const initMap = () => {
    if (mapInstance.value) return;
    let initialLat = props.form.latitude || 13.7563;
    let initialLng = props.form.longitude || 100.5018;
    let initialZoom = props.form.latitude ? 16 : 10;

    mapInstance.value = L.map("map-picker").setView(
        [initialLat, initialLng],
        initialZoom,
    );
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        attribution:
            '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    }).addTo(mapInstance.value);

    if (props.form.latitude && props.form.longitude) {
        selectedLocation.value = {
            lat: props.form.latitude,
            lng: props.form.longitude,
        };
        mapMarker.value = L.marker(selectedLocation.value).addTo(
            mapInstance.value,
        );
    }

    mapInstance.value.on("click", (e) => {
        selectedLocation.value = e.latlng;
        if (mapMarker.value) mapInstance.value.removeLayer(mapMarker.value);
        mapMarker.value = L.marker(e.latlng).addTo(mapInstance.value);
        selectedProvinceName.value = null;
        reverseGeocode(e.latlng);
    });
};

const confirmLocation = () => {
    if (selectedLocation.value) {
        props.form.latitude = selectedLocation.value.lat;
        props.form.longitude = selectedLocation.value.lng;
    }
    showMapModal.value = false;
};

const closeModal = () => {
    showMapModal.value = false;
};

async function reverseGeocode(latlng) {
    if (allProvinces.value.length === 0) return;
    isGeocoding.value = true;
    try {
        const response = await fetch(
            `https://nominatim.openstreetmap.org/reverse?format=json&lat=${latlng.lat}&lon=${latlng.lng}&accept-language=th,en`,
        );
        const data = await response.json();
        if (data && data.address) {
            const provinceName = data.address.province || data.address.city;
            if (provinceName) {
                const normalizedName = provinceName
                    .replace("จังหวัด", "")
                    .trim();
                const matchingProvince = allProvinces.value.find(
                    (p) =>
                        p.name_th.includes(normalizedName) ||
                        (p.name_en && p.name_en.includes(normalizedName)),
                );
                if (matchingProvince) {
                    props.form.province_id = matchingProvince.id;
                    selectedProvinceName.value = matchingProvince.name_th;
                } else {
                    props.form.province_id = null;
                    selectedProvinceName.value = "Not Found";
                }
            }
        }
    } catch (error) {
        props.form.province_id = null;
        selectedProvinceName.value = "Error";
    } finally {
        isGeocoding.value = false;
    }
}

watch(showMapModal, (isShowing) => {
    if (isShowing) {
        nextTick(() => initMap());
    } else {
        if (mapInstance.value) {
            mapInstance.value.remove();
            mapInstance.value = null;
            mapMarker.value = null;
        }
    }
});

watch(
    () => props.form.start_sale_date,
    (newStartDate) => {
        if (
            newStartDate &&
            props.form.end_sale_date &&
            new Date(props.form.end_sale_date) < new Date(newStartDate)
        ) {
            props.form.end_sale_date = null;
        }
    },
);

watch(
    () => props.form.start_show_date,
    (newStartDate) => {
        if (
            newStartDate &&
            props.form.end_show_date &&
            new Date(props.form.end_show_date) < new Date(newStartDate)
        ) {
            props.form.end_show_date = null;
        }
    },
);
</script>

<template>
    <div ref="errorSummary" v-if="Object.keys(props.form.errors).length"
        class="max-w-xl lg:max-w-full mx-auto bg-card lg:shadow-xl rounded-md mb-4 p-6">
        <div>
            <div class="flex flex-col w-full space-y-2">
                <span class="text-lg font-semibold text-primary">Oops! Something went wrong.</span>
                <span class="text-sm">Please check the fields with dashed borders.</span>
                <ul
                    class="list-disc list-inside space-y-1 pl-5 bg-background p-4 rounded-md outline-dashed -outline-offset-4 text-primary">
                    <li v-for="(errorMessages, fieldName) in props.form.errors" :key="fieldName"
                        class="text-sm text-primary">
                        <strong class="font-bold">{{ fieldLabels[fieldName] || fieldName }}:</strong>
                        <span class="ml-1 text-text">{{ errorMessages }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-5xl lg:max-w-7xl space-y-2 relative">
        <div class="lg:flex p-6 space-y-2 lg:space-y-0 bg-card rounded-md">
            <div class="flex-shrink w-fit lg:mr-4">
                <div v-if="photoPreview" class="relative cursor-pointer lg:h-[444px]" :class="{
                    'outline-dashed outline-primary -outline-offset-4 rounded-md':
                        props.form.errors.picture_url,
                }">
                    <img :src="photoPreview" class="h-full object-cover rounded-md" />
                    <input ref="photoInput" type="file" class="hidden" @change="updatePhotoPreview" accept="image/*" />
                    <button type="button" @click.prevent="selectNewPhoto" class="absolute inset-0" />
                </div>
            </div>
            <div class="flex flex-shrink-0 flex-col min-w-[300px] justify-between pb-2">
                <div class="flex space-x-4">
                    <DropdownSelector placeholder="Event Type" v-model="props.form.event_type" :options="eventTypes"
                        :class="{
                            'outline-dashed outline-primary -outline-offset-4 rounded-md':
                                props.form.errors.event_type,
                        }" />
                    <DropdownSelector placeholder="Genre" v-model="props.form.genre" :options="genres" :class="{
                        'outline-dashed outline-primary -outline-offset-4 rounded-md':
                            props.form.errors.genre,
                    }" />
                </div>
                <input type="text" v-model="props.form.name"
                    class="mt-2 bg-background rounded-md font-medium border-none focus:ring-transparent placeholder:font-normal placeholder:text-text-medium"
                    :class="{
                        'outline-dashed outline-primary -outline-offset-4 rounded-md':
                            props.form.errors.name,
                    }" placeholder="Concert Name" />

                <div class="flex items-center space-x-8 ml-12 mt-4">
                    <HeartIcon class="flex-none h-8 w-8 text-primary" />
                    <LinkIcon class="flex-none h-8 w-8 text-secondary" />
                    <input type="text" v-model="props.form.ticket_link"
                        class="w-full bg-background rounded-md border-none focus:ring-transparent placeholder:font-normal placeholder:text-text-medium"
                        :class="{
                            'outline-dashed outline-primary -outline-offset-4 rounded-md':
                                props.form.errors.ticket_link,
                        }" placeholder="Ticket Link" />
                </div>

                <div class="flex flex-col mt-12 space-y-2 pl-2">
                    <AnimatedRangeInput :trigger-value="props.form.start_sale_date"
                        v-model:end-value="props.form.end_sale_date">
                        <template #icon>
                            <div class="flex items-center space-x-1 mr-2 border-2 border-primary rounded-md px-2 py-1">
                                <TicketIcon class="flex-none h-6 w-6" />
                            </div>
                        </template>
                        <template #startInput>
                            <DatePicker v-model="props.form.start_sale_date" :max-date="props.form.end_sale_date"
                                placeholder="Start Sale Date" :error="props.form.errors.start_sale_date" />
                        </template>
                        <template #endInput>
                            <DatePicker v-model="props.form.end_sale_date" :min-date="props.form.start_sale_date"
                                placeholder="End Sale Date" :error="props.form.errors.end_sale_date" />
                        </template>
                    </AnimatedRangeInput>

                    <AnimatedRangeInput :trigger-value="props.form.start_show_date"
                        v-model:end-value="props.form.end_show_date">
                        <template #icon>
                            <div class="flex items-center space-x-1 mr-2 border-2 border-primary rounded-md px-2 py-1">
                                <CalendarIcon class="flex-none h-6 w-6" />
                            </div>
                        </template>
                        <template #startInput>
                            <DatePicker v-model="props.form.start_show_date" placeholder="Start Show Date"
                                :min-date="isEditMode ? null : new Date()" :max-date="props.form.end_show_date"
                                :error="props.form.errors.start_show_date" />
                        </template>
                        <template #endInput>
                            <DatePicker v-model="props.form.end_show_date" placeholder="End Show Date"
                                :min-date="props.form.start_show_date" :error="props.form.errors.end_show_date" />
                        </template>
                    </AnimatedRangeInput>

                    <AnimatedRangeInput :trigger-value="props.form.start_show_time"
                        v-model:end-value="props.form.end_show_time">
                        <template #icon>
                            <div class="flex items-center space-x-1 mr-2 border-2 border-primary rounded-md px-2 py-1">
                                <ClockIcon class="flex-none h-6 w-6" />
                            </div>
                        </template>
                        <template #startInput>
                            <TimePicker v-model="props.form.start_show_time" placeholder="Start Show Time"
                                :error="props.form.errors.start_show_time" />
                        </template>
                        <template #endInput>
                            <TimePicker v-model="props.form.end_show_time" placeholder="End Show Time"
                                :error="props.form.errors.end_show_time" />
                        </template>
                    </AnimatedRangeInput>

                    <AnimatedRangeInput :trigger-value="props.form.price_min">
                        <template #icon>
                            <div class="flex items-center space-x-1 mr-2 border-2 border-primary rounded-md px-2 py-1">
                                <BanknotesIcon class="flex-none h-6 w-6" />
                            </div>
                        </template>
                        <template #startInput><input type="number" v-model="props.form.price_min"
                                class="bg-background rounded-md text-sm font-medium border-none focus:ring-transparent placeholder:font-normal placeholder:text-text-medium w-[100px]"
                                :class="{
                                    'outline-dashed outline-primary -outline-offset-4 rounded-md':
                                        props.form.errors.price_min,
                                }" placeholder="Minimum Price" /></template>
                        <template #endInput><input type="number" v-model="props.form.price_max"
                                class="bg-background rounded-md text-sm font-medium border-none focus:ring-transparent placeholder:font-normal placeholder:text-text-medium w-[100px]"
                                :class="{
                                    'outline-dashed outline-primary -outline-offset-4 rounded-md':
                                        props.form.errors.price_max,
                                }" placeholder="Maximum Price" /></template>
                    </AnimatedRangeInput>

                    <div class="flex items-start space-x-2">
                        <AnimatedRangeInput :trigger-value="locationMode === 'manual'
                            ? props.form.province_id
                            : props.form.latitude
                            " separator="," :is-long="true" class="grow">
                            <template #icon>
                                <button type="button" @click="toggleLocationMode" :title="locationMode === 'manual'
                                    ? 'Select from map'
                                    : 'Enter manually'
                                    ">
                                    <div
                                        class="flex items-center space-x-1 mr-2 border-2 border-primary rounded-md px-2 py-1">
                                        <MapIcon v-if="locationMode === 'manual'" class="h-6 w-6" />
                                        <MapPinIcon v-else class="h-6 w-6" />
                                    </div>
                                </button>
                            </template>

                            <template #startInput>
                                <button v-if="locationMode === 'map'" type="button" @click="openMap"
                                    class="bg-background hover:bg-background-hover rounded-md text-sm font-medium border-none text-text-medium text-left w-full px-3 py-2"
                                    :class="{
                                        'outline-dashed outline-primary -outline-offset-4 rounded-md':
                                            props.form.errors.latitude ||
                                            props.form.errors.longitude,
                                        'text-text': props.form.latitude,
                                    }">
                                    <span v-if="selectedCoordinates" class="text-text">{{ selectedProvinceName }} :
                                        <span class="text-text-medium">{{
                                            selectedCoordinates
                                        }}</span></span>
                                    <span v-else>Select location from map</span>
                                </button>
                                <ProvinceDropdown v-if="locationMode === 'manual'" v-model="props.form.province_id"
                                    :provinces="allProvinces" :class="{
                                        'outline-dashed outline-primary -outline-offset-4 rounded-md':
                                            props.form.errors.province_id,
                                    }" />
                            </template>

                            <template #endInput>
                                <div v-if="locationMode === 'manual'" class="relative w-full">
                                    <input type="text" v-model="props.form.venue_name"
                                        class="bg-background rounded-md text-sm font-medium border-none focus:ring-transparent placeholder:font-normal placeholder:text-text-medium w-full pr-10"
                                        :class="{
                                            'outline-dashed outline-primary -outline-offset-4 rounded-md':
                                                props.form.errors.venue_name,
                                        }" placeholder="Venue Name" />
                                </div>
                            </template>
                        </AnimatedRangeInput>
                    </div>
                    <div class="flex text-sm space-x-4 ml-2.5">
                        <ArrowTurnLeftUpIcon class="h-4 w-4 inline-block" />
                        <span class="mt-0.5">Click to change location mode</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-6 bg-card rounded-md">
            <div class="flex flex-col">
                <h3 class="font-bold text-xl pb-2">Description</h3>
                <div class="w-full space-y-2">
                    <div class="w-full h-full px-2 py-4 bg-background rounded-md space-y-2">
                        <div class="flex flex-wrap items-center gap-y-2 gap-x-1 mx-2 bg-transparent">
                            <div class="relative">
                                <ArtistDropdown v-model="tempArtistId" :options="availableArtists"
                                    :is-dark-mode="isDarkMode" :class="{
                                        'outline-dashed outline-primary -outline-offset-4 rounded-full':
                                            props.form.errors.artist_ids,
                                    }">
                                    <template #button-face="{ selectedOption }">
                                        <div
                                            class="group flex items-center bg-card hover:bg-primary py-1 px-2 rounded-full text-sm font-medium cursor-pointer transition-all duration-150">
                                            <PlusIcon
                                                class="h-4 w-4 stroke-current stroke-[3px] text-primary group-hover:text-white" />
                                            <span class="mx-1 group-hover:text-white uppercase">Add Artist</span>
                                        </div>
                                    </template>
                                </ArtistDropdown>
                            </div>
                            <div v-for="artist in selectedArtists" :key="artist.id"
                                class="flex items-center space-x-2 bg-card py-1 px-2 rounded-full">
                                <img :src="getArtistPicture(artist)" :alt="artist.name"
                                    class="w-5 h-5 rounded-full object-cover" />
                                <span class="text-sm font-medium uppercase">{{
                                    artist.name
                                }}</span>
                                <button @click="removeArtist(artist.id)" type="button"
                                    class="text-text-medium hover:text-primary rounded-full p-0.5">
                                    <XMarkIcon class="w-4 h-4 stroke-current stroke-[2px]" />
                                </button>
                            </div>
                        </div>
                        <textarea v-model="props.form.description"
                            class="w-full resize-none h-96 bg-transparent border-none focus:ring-transparent placeholder:font-normal placeholder:text-text-medium"
                            :class="{
                                'outline-dashed outline-primary -outline-offset-4 rounded-md':
                                    props.form.errors.description,
                            }" placeholder="Description"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <button type="button" @click="submit"
            class="rounded-md bg-primary flex items-center space-x-2 justify-center px-4 py-1 w-full text-white font-semibold disabled:opacity-50"
            :disabled="props.form.processing">
            <FolderArrowDownIcon class="w-4 h-4 stroke-current stroke-[2.5px]" />
            <span v-if="props.form.processing">{{
                isEditMode ? "Updating..." : "Creating..."
            }}</span>
            <span v-else>{{
                isEditMode ? "Update Concert" : "Create Concert"
            }}</span>
        </button>

        <teleport to="body">
            <div v-if="showMapModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75 p-4"
                @click.self="closeModal">
                <div class="bg-card rounded-md shadow-xl w-full max-w-2xl p-6 space-y-4">
                    <div class="flex justify-between items-center">
                        <h3
                            class="text-lg flex flex-col sm:flex-row items-start sm:items-center sm:space-x-2 font-medium leading-6">
                            <span>Choose Location</span>
                            <span v-if="isGeocoding"
                                class="text-sm font-normal text-text-medium">(Searching for province...)</span>
                            <span v-else-if="selectedLocation" class="text-sm font-normal text-text-medium">({{
                                selectedProvinceName }} - Coordinates:
                                {{ modalSelectedCoordinates }})</span>
                        </h3>
                        <button @click="closeModal" class="hover:text-text-hover">
                            <XMarkIcon class="h-6 w-6" />
                        </button>
                    </div>
                    <div id="map-picker" class="rounded-md h-[400px] w-full"></div>
                    <button type="button" @click="confirmLocation"
                        class="rounded-md w-full bg-primary px-4 py-2 font-bold text-white hover:bg-primary-hover disabled:opacity-50"
                        :disabled="!selectedLocation || isGeocoding">
                        <span v-if="isGeocoding">Searching...</span>
                        <span v-else>Confirm Location</span>
                    </button>
                </div>
            </div>
        </teleport>
    </div>
</template>
