<script setup>
import { defineProps, computed, inject, ref } from "vue";
import { Link, router, useForm, usePage } from "@inertiajs/vue3";
import {
    HeartIcon as OutlineHeartIcon,
    LinkIcon,
    ArrowTopRightOnSquareIcon,
    ArrowRightStartOnRectangleIcon,
    TicketIcon,
    CalendarIcon,
    ClockIcon,
    BanknotesIcon,
    MapPinIcon,
    PencilIcon,
    TrashIcon,
    ArrowLongDownIcon,
    EllipsisVerticalIcon,
} from "@heroicons/vue/24/outline";
import { HeartIcon as SolidHeartIcon } from "@heroicons/vue/24/solid";
import InputError from "@/Components/InputError.vue";
import Dropdown from "@/Components/Dropdown.vue";

const props = defineProps({
    concert: Object,
    provinces: Object,
    role: String,
});

const isDarkMode = inject("isDarkMode", ref(false));
const copied = ref(false);
const page = usePage();

const getRoutePrefix = () => {
    if (page.url.startsWith("/admin")) return "admin.";
    if (page.url.startsWith("/promoter")) return "promoter.";
    return "";
};

const commentForm = useForm({ content: "" });
const editingId = ref(null);
const editForm = useForm({ content: "" });

const submitComment = () => {
    const routeName = `${getRoutePrefix()}concert.comment.store`;
    commentForm.post(route(routeName, props.concert.id), {
        preserveScroll: true,
        onSuccess: () => commentForm.reset("content"),
    });
};

const startEdit = (comment) => {
    editingId.value = comment.id;
    editForm.content = comment.content;
};

const cancelEdit = () => {
    editingId.value = null;
    editForm.reset();
};

const submitEdit = (commentId) => {
    const routeName = `${getRoutePrefix()}concert.comment.update`;
    editForm.put(route(routeName, commentId), {
        preserveScroll: true,
        onSuccess: () => cancelEdit(),
    });
};

const deleteComment = (commentId) => {
    if (confirm("Are you sure you want to delete this comment?")) {
        const routeName = `${getRoutePrefix()}concert.comment.destroy`;
        router.delete(route(routeName, commentId), {
            preserveScroll: true,
        });
    }
};

const editHref = computed(() => {
    if (props.role === "admin") {
        return route("admin.concert.edit", { concert: props.concert.id });
    }
    return route("promoter.concert.edit", { concert: props.concert.id });
});

const copyUrlToClipboard = () => {
    const url = window.location.href;
    navigator.clipboard
        .writeText(url)
        .then(() => {
            copied.value = true;
            setTimeout(() => {
                copied.value = false;
            }, 2000);
        })
        .catch((err) => {
            console.error("Failed to copy URL: ", err);
        });
};

const deleteRouteName = computed(() => {
    if (props.role === "admin") {
        return "admin.concert.delete";
    }
    return "promoter.concert.delete";
});

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

const photoPreview = computed(() => {
    if (props.concert.picture_url) {
        if (props.concert.picture_url.startsWith("http")) {
            return props.concert.picture_url;
        }
        return `/storage/${props.concert.picture_url}`;
    }
    return isDarkMode.value
        ? "https://placehold.co/800x1200/1c1423/ffffff80?text=No%5CnPicture"
        : "https://placehold.co/800x1200/e5e7eb/00000080?text=No%5CnPicture";
});

const promoterPicturePlaceholder = computed(() => {
    return (promoter) => {
        const name = promoter?.fullname;
        return isDarkMode.value
            ? `https://ui-avatars.com/api/?name=${name}&background=1c1423&color=ffffff`
            : `https://ui-avatars.com/api/?name=${name}&background=f3f4f7&color=000000`;
    };
});

const displayEventType = computed(() => {
    const eventType = eventTypes.find(
        (e) => e.value === props.concert.event_type,
    );
    return eventType
        ? eventType.name
        : props.concert.event_type || "N/A";
});

const displayGenre = computed(() => {
    const genre = genres.find((g) => g.value === props.concert.genre);
    return genre ? genre.name : props.concert.genre || "N/A";
});

const formattedDate = (dateString) => {
    if (!dateString) return null;
    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return "Invalid Date";
        return date.toLocaleDateString("th-TH", {
            year: "numeric",
            month: "short",
            day: "numeric",
        });
    } catch (e) {
        return dateString;
    }
};

