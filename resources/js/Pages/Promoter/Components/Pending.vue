<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import PromoterCreateForm from "@/Components/Promoters/PromoterCreate.vue";

const props = defineProps({
    promoter: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    fullname: props.promoter?.fullname || "",
    business_name: props.promoter?.business_name || "",
    business_category: props.promoter?.business_category || "individual",
    business_address: props.promoter?.business_address || "",
    bio: props.promoter?.bio || "",
    social_links: {
        facebook: props.promoter?.social_links?.facebook || "",
        instagram: props.promoter?.social_links?.instagram || "",
        tiktok: props.promoter?.social_links?.tiktok || "",
        twitter: props.promoter?.social_links?.twitter || "",
        discord: props.promoter?.social_links?.discord || "",
        linkedin: props.promoter?.social_links?.linkedin || "",
        youtube: props.promoter?.social_links?.youtube || "",
        website: props.promoter?.social_links?.website || "",
    },
});

const errorSummary = ref(null);

const fieldLabels = {
    fullname: "Full Name",
    business_name: "Business Name",
    business_category: "Business Category",
    business_address: "Business Address",
    bio: "Bio",
    "social_links.facebook": "Facebook Link",
    "social_links.instagram": "Instagram Link",
    "social_links.tiktok": "Tiktok Link",
    "social_links.twitter": "Twitter Link",
    "social_links.discord": "Discord Link",
    "social_links.linkedin": "LinkedIn Link",
    "social_links.youtube": "YouTube Link",
    "social_links.website": "Website Link",
};

const submit = () => {
    form.put(route("promoter.update"), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout title="Pending Verification">
        <div class="p-6 bg-card shadow sm:rounded-md border border-primary">
            <h2 class="text-lg font-medium text-primary">
                Verification Pending
            </h2>
            <p class="mt-1 text-sm">
                Your promoter account is currently awaiting verification by an
                administrator. You can review and edit your information below
                while you wait.
            </p>
        </div>

        <div ref="errorSummary" v-if="Object.keys(form.errors).length" class="bg-card rounded-md mb-4 p-6 shadow">
            <div>
                <div class="flex flex-col w-full space-y-2">
                    <span class="text-lg font-semibold text-primary">Oops! Something went wrong.</span>
                    <span class="text-sm text-text-medium">Please check the fields with dashed borders.</span>
                    <ul
                        class="list-disc list-inside space-y-1 pl-5 bg-background p-4 rounded-md outline-dashed -outline-offset-4 text-primary">
                        <li v-for="(errorMessages, fieldName) in form.errors" :key="fieldName"
                            class="text-sm text-primary">
                            <strong class="font-bold">{{
                                fieldLabels[fieldName] || fieldName
                            }}:</strong>
                            <span class="ml-1 text-text">{{
                                errorMessages
                                }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="overflow-hidden">
            <PromoterCreateForm :form="form" :isUpdate="true" @submit="submit" />
        </div>
    </AppLayout>
</template>
