<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, watch, computed, inject } from "vue";
import { debounce } from "lodash-es";
import { PlusIcon, PencilIcon, TrashIcon, GlobeAsiaAustraliaIcon } from "@heroicons/vue/24/solid";
import { FolderPlusIcon, MagnifyingGlassIcon } from "@heroicons/vue/24/outline";

const props = defineProps({
    artists: Object,
    filters: Object,
});

const isDarkMode = inject("isDarkMode");
const search = ref(props.filters?.search || "");

const hasArtists = computed(() => {
    return props.artists?.data?.length > 0;
});

watch(
    search,
    debounce((value) => {
        router.get(
            route("admin.artist.index"),
            { search: value },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            }
        );
    }, 300)
);

const getPictureUrl = (artist) => {
    if (artist?.picture_url) {
        if (artist.picture_url.startsWith("http")) {
            return artist.picture_url;
        }
        return `/storage/${artist.picture_url}`;
    }

    const name = encodeURIComponent(artist?.name || "NA");
    return isDarkMode.value
        ? `https://ui-avatars.com/api/?name=${name}&background=1c1423&color=ffffff`
        : `https://ui-avatars.com/api/?name=${name}&background=e5e7eb&color=000000`;
};
</script>

<template>
    <div class="w-full mx-auto rounded-md space-y-6">
        <div class="flex justify-between items-center space-x-4">
            <div class="w-full flex items-center space-x-4">
                <div class="relative w-full">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <MagnifyingGlassIcon class="h-5 w-5 text-text" />
                    </div>
                    <input type="text" name="search" id="search"
                        class="block w-full pl-10 pr-3 p-1 ring-4 ring-card border-none rounded-md leading-5 bg-card focus:outline-none focus:ring-4 focus:ring-card sm:text-sm"
                        placeholder="Search by name..." v-model="search" />
                </div>
            </div>
            <Link :href="route('admin.artist.create')"
                class="flex items-center justify-center py-1 ring-2 ring-primary rounded-md w-8 bg-card">
                <PlusIcon class="w-5 h-5 stroke-current" />
            </Link>
            <Link :href="route('admin.artist.scraper')"
                class="flex items-center justify-center py-1 ring-2 ring-primary rounded-md w-8 bg-card">
                <GlobeAsiaAustraliaIcon class="w-5 h-5" />
            </Link>
        </div>

        <div v-if="hasArtists"
            class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
            <Link v-for="artist in props.artists.data" :href="route('admin.artist.edit', {
                artist: artist.id,
            })
                " :key="artist.id"
                class="bg-card rounded-md shadow-lg overflow-hidden ring-4 ring-card flex flex-col hover:scale-[1.05] transition-transform duration-300">
                <img :src="getPictureUrl(artist)" :alt="artist.name"
                    class="w-full aspect-square object-cover bg-background-hover rounded-md" />
                <h3 class="p-3 text-center font-semibold text-text truncate">
                    {{ artist.name }}
                </h3>
                <div class="flex justify-center px-3 pb-3 pt-0">
                    <Link :href="route('admin.artist.delete', {
                        artist: artist.id,
                    })
                        " method="delete"
                        class="flex items-center px-2 h-6 w-fit space-x-1 bg-red-600 hover:bg-red-700 rounded-md transition-colors duration-200 text-white text-sm">
                        <TrashIcon class="w-4 h-4" />
                        <span>Delete</span>
                    </Link>
                </div>
            </Link>
        </div>

        <div v-else
            class="w-full flex flex-col space-y-2 font-semibold items-center text-text-medium justify-center py-10 rounded-md bg-card ring-4 ring-card">
            <FolderPlusIcon class="w-12 h-12 stroke-current" />
            <p>No artists found</p>
        </div>
    </div>
</template>