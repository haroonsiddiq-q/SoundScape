<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import StyledTable from "@/Components/StyledTable.vue";
import { ref, watch } from "vue";
import { debounce } from "lodash-es";
import { EyeIcon } from "@heroicons/vue/24/solid";

const props = defineProps({
    promoters: Object,
    is_verified: Boolean,
    filters: Object,
});

const search = ref(props.filters?.search || "");

watch(
    search,
    debounce((value) => {
        router.get(
            route("admin.promoter.index"),
            { search: value },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            }
        );
    }, 300)
);

const updateVerificationStatus = (promoter) => {
    router.put(
        route("admin.promoter.updateVerificationStatus", promoter.id),
        {
            is_verified: !promoter.is_verified,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};
</script>

<template>
    <AdminLayout title="Promoter">

        <Head title="Promoter Management" />
        <StyledTable :items="promoters" v-model:search="search">
            <template #header>
                <tr>
                    <th scope="col" class="px-4 py-3 pl-6 text-left">
                        Name
                    </th>
                    <th scope="col" class="px-4 py-3 text-left">Email</th>
                    <th scope="col" class="px-4 py-3 whitespace-nowrap">Toggle Status</th>
                    <th scope="col" class="px-4 py-3 pr-6 text-end"></th>
                </tr>
            </template>

            <template #body>
                <tr v-for="promoter in promoters" :key="promoter.id">
                    <td class="pl-6 px-4 py-3 text-sm text-text">
                        {{ promoter.fullname }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-text">
                        {{ promoter.user.email }}
                    </td>
                    <td class="px-4 py-3 text-center whitespace-nowrap text-sm uppercase">
                        <span @click="updateVerificationStatus(promoter)"
                            class="px-2 py-1 rounded-md text-xs font-semibold cursor-pointer" :class="promoter.is_verified
                                    ? 'bg-primary text-white'
                                    : 'bg-card text-text'
                                ">
                            {{
                                promoter.is_verified
                                    ? "Verified"
                                    : "Not Verified"
                            }}
                        </span>
                    </td>
                    <td
                        class="flex justify-end space-x-2 px-4 py-3 pr-6 whitespace-nowrap text-sm text-white font-semibold">
                        <Link :href="route('admin.promoter.detail', {
                            promoter: promoter.id,
                        })
                            "
                            class="flex items-center px-2 h-6 w-fit space-x-1 bg-primary hover:bg-primary-hover rounded-md transition-colors duration-200">
                            <EyeIcon class="w-4 h-4" />
                            <span class="pb-1">View Details</span>
                        </Link>
                    </td>
                </tr>
            </template>
        </StyledTable>
    </AdminLayout>
</template>