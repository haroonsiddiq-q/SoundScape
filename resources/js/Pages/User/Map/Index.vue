<script setup>
import { ref, watch } from "vue";
import { Head, usePage } from "@inertiajs/vue3";
import { XMarkIcon } from "@heroicons/vue/24/solid";
import { MapIcon } from "@heroicons/vue/24/outline";
import AppLayout from "@/Layouts/AppLayout.vue";
import LeafletMap from "@/Components/LeafletMap.vue";
import MapDetailCard from "@/Components/MapDetailCard.vue";

const props = defineProps({
    concerts: Array,
    provinces: Array,
});

const page = usePage();

const selectedConcert = ref(null);

const handleMarkerClick = (concert) => {
    selectedConcert.value = concert;
};

const closePanel = () => {
    selectedConcert.value = null;
};

watch(
    () => props.concerts,
    (newConcertsList) => {
        if (selectedConcert.value) {
            const updatedConcert = newConcertsList.find(
                (concert) => concert.id === selectedConcert.value.id
            );

            if (updatedConcert) {
                selectedConcert.value = updatedConcert;
            }
        }
    },
    {
        deep: true,
    }
);
</script>

<template>
    <AppLayout title="Map" :can-login="page.props.canLogin" :can-register="page.props.canRegister">

        <Head title="Map of Concerts" />

        <div class="relative">
            <div class="flex flex-col-reverse md:flex-row gap-2 h-auto md:h-[68vh]">
                <div class="flex flex-col-reverse md:flex-col gap-2 w-full items-center md:w-1/3 lg:w-1/4 h-full">
                    <div class="h-full w-full custom-scrollbar overflow-y-auto bg-card p-4 rounded-md">
                        <div v-if="selectedConcert">
                            <MapDetailCard :concert="selectedConcert" />
                        </div>

                        <div v-else
                            class="flex flex-col items-center justify-center h-full text-center text-text-medium p-6">
                            <MapIcon class="w-16 h-16 text-text-high mb-4" />
                            <h3 class="text-lg font-semibold text-text-high">
                                No concert selected
                            </h3>
                            <p class="text-sm text-text-medium mt-2">
                                Click on a marker on the map to view concert details here.
                            </p>
                        </div>
                    </div>
                    <button v-if="selectedConcert" @click="closePanel"
                        class="flex items-center justify-center p-1 w-1/3 md:w-full rounded-md bg-card text-primary hover:bg-primary hover:text-white transition-colors duration-150"
                        aria-label="Close details">
                        <XMarkIcon class="w-5 h-5 stroke-current stroke-[2px]" />
                    </button>
                </div>

                <div class="h-[68vh] md:h-full md:flex-1 rounded-md overflow-hidden">
                    <LeafletMap :concerts="props.concerts" @marker-click="handleMarkerClick" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
