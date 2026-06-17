<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, onMounted, onUnmounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { CheckIcon } from '@heroicons/vue/24/solid';

const currentJob = ref(null);
let pollingInterval = null;

const form = useForm({
    target_type: 'concert',
    target_website: 'highlight',
});

const startScraping = async () => {
    try {
        const formData = new FormData();
        formData.append('target_type', form.target_type);
        formData.append('target_website', form.target_website);

        const response = await axios.post(route('admin.scraper.run'), formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });

        localStorage.setItem('active_scraper_job', response.data.job_id);

        startPolling(response.data.job_id);
    } catch (error) {
        console.error("Failed to start scraper:", error);
        alert("Failed to start the scraper. Check the console.");
    }
};

const cancelScraping = async () => {
    if (!currentJob.value) return;
    if (!confirm("Are you sure you want to stop the scraper?")) return;

    try {
        await axios.post(route('admin.scraper.cancel', currentJob.value.id));

        clearInterval(pollingInterval);
        localStorage.removeItem('active_scraper_job');

        currentJob.value.status = 'failed';
        currentJob.value.error_message = 'Cancelled by Admin';
    } catch (error) {
        console.error("Failed to cancel:", error);
    }
};

onMounted(() => {
    const savedJobId = localStorage.getItem('active_scraper_job');
    if (savedJobId) {
        startPolling(savedJobId);
    }
});

const startPolling = (jobId) => {
    pollingInterval = setInterval(async () => {
        const response = await axios.get(route('admin.scraper.status', jobId));
        currentJob.value = response.data;
        
        if (currentJob.value.status === 'completed' || currentJob.value.status === 'failed') {
            clearInterval(pollingInterval);
            
            localStorage.removeItem('active_scraper_job'); 
        }
    }, 2000);
};

onUnmounted(() => {
    if (pollingInterval) clearInterval(pollingInterval);
});
</script>

<template>
    <AdminLayout title="Highlight Scraper">
        <div>
            <div class="max-w-7xl mx-auto space-y-2">

                <div class="bg-card overflow-hidden shadow-sm rounded-md p-6">
                    <form @submit.prevent="startScraping">
                        <div class="space-y-2">
                            <p class="block text-lg font-semibold mb-2">The system will scrape and fetch the latest events from Highlight
                                automatically</p>
                        </div>

                        <div class="mt-2">
                            <button v-if="currentJob && currentJob.status === 'running'" type="button"
                                @click.prevent="cancelScraping"
                                class="w-full justify-center inline-flex items-center py-2 border-2 border-primary rounded-md hover:font-bold text-sm tracking-wide transition">
                                Cancel
                            </button>

                            <button v-else type="submit"
                                class="w-full justify-center inline-flex items-center py-2 border-2 border-primary rounded-md hover:font-bold text-sm tracking-wide transition">
                                Start Scraper
                            </button>
                        </div>
                    </form>
                </div>

                <div v-if="currentJob" class="bg-card overflow-hidden shadow-sm rounded-md p-6">
                    <h3 class="text-lg font-semibold mb-2">Status</h3>

                    <div v-if="currentJob.status === 'failed'"
                        class="bg-card rounded-md mb-4 border-2 border-dashed border-primary p-3">
                        <span class="font-bold">Error:</span> {{ currentJob.error_message || 'Process shutdown' }}
                    </div>

                    <div class="relative pt-1">
                        <div class="flex mb-2 items-center justify-between">
                            <div>
                                <span
                                    class="text-xs font-semibold inline-block py-1 px-2 rounded-md text-white bg-secondary">
                                    {{ currentJob.status === 'running' ? 'Fetching data' : (currentJob.status ===
                                    'completed' ? 'Succeed' : 'Failed') }}
                                </span>
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-semibold inline-block text-accent">
                                    {{ currentJob.progress }}%
                                </span>
                            </div>
                        </div>
                        <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-background">
                            <div :style="`width: ${currentJob.progress}%`"
                                class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-primary transition-all duration-500">
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <h4 class="text-md mb-3">Retrieved data</h4>
                        <div v-if="!currentJob.results || currentJob.results.length === 0"
                            class="text-sm text-text-medium italic">
                            Fetching data
                        </div>
                        <ul v-else
                            class="bg-background rounded-md border border-card-hover max-h-60 overflow-y-auto p-2">
                            <li v-for="(item, index) in currentJob.results" :key="index"
                                class="py-2 px-3 text-sm flex uppercase items-center">
                                <CheckIcon class="w-5 h-5 text-primary stroke-current mr-2" /> {{ item }}
                            </li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </AdminLayout>
</template>