<script setup>
import { Link, router } from '@inertiajs/vue3';
import DashboardStatCard from '@/Components/DashboardStatCard.vue';
import {
    MusicalNoteIcon,
    UserIcon,
    HeartIcon,
    ChatBubbleBottomCenterTextIcon,
} from '@heroicons/vue/24/outline';

defineProps({
    promoter: {
        type: Object,
        default: () => ({})
    },
    stats: {
        type: Object,
        default: () => ({
            total_concerts: 0,
            total_views: 0,
            total_follows: 0,
            total_comments: 0
        })
    },
    upcomingConcerts: {
        type: Array,
        default: () => []
    },
});
</script>

<template>
    <div>
        <div class="space-y-2">
            <div class="grid grid-cols-2 xl:grid-cols-4 gap-2">
                <DashboardStatCard title="Total Concerts" :value="stats.total_concerts"
                    border-color="shadow-sm border-indigo-500">
                    <template #icon>
                        <MusicalNoteIcon class="w-8 h-8 text-indigo-500 stroke-[2px]" />
                    </template>
                </DashboardStatCard>
                <DashboardStatCard title="Total Views" :value="stats.total_views"
                    border-color="border-blue-500">
                    <template #icon>
                        <UserIcon class="w-8 h-8 text-blue-500 stroke-[2px]" />
                    </template>
                </DashboardStatCard>
                <DashboardStatCard title="Total Follows" :value="stats.total_follows" border-color="border-pink-500">
                    <template #icon>
                        <HeartIcon class="w-8 h-8 text-pink-500 stroke-[2px]" />
                    </template>
                </DashboardStatCard>
                <DashboardStatCard title="Total Comments" :value="stats.total_comments"
                    border-color="border-green-500">
                    <template #icon>
                        <ChatBubbleBottomCenterTextIcon class="w-8 h-8 text-green-500 stroke-[2px]" />
                    </template>
                </DashboardStatCard>
            </div>

            <div class="bg-card overflow-hidden shadow-sm rounded-md">
                <div class="p-6">
                    <h3 class="text-lg font-bold mb-2">Upcoming Concerts</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-text-medium">
                            <thead class="text-xs text-text-high uppercase bg-card-hover">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Concert Name</th>
                                    <th scope="col" class="px-6 py-3">Show Date</th>
                                    <th scope="col" class="px-6 py-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="concert in upcomingConcerts" :key="concert.id"
                                    @click="router.get(route('promoter.concert.detail', concert.id))"
                                    class="bg-card border-b-2 border-background hover:bg-background-hover cursor-pointer">
                                    <td class="px-6 py-4 font-medium whitespace-nowrap">
                                        {{ concert.name }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ new Date(concert.start_show_date).toLocaleDateString('th-TH') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <Link :href="route('promoter.concert.edit', concert.id)"
                                            @click.stop
                                            class="text-primary hover:text-primary-hover">
                                            Edit
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="upcomingConcerts.length === 0">
                                    <td colspan="3" class="px-6 py-4 text-center text-text-medium">
                                        No upcoming concerts available.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