const formattedTime = (timeString) => {
    if (!timeString) return null;
    const parts = timeString.split(":");
    if (parts.length < 2) return timeString;
    return `${parts[0]}:${parts[1]}`;
};

const formattedPrice = (price) => {
    if (price == 0) return null;
    return new Intl.NumberFormat("th-TH", {
        maximumFractionDigits: 0,
    }).format(price);
};

const formatCommentDate = (dateString) => {
    if (!dateString) return "";
    const dateObj = new Date(dateString);

    const time = dateObj.toLocaleTimeString("th-TH", {
        hour: "2-digit",
        minute: "2-digit",
    });

    const date = dateObj.toLocaleDateString("th-TH", {
        month: "2-digit",
        day: "numeric",
    });

    return `${time} น. · ${date}`;
};

const startSaleDate = computed(() =>
    formattedDate(props.concert.start_sale_date),
);
const endSaleDate = computed(() => formattedDate(props.concert.end_sale_date));
const startShowDate = computed(() =>
    formattedDate(props.concert.start_show_date),
);
const endShowDate = computed(() => formattedDate(props.concert.end_show_date));
const startShowTime = computed(() =>
    formattedTime(props.concert.start_show_time),
);
const endShowTime = computed(() => formattedTime(props.concert.end_show_time));
const priceMin = computed(() => formattedPrice(props.concert.price_min));
const priceMax = computed(() => formattedPrice(props.concert.price_max));

const showAllArtists = ref(false);
const ARTIST_DISPLAY_LIMIT = 5;

const displayedArtists = computed(() => {
    if (!props.concert.artists) return [];
    return showAllArtists.value
        ? props.concert.artists
        : props.concert.artists.slice(0, ARTIST_DISPLAY_LIMIT);
});

const hasMoreArtists = computed(() => {
    return props.concert.artists && props.concert.artists.length > ARTIST_DISPLAY_LIMIT;
});

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

const provinceName = computed(() => {
    if (props.provinces && props.concert && props.concert.province_id) {
        const province = props.provinces[props.concert.province_id];
        return province ? province.name_th : "Unknown Province";
    }
    return "N/A";
});

