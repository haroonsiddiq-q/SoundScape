<script setup>
import { computed } from "vue";
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
    provinces: Object,
    role: String,
});

const pictureUrl = computed(() => {
    if (props.concert?.picture_url) {
        if (props.concert.picture_url.startsWith("http")) {
            return props.concert.picture_url;
        }
        return `/storage/${props.concert.picture_url}`;
    }
    return "https://placehold.co/600x400?text=SoundScape";
});

const provinceName = computed(() => {
    if (props.provinces && props.concert && props.concert.province_id) {
        const province = props.provinces[props.concert.province_id];
        return province ? province.name_th : "Unknown Province";
    }
    return "?";
});

const formattedDate = computed(() => {
    if (props.concert && props.concert.start_show_date) {
        const date = new Date(props.concert.start_show_date);
        return date.toLocaleDateString("th-TH", {
            day: "2-digit",
            month: "long",
            year: "numeric",
        });
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
    return "?";
});

const followConcert = (follow) => {
    router.post(
        route("concert.follow", props.concert.id),
        {
            is_following: !follow,
        },
        { preserveState: true }
    );
};
</script>

<template>
    <div class="bg-card rounded-md p-2 space-y-1 shadow-md overflow-hidden">
        <img :src="pictureUrl" :alt="props.concert.name || 'Concert Picture'"
            class="w-full aspect-[2/3] object-fill rounded-md" />
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
                    {{props.concert.name}}
                </span>
            </span>
            <div class="h-1 w-full bg-card-hover rounded" />
            <div class="flex justify-between text-sm">
                <div class="flex items-center gap-1">
                    <TicketIcon class="w-6 h-6 flex-shrink-0 stroke-[2px] text-text-high p-1 border-2 border-primary-high rounded-md" />
                    <span class="truncate"> {{ formattedPrice }} </span>
                </div>
                <div class="flex items-center gap-1">
                    <span class="truncate"> {{ provinceName }} </span>
                    <MapPinIcon class="w-6 h-6 flex-shrink-0 stroke-[2.5px] text-text-high p-1 border-2 border-secondary-high rounded-md" />
                </div>
            </div>
        </div>
    </div>
</template>
