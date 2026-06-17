<script setup>
import { ref, watch } from "vue";
import { usePage, Link, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import HighlightBanner from "@/Components/Highlights/HighlightBanner.vue";
import ConcertCard from "@/Components/Concerts/ConcertCard.vue";
import DropdownFilter from "@/Components/DropdownFilter.vue";
import { MinusCircleIcon, HeartIcon } from "@heroicons/vue/24/outline";
import { HeartIcon as HeartIconSolid } from "@heroicons/vue/24/solid";

const page = usePage();

const props = defineProps({
    highlights: Object,
    concerts: Object,
    provinces: Object,
    filters: Object,
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

const sortOptions = [
    { value: "newest", name: "Newest" },
    { value: "name_az", name: "Name A-Z" },
    { value: "name_za", name: "Name Z-A" },
    { value: "date_asc", name: "Date Closest" },
    { value: "price_asc", name: "Price Lowest" },
    { value: "price_desc", name: "Price Highest" },
];

const selectedEventType = ref(props.filters?.event_type || "");
const selectedGenre = ref(props.filters?.genre || "");
const selectedSort = ref(props.filters?.sort || "newest");
const showFollowedOnly = ref(props.filters?.followed === "true" || false);

watch(
    [selectedEventType, selectedGenre, selectedSort, showFollowedOnly],
    ([eventType, genre, sort, followed]) => {
        const currentSearch = props.filters?.search || undefined;

        router.get(
            route("index"),
            {
                search: currentSearch,
                event_type: eventType || undefined,
                genre: genre || undefined,
                sort: sort === "newest" ? undefined : sort,
                followed: followed ? "true" : undefined,
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            }
        );
    }
);
</script>

<template>
    <AppLayout title="Home" :can-login="$page.props.canLogin" :can-register="$page.props.canRegister">
        <HighlightBanner v-if="!filters?.search" :highlights="page.props.highlights" />

        <div class="space-y-4">
            <h2 class="text-2xl font-bold uppercase">
                {{
                    filters?.search
                        ? `Results for "${filters.search}"`
                        : "All Concerts"
                }}
            </h2>
            <div class="mx-8 flex flex-wrap gap-2">
                <DropdownFilter id="event_type" v-model="selectedEventType" :options="eventTypes"
                    placeholder="All Types" />

                <DropdownFilter id="genre" v-model="selectedGenre" :options="genres" placeholder="All Genres" />

                <DropdownFilter id="sort" v-model="selectedSort" :options="sortOptions" placeholder="Newest" />

                <button v-if="
                    selectedEventType ||
                    selectedGenre ||
                    selectedSort !== 'newest'
                " @click="
                        (selectedEventType = ''),
                        (selectedGenre = ''),
                        (selectedSort = 'newest')
                        " type="button"
                    class="group p-1 bg-card rounded-full text-text hover:bg-primary hover:text-white transition-colors duration-100"
                    title="Clear filters">
                    <MinusCircleIcon class="h-5 w-5 stroke-[2px] group-hover:stroke-[2.5px]" />
                </button>

                <button v-if="$page.props.auth.user" @click="showFollowedOnly = !showFollowedOnly" type="button" :class="[
                    'group p-1 bg-card rounded-full transition-colors duration-200',
                    showFollowedOnly
                        ? 'text-primary hover:bg-primary hover:text-text'
                        : 'text-text hover:bg-primary hover:text-text',
                ]" :title="showFollowedOnly
                            ? 'Show all concerts'
                            : 'Show followed concerts only'
                        ">
                    <HeartIconSolid v-if="showFollowedOnly"
                        class="h-5 w-5 stroke-[2px] group-hover:stroke-[2.5px] fill-current" />
                    <HeartIcon v-else class="h-5 w-5 stroke-[2px] group-hover:stroke-[2.5px]" />
                </button>
            </div>
            <div v-if="concerts && concerts.length > 0"
                class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-2">
                <Link v-for="concert in concerts" :key="concert.id" :href="route('concert.detail', {
                    concert: concert.id,
                })
                    " class="block h-full">
                    <ConcertCard :concert="concert" :provinces="provinces" role="user" />
                </Link>
            </div>

            <div v-else class="text-center py-12 text-text-medium">
                <p class="text-lg font-medium">No concerts found.</p>
                <p class="text-sm mt-2">
                    Try adjusting your filters or check back later.
                </p>
            </div>
        </div>
    </AppLayout>
</template>