const googleMapsLink = computed(() => {
    if (props.concert.latitude && props.concert.longitude) {
        return `https://www.google.com/maps/search/?api=1&query=${props.concert.latitude},${props.concert.longitude}`;
    }
    const query = `${props.concert.venue_name}, ${props.concert.province?.name || props.concert.province_id
        }`;
    return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(
        query,
    )}`;
});

const followConcert = (follow) => {
    router.post(
        route("concert.follow", props.concert.id),
        {
            is_following: !follow,
        },
        { preserveState: true },
    );
};
</script>

<template>
    <div class="mx-auto max-w-5xl lg:max-w-7xl space-y-2 overflow-hidden relative">
        <div class="lg:flex p-6 space-y-2 lg:space-y-0 bg-card rounded-md overflow-auto">
            <div class="flex-shrink-0 w-fit lg:mr-4">
                <div class="lg:h-[444px]">
                    <img :src="photoPreview" class="h-full object-cover rounded-md" alt="Concert Poster" />
                </div>
            </div>

            <div class="flex flex-shrink flex-col min-w-[300px] justify-between pb-2">
                <div class="flex flex-col">
                    <div class="flex space-x-2">
                        <div
                            class="inline-flex items-center bg-primary rounded-md px-3 h-6 text-sm text-white font-semibold">
                            {{ displayEventType }}
                        </div>
                        <div
                            class="inline-flex items-center bg-secondary rounded-md px-3 h-6 text-sm text-white font-semibold">
                            {{ displayGenre }}
                        </div>
                        <div v-if="props.role == 'admin'" class="absolute right-6">
                            <template v-if="props.concert.origin">
                                <span v-if="props.concert.origin == 'The Concert'"
                                    class="inline-flex items-center bg-[#000000] dark:bg-transparent rounded-md px-2 h-6">
                                    <img src="https://cdn.theconcert.com/dist/2.0.303/assets/images/tcc-main-logo.svg"
                                        alt="The Concert" class="h-4" />
                                </span>
                                <span v-if="props.concert.origin == 'Ticketier'"
                                    class="inline-flex items-center dark:bg-[#ffffff] rounded-md px-2 h-6">
                                    <img src="https://www.ticketier.com/ticketier_logo_text.svg" alt="Ticketier"
                                        class="h-4" />
                                </span>
                                <span v-if="props.concert.origin = 'All Ticket'"
                                    class="inline-flex items-center bg-[#333333] dark:bg-transparent rounded-md px-2 h-6">
                                    <img src="https://atkmedia.allticket.com/assets/images/allticket-logo.png?_dat=20260315"
                                        alt="All Ticket" class="h-4" />
                                </span>
                            </template>

                            <template v-if="props.concert.promoter_id">
                                <div class="flex items-center">
                                    <Link v-if="props.role === 'admin'" :href="route('admin.promoter.detail', {
                                        promoter:
                                            props.concert.promoter_id,
                                    })
                                        ">
                                        <img :src="promoterPicturePlaceholder(
                                            props.concert.promoter,
                                        )
                                            " alt="Promoter Icon"
                                            class="w-6 h-6 border-2 border-primary rounded-full object-cover" />
                                    </Link>
                                </div>
                            </template>
                        </div>
                    </div>

                    <h1 class="custom-scrollbar mt-1 font-semibold text-2xl lg:text-4xl break-word py-1">
                        <span class="line-clamp-3">
                            {{ props.concert.name }}
                        </span>
                    </h1>

                    <div class="flex items-center space-x-12 mx-auto lg:ml-12 mt-4">
                        <OutlineHeartIcon v-if="
                            props.role === 'admin' ||
                            props.role === 'promoter'
                        " class="flex-none h-8 w-8 text-primary" />
                        <button v-else @click.prevent.stop="
                            followConcert(props.concert.is_followed)
                            ">
                            <component :is="props.concert.is_followed
                                ? SolidHeartIcon
                                : OutlineHeartIcon
                                " class="flex-none h-8 w-8 text-primary" />
                        </button>
                        <div class="relative">
                            <button @click="copyUrlToClipboard" type="button" class="flex items-center"
                                aria-label="Copy page URL">
                                <LinkIcon class="flex-none h-8 w-8 text-secondary" />
                            </button>
                            <transition enter-active-class="transition-opacity duration-200"
                                leave-active-class="transition-opacity duration-300" enter-from-class="opacity-0"
                                leave-to-class="opacity-0">
                                <div v-if="copied"
                                    class="absolute -top-8 left-1/2 -translate-x-1/2 px-2 py-1 bg-background text-xs rounded-md shadow-xl whitespace-nowrap">
                                    Copied to clipboard!
                                </div>
                            </transition>
                        </div>
                        <a v-if="props.concert.ticket_link" :href="props.concert.ticket_link" target="_blank"
                            rel="noopener noreferrer" title="Ticket Link">
                            <ArrowRightStartOnRectangleIcon class="flex-none h-8 w-8 text-accent" />
                        </a>
                    </div>
                </div>

                <div class="flex flex-col min-w-max mt-12 space-y-1 pl-1">
                    <div v-if="props.concert.start_sale_date" class="flex items-center font-semibold">
                        <div class="flex items-center space-x-1 mr-2 border-2 border-primary rounded-md px-2 py-1">
                            <TicketIcon class="flex-none h-6 w-6" />
                            <span class="text-center"> Start Sale </span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span>{{ startSaleDate }}</span>
                            <span v-if="props.concert.end_sale_date">-</span>
                            <span v-if="props.concert.end_sale_date">{{
                                endSaleDate
                            }}</span>
                        </div>
                    </div>

                    <div v-if="props.concert.start_show_date" class="flex items-center font-semibold">
                        <div class="flex items-center space-x-1 mr-2 border-2 border-primary rounded-md px-2 py-1">
                            <CalendarIcon class="flex-none h-6 w-6" />
                            <span class="text-center"> Show Date </span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span>{{ startShowDate }}</span>
                            <span v-if="props.concert.end_show_date">-</span>
                            <span v-if="props.concert.end_show_date">{{
                                endShowDate
                            }}</span>
                        </div>
                    </div>

                    <div v-if="props.concert.start_show_time" class="flex items-center font-semibold">
                        <div class="flex items-center space-x-1 mr-2 border-2 border-primary rounded-md px-2 py-1">
                            <ClockIcon class="flex-none h-6 w-6" />
                            <span class="text-center"> Show Time </span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span>{{ startShowTime }}</span>
                            <span v-if="props.concert.end_show_time">-</span>
                            <span v-if="props.concert.end_show_time">{{
                                endShowTime
                            }}</span>
                        </div>
                    </div>

                    <div v-if="props.concert.price_min" class="flex items-center font-semibold">
                        <div class="flex items-center space-x-1 mr-2 border-2 border-primary rounded-md px-2 py-1">
                            <BanknotesIcon class="flex-none h-6 w-6" />
                            <span class="text-center"> Ticket Price </span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span v-if="props.concert.price_min">{{
                                priceMin
                            }}</span>
                            <span v-if="props.concert.price_max">-</span>
                            <span v-if="props.concert.price_max">{{
                                priceMax
                            }}</span>
                        </div>
                    </div>

                    <div v-if="
                        props.concert.province_id ||
                        props.concert.venue_name
                    " class="flex items-center font-semibold">
                        <div class="flex items-center space-x-1 mr-2 border-2 border-primary rounded-md px-2 py-1">
                            <MapPinIcon class="flex-none h-6 w-6" />
                            <span v-if="props.concert.province_id" class="text-center">
                                {{
                                    props.concert.venue_name ||
                                        [
                                            props.concert.latitude,
                                            props.concert.longitude,
                                        ].every(Boolean)
                                        ? provinceName
                                        : provinceName
                                }}
                            </span>
                        </div>
                        <a :href="googleMapsLink" target="_blank" rel="noopener noreferrer" class="hover:underline"
                            aria-label="View on map">
                            <span v-if="props.concert.venue_name">
                                {{ props.concert.venue_name }}
                                <ArrowTopRightOnSquareIcon class="h-4 w-4 inline-block stroke-[2px] mb-2" />
                            </span>
                            <span v-else-if="
                                props.concert.latitude &&
                                props.concert.longitude
                            ">
                                View on Map
                                <ArrowTopRightOnSquareIcon class="h-4 w-4 inline-block stroke-[2px] mb-2" />
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-2 w-full">
            <div class="flex flex-col lg:col-span-2 p-6 bg-card rounded-md min-h-[500px]">
                <h3 class="font-bold text-xl border-b border-text-low pb-2 mb-4">
                    Description
                </h3>

                <div class="w-full h-fit space-y-4">
                    <div v-if="props.concert.artists?.length" class="flex flex-wrap items-center gap-y-2 gap-x-1 mx-2">
                        <div v-for="artist in displayedArtists" :key="artist.id"
                            class="flex items-center space-x-2 bg-card py-1 px-2 rounded-full border-2 border-primary">
                            <img :src="getArtistPicture(artist)" :alt="artist.name"
                                class="w-5 h-5 rounded-full object-cover" />
                            <span class="text-sm font-medium uppercase">{{
                                artist.name
                                }}</span>
                        </div>

                        <button v-if="hasMoreArtists" @click="showAllArtists = !showAllArtists"
                            class="ml-2 text-sm font-medium text-primary hover:text-primary-hover hover:underline transition-colors focus:outline-none">
                            {{ showAllArtists ? 'Show Less' : `+${props.concert.artists.length - ARTIST_DISPLAY_LIMIT}
                            Artists`
                            }}
                        </button>
                    </div>

                    <div class="w-full whitespace-pre-wrap">
                        {{ props.concert.description || "No description" }}
                    </div>
                </div>

                <div class="mt-auto pt-8">
                    <div class="pt-4 border-t border-text-low">
                        <div v-if="
                            props.role === 'admin' ||
                            props.role === 'promoter'
                        " class="flex justify-end space-x-2 font-semibold">
                            <Link :href="editHref"
                                class="flex justify-center px-4 py-1 items-center space-x-2 bg-secondary text-white rounded-md hover:opacity-90 transition">
                                <PencilIcon class="w-4 h-4 stroke-current stroke-[2.5px]" />
                                <span>Edit</span>
                            </Link>
                            <Link :href="route(deleteRouteName, {
                                concert: props.concert.id,
                            })
                                " method="delete" as="button" preserve-scroll
                                class="flex justify-center px-4 py-1 items-center space-x-2 bg-red-600 text-white rounded-md hover:opacity-90 transition">
                                <TrashIcon class="w-4 h-4 stroke-current stroke-[2.5px]" />
                                <span>Delete</span>
                            </Link>
                        </div>
                        <div v-else class="flex justify-end">
                            <a v-if="props.concert.ticket_link" :href="props.concert.ticket_link" target="_blank"
                                rel="noopener noreferrer"
                                class="bg-primary text-white px-6 py-2 flex items-center justify-center space-x-2 rounded-md font-semibold hover:opacity-90 transition">
                                <span>Ticket Link</span>
                                <ArrowRightStartOnRectangleIcon class="w-5 h-5 stroke-[2px]" />
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1 relative">
                <div class="flex flex-col h-[500px] lg:h-full lg:absolute lg:inset-0 px-6 py-6 bg-card rounded-md">
                    <h3 class="font-bold text-xl mb-4 border-b border-text-low pb-2 shrink-0">
                        Comments
                    </h3>

                    <div v-if="$page.props.auth.user || props.role === 'admin'" class="mb-4 shrink-0">
                        <form @submit.prevent="submitComment">
                            <InputError class="mb-2" :message="commentForm.errors.content" />
                            <div class="flex gap-2">
                                <textarea v-model="commentForm.content"
                                    class="custom-scrollbar overflow-y-auto resize-none w-full rounded-md border-text-low shadow-sm focus:border-primary focus:ring-primary text-sm bg-background"
                                    rows="2" placeholder="Share your thoughts..." required></textarea>
                                <div class="flex justify-end">
                                    <button type="submit" :disabled="commentForm.processing"
                                        class="bg-primary my-2 px-1 text-white text-sm font-semibold rounded-md disabled:opacity-50 transition hover:bg-primary-hover">
                                        <ArrowLongDownIcon class="w-6 h-6 stroke-current stroke-[2px]" />
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div v-else class="mb-4 p-3 bg-background rounded-md text-sm shrink-0">
                        Please
                        <a :href="route('login')" class="text-primary font-bold hover:underline">log in</a>
                        to comment.
                    </div>

                    <div class="flex-1 overflow-y-auto space-y-4 pr-2 custom-scrollbar">
                        <div v-for="comment in concert.comments" :key="comment.id"
                            class="flex flex-col border-b border-text-low pb-3">
                            <div class="flex items-center justify-between mb-1">
                                <h4 class="font-semibold flex items-center text-sm">
                                    {{ comment.author_name }}
                                    <span v-if="comment.is_author"
                                        class="ml-2 px-2 text-xs bg-primary text-white rounded font-semibold">
                                        Organizer
                                    </span>
                                </h4>

                                <span class="text-xs text-text-medium">
                                    {{
                                        formatCommentDate(
                                            comment.created_at,
                                        )
                                    }}
                                </span>

                            </div>

                            <div v-if="editingId === comment.id" class="mt-1">
                                <textarea v-model="editForm.content"
                                    class="custom-scrollbar overflow-y-auto resize-none w-full rounded-md border-text-low shadow-sm focus:border-primary focus:ring-primary text-sm bg-background"
                                    rows="2"></textarea>
                                <div class="flex space-x-2">
                                    <button @click="submitEdit(comment.id)" :disabled="editForm.processing"
                                        class="px-3 py-1 bg-primary text-white text-xs rounded font-semibold transition hover:bg-primary-hover">
                                        Save
                                    </button>
                                    <button @click="cancelEdit" type="button"
                                        class="px-3 py-1 bg-text-medium text-white text-xs rounded font-semibold transition hover:bg-text-high">
                                        Cancel
                                    </button>
                                </div>
                            </div>

                            <div v-else class="flex justify-between">
                                <p class="text-text text-sm break-all whitespace-pre-wrap">
                                    {{ comment.content }}
                                </p>
                                <Dropdown v-if="comment.is_owner" align="right" width="48">
                                    <template #trigger>
                                        <button
                                            class="flex items-center px-1 text-primary focus:outline-none transition">
                                            <EllipsisVerticalIcon class="w-5 h-5 stroke-current stroke-[2px]" />
                                        </button>
                                    </template>
                                    <template #content>
                                        <div class="py-1 bg-card rounded-md shadow-xs border border-text-low">
                                            <button @click="startEdit(comment)"
                                                class="flex items-center space-x-2 w-full px-4 py-2 text-left text-sm hover:bg-background transition">
                                                <PencilIcon class="w-4 h-4 stroke-current" />
                                                <span>Edit</span>
                                            </button>
                                            <button @click="
                                                deleteComment(
                                                    comment.id,
                                                )
                                                "
                                                class="flex items-center space-x-2 w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-background transition">
                                                <TrashIcon class="w-4 h-4 stroke-current" />
                                                <span>Delete</span>
                                            </button>
                                        </div>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <p v-if="!concert.comments?.length" class="text-text-medium text-sm text-center">
                            No comments yet. Be the first to comment!
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
