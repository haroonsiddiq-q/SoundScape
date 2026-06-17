<script setup>
import { computed, ref } from "vue";
import { router } from "@inertiajs/vue3";
import {
    HeartIcon as OutlineHeartIcon,
    MapPinIcon,
    TicketIcon,
} from "@heroicons/vue/24/outline";
import {
    HeartIcon as SolidHeartIcon,
} from "@heroicons/vue/24/solid";

const props = defineProps({
    concert: Object,
    role: {
        type: String,
        default: 'user',
    },
});

const isLoading = ref(false);

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

const pictureUrl = computed(() => {
    if (props.concert?.picture_url) {
        if (props.concert.picture_url.startsWith("http")) {
            return props.concert.picture_url;
        }
        return `/storage/${props.concert.picture_url}`;
    }
    return "https://placehold.co/600x400?text=SoundScape";
});

const formattedDate = computed(() => {
    if (props.concert && props.concert.start_show_date) {
        const date = new Date(props.concert.start_show_date);
        return date.toLocaleDateString("en-GB", {
            day: "2-digit",
            month: "long",
            year: "numeric",
        });
    }
    return "N/A";
});

const eventTypeName = computed(() => {
    if (props.concert && props.concert.event_type) {
        const event = eventTypes.find(e => e.value === props.concert.event_type);
        return event ? event.name : props.concert.event_type;
    }
    return "N/A";
});

const genreName = computed(() => {
    if (props.concert && props.concert.genre) {
        const genre = genres.find(g => g.value === props.concert.genre);
        return genre ? genre.name : props.concert.genre;
    }
    return "N/A";
});

const VenueName = computed(() => {
    if (props.concert && props.concert.venue_name != null) {
        return venue_name ? concert.vanue_name : "N/A";
    }
    return "N/A";
});

const formattedPrice = computed(() => {
    if (props.concert && props.concert.price_min != null) {
        if (props.concert.price_min === 0) {
            return "Free";
        }
        return new Intl.NumberFormat("th-TH", {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(props.concert.price_min) + " THB";
    }
    return "N/A";
});

const detailUrl = computed(() => {
    return route('concert.detail', { concert: props.concert.id });
});

const followConcert = (follow) => {
    if (isLoading.value) return;
    isLoading.value = true;

    props.concert.is_followed = !follow;

    router.post(
        route("concert.follow", props.concert.id),
        {
            is_following: !follow,
        },
        {
            preserveState: true,
            preserveScroll: true,
            onFinish: () => {
                isLoading.value = false;
            },
            onError: () => {
                props.concert.is_followed = follow;
            }
        }
    );
};
</script>

<template>
    <div class="w-full bg-card space-y-1 overflow-hidden">
        <img :src="pictureUrl" :alt="props.concert.name || 'Concert Picture'"
            class="w-full aspect-[2/3] object-fill rounded-md" />
        <div class="space-y-2">
            <div class="flex flex-col space-y-1">
                <div class="flex justify-between items-center">
                    <span class="text-sm truncate text-text-medium">{{
                        formattedDate
                    }}</span>
                    <button @click.prevent.stop="
                        followConcert(props.concert.is_followed)
                        " :class="{ hidden: role !== 'user' }">
                        <component :is="props.concert.is_followed
                            ? SolidHeartIcon
                            : OutlineHeartIcon
                            " class="w-6 h-6 p-1 stroke-current stroke-[2px]" :class="props.concert.is_followed
                                ? 'text-primary'
                                : 'text-text-medium hover:text-primary transition-colors duration-200'
                                " />
                    </button>
                </div>
                <span class="text-sm md:text-base lg:text-lg xl:text-xl font-medium min-h-14 break-all">
                    <span class="line-clamp-2">
                        {{ props.concert.name }}
                    </span>
                </span>
                <div class="h-1 w-full bg-card-hover rounded" />
                <div class="flex justify-between text-sm">
                    <div class="flex items-center gap-1">
                        <TicketIcon
                            class="w-6 h-6 flex-shrink-0 stroke-[2px] text-text-high p-1 border-2 border-accent-high rounded-md" />
                        <span class="truncate"> {{ formattedPrice }} </span>
                    </div>
                    <div class="flex items-center gap-1">
                        <span class="truncate"> {{ provinceName }} </span>
                        <MapPinIcon
                            class="w-6 h-6 flex-shrink-0 stroke-[2.5px] text-text-high p-1 border-2 border-secondary-high rounded-md" />
                    </div>
                </div>
            </div>

            <a :href="detailUrl" target="_blank"
                class="block w-full text-xs font-medium rounded-md py-1 text-center border-2 border-primary">
                View Details
            </a>
        </div>
    </div>
</template>