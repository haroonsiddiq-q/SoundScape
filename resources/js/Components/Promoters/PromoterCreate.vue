<script setup>
import { ref, watch, onMounted } from "vue";
import { router } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import DropdownSelector from "@/Components/DropdownSelector.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import {
    UserIcon,
    BuildingStorefrontIcon,
    MapPinIcon,
    GlobeAltIcon,
    PlusIcon,
    TrashIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    form: Object,
    isUpdate: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["submit"]);

const goToHome = () => {
    router.visit(route("index"));
};

const BusinessCategories = [
    { value: "individual", name: "Individual" },
    { value: "company", name: "Company" },
    { value: "nightclub", name: "Nightclub/Location" },
    { value: "other", name: "Other" },
];

const platformPatterns = [
    {
        key: "facebook",
        regex: /facebook\.com|fb\.com/i,
        label: "Facebook",
        color: "text-primary",
        icon: "M16,2c-7.732,0-14,6.268-14,14,0,6.566,4.52,12.075,10.618,13.588v-9.31h-2.887v-4.278h2.887v-1.843c0-4.765,2.156-6.974,6.835-6.974,.887,0,2.417,.174,3.043,.348v3.878c-.33-.035-.904-.052-1.617-.052-2.296,0-3.183,.87-3.183,3.13v1.513h4.573l-.786,4.278h-3.787v9.619c6.932-.837,12.304-6.74,12.304-13.897,0-7.732-6.268-14-14-14Z",
    },
    {
        key: "instagram",
        regex: /instagram\.com/i,
        label: "Instagram",
        color: "text-primary",
        icon: "M10.202,2.098c-1.49,.07-2.507,.308-3.396,.657-.92,.359-1.7,.84-2.477,1.619-.776,.779-1.254,1.56-1.61,2.481-.345,.891-.578,1.909-.644,3.4-.066,1.49-.08,1.97-.073,5.771s.024,4.278,.096,5.772c.071,1.489,.308,2.506,.657,3.396,.359,.92,.84,1.7,1.619,2.477,.779,.776,1.559,1.253,2.483,1.61,.89,.344,1.909,.579,3.399,.644,1.49,.065,1.97,.08,5.771,.073,3.801-.007,4.279-.024,5.773-.095s2.505-.309,3.395-.657c.92-.36,1.701-.84,2.477-1.62s1.254-1.561,1.609-2.483c.345-.89,.579-1.909,.644-3.398,.065-1.494,.081-1.971,.073-5.773s-.024-4.278-.095-5.771-.308-2.507-.657-3.397c-.36-.92-.84-1.7-1.619-2.477s-1.561-1.254-2.483-1.609c-.891-.345-1.909-.58-3.399-.644s-1.97-.081-5.772-.074-4.278,.024-5.771,.096m.164,25.309c-1.365-.059-2.106-.286-2.6-.476-.654-.252-1.12-.557-1.612-1.044s-.795-.955-1.05-1.608c-.192-.494-.423-1.234-.487-2.599-.069-1.475-.084-1.918-.092-5.656s.006-4.18,.071-5.656c.058-1.364,.286-2.106,.476-2.6,.252-.655,.556-1.12,1.044-1.612s.955-.795,1.608-1.05c.493-.193,1.234-.422,2.598-.487,1.476-.07,1.919-.084,5.656-.092,3.737-.008,4.181,.006,5.658,.071,1.364,.059,2.106,.285,2.599,.476,.654,.252,1.12,.555,1.612,1.044s.795,.954,1.051,1.609c.193,.492,.422,1.232,.486,2.597,.07,1.476,.086,1.919,.093,5.656,.007,3.737-.006,4.181-.071,5.656-.06,1.365-.286,2.106-.476,2.601-.252,.654-.556,1.12-1.045,1.612s-.955,.795-1.608,1.05c-.493,.192-1.234,.422-2.597,.487-1.476,.069-1.919,.084-5.657,.092s-4.18-.007-5.656-.071M21.779,8.517c.002,.928,.755,1.679,1.683,1.677s1.679-.755,1.677-1.683c-.002-.928-.755-1.679-1.683-1.677,0,0,0,0,0,0-.928,.002-1.678,.755-1.677,1.683m-12.967,7.496c.008,3.97,3.232,7.182,7.202,7.174s7.183-3.232,7.176-7.202c-.008-3.97-3.233-7.183-7.203-7.175s-7.182,3.233-7.174,7.203m2.522-.005c-.005-2.577,2.08-4.671,4.658-4.676,2.577-.005,4.671,2.08,4.676,4.658,.005,2.577-2.08,4.671-4.658,4.676-2.577,.005-4.671-2.079-4.676-4.656h0",
    },
    {
        key: "tiktok",
        regex: /tiktok\.com/i,
        label: "TikTok",
        color: "text-primary",
        icon: "M24.562,7.613c-1.508-.983-2.597-2.557-2.936-4.391-.073-.396-.114-.804-.114-1.221h-4.814l-.008,19.292c-.081,2.16-1.859,3.894-4.039,3.894-.677,0-1.315-.169-1.877-.465-1.288-.678-2.169-2.028-2.169-3.582,0-2.231,1.815-4.047,4.046-4.047,.417,0,.816,.069,1.194,.187v-4.914c-.391-.053-.788-.087-1.194-.087-4.886,0-8.86,3.975-8.86,8.86,0,2.998,1.498,5.65,3.783,7.254,1.439,1.01,3.19,1.606,5.078,1.606,4.886,0,8.86-3.975,8.86-8.86V11.357c1.888,1.355,4.201,2.154,6.697,2.154v-4.814c-1.345,0-2.597-.4-3.647-1.085Z",
    },
    {
        key: "x",
        regex: /twitter\.com|x\.com/i,
        label: "X",
        color: "text-primary",
        icon: "M18.42,14.009L27.891,3h-2.244l-8.224,9.559L10.855,3H3.28l9.932,14.455L3.28,29h2.244l8.684-10.095,6.936,10.095h7.576l-10.301-14.991h0Zm-3.074,3.573l-1.006-1.439L6.333,4.69h3.447l6.462,9.243,1.006,1.439,8.4,12.015h-3.447l-6.854-9.804h0Z",
    },
    {
        key: "youtube",
        regex: /youtube\.com|youtu\.be/i,
        label: "YouTube",
        color: "text-primary",
        icon: "M31.331,8.248c-.368-1.386-1.452-2.477-2.829-2.848-2.496-.673-12.502-.673-12.502-.673,0,0-10.007,0-12.502,.673-1.377,.37-2.461,1.462-2.829,2.848-.669,2.512-.669,7.752-.669,7.752,0,0,0,5.241,.669,7.752,.368,1.386,1.452,2.477,2.829,2.847,2.496,.673,12.502,.673,12.502,.673,0,0,10.007,0,12.502-.673,1.377-.37,2.461-1.462,2.829-2.847,.669-2.512,.669-7.752,.669-7.752,0,0,0-5.24-.669-7.752ZM12.727,20.758V11.242l8.364,4.758-8.364,4.758Z",
    },
];

const dynamicLinks = ref([]);

onMounted(() => {
    let hasData = false;
    for (const [key, value] of Object.entries(props.form.social_links)) {
        if (value) {
            dynamicLinks.value.push({ url: value, platform: key });
            hasData = true;
        }
    }
    if (!hasData) addLink();
});

const addLink = () => dynamicLinks.value.push({ url: "", platform: "website" });

const removeLink = (index) => {
    dynamicLinks.value.splice(index, 1);
    if (dynamicLinks.value.length === 0) addLink();
};

const detectPlatform = (url) => {
    if (!url) return "website";
    const match = platformPatterns.find((p) => p.regex.test(url));
    return match ? match.key : "website";
};

const getPlatformInfo = (key) =>
    platformPatterns.find((item) => item.key === key);

watch(
    dynamicLinks,
    (newLinks) => {
        Object.keys(props.form.social_links).forEach(
            (key) => (props.form.social_links[key] = ""),
        );
        newLinks.forEach((link) => {
            const platform = detectPlatform(link.url);
            link.platform = platform;
            if (props.form.social_links.hasOwnProperty(platform)) {
                props.form.social_links[platform] = link.url;
            } else {
                props.form.social_links.website = link.url;
            }
        });
    },
    { deep: true },
);

const submit = () => emit("submit");
</script>

<template>
    <form @submit.prevent="submit" class="mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-y-6 gap-x-12 mb-12">
            <div>
                <h3 class="text-xl font-bold">General Information</h3>
                <p class="text-sm text-text-medium mt-2 leading-relaxed">
                    Personal and business details that will be displayed on your
                    public profile.
                </p>
            </div>

            <div class="lg:col-span-2 space-y-6 bg-card shadow-sm rounded-md p-8">
                <div class="space-y-6">
                    <div class="flex items-center gap-2 text-primary">
                        <UserIcon class="h-5 w-5 stroke-[2px]" />
                        <span class="text-xs font-black uppercase tracking-widest">Identity</span>
                        <div class="h-px grow bg-primary-medium ml-2"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1.5">
                            <InputLabel value="Full Name" />
                            <input v-model="form.fullname" type="text"
                                class="w-full text-sm rounded-md focus:ring-primary bg-background border-none focus:border-primary"
                                placeholder="e.g. John Doe" :class="{
                                    'outline-dashed outline-primary -outline-offset-2 rounded-md':
                                        props.form.errors.fullname,
                                }" />
                        </div>
                        <div class="space-y-1.5">
                            <InputLabel value="Business Name" />
                            <input v-model="form.business_name" type="text"
                                class="w-full text-sm rounded-md focus:ring-primary bg-background border-none focus:border-primary"
                                placeholder="e.g. SoundScape" :class="{
                                    'outline-dashed outline-primary -outline-offset-2 rounded-md':
                                        props.form.errors.business_name,
                                }" />
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="flex items-center gap-2 text-primary">
                        <BuildingStorefrontIcon class="h-5 w-5 stroke-[2px]" />
                        <span class="text-xs font-black uppercase tracking-widest">Business Details</span>
                        <div class="h-px grow bg-primary-medium ml-2"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1.5">
                            <InputLabel value="Category" />
                            <DropdownSelector v-model="form.business_category" :options="BusinessCategories" />
                        </div>
                        <div class="space-y-1.5">
                            <InputLabel value="Business Address" />
                            <div class="relative">
                                <input v-model="form.business_address" type="text"
                                    class="w-full text-sm pl-9 rounded-md focus:ring-primary bg-background border-none focus:border-primary"
                                    placeholder="City, Country" :class="{
                                        'outline-dashed outline-primary -outline-offset-2 rounded-md':
                                            props.form.errors.business_address,
                                    }" />
                                <MapPinIcon class="absolute left-3 top-2.5 h-4 w-4" />
                            </div>
                        </div>
                        <div class="md:col-span-2 space-y-1.5">
                            <InputLabel value="Short Bio" />
                            <textarea v-model="form.bio" rows="3"
                                class="w-full text-sm rounded-md focus:ring-primary bg-background border-none focus:border-primary"
                                placeholder="Tell us a bit about what you do..." :class="{
                                    'outline-dashed outline-primary -outline-offset-2 rounded-md':
                                        props.form.errors.bio,
                                }"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-y-6 gap-x-12 mb-12 border-t border-text-low pt-12">
            <div>
                <h3 class="text-xl font-bold">Social Media Platform</h3>
                <p class="text-sm text-text-medium mt-2 leading-relaxed">
                    Connect your platforms. We'll automatically identify the
                    service based on the URL.
                </p>
            </div>

            <div class="lg:col-span-2 space-y-6 bg-card shadow-sm rounded-md p-8">
                <TransitionGroup name="list" tag="div" class="space-y-3">
                    <div v-for="(link, index) in dynamicLinks" :key="index"
                        class="flex items-center gap-1 bg-background px-3 border-none rounded-md transition-all">
                        <div class="w-6 h-6 flex items-center justify-center rounded-md">
                            <GlobeAltIcon v-if="link.platform === 'website'" class="h-5 w-5 text-text-medium" />
                            <svg v-else class="h-5 w-5 fill-current" :class="getPlatformInfo(link.platform)?.color"
                                viewBox="0 0 32 32">
                                <path :d="getPlatformInfo(link.platform)?.icon" />
                            </svg>
                        </div>

                        <input v-model="link.url" type="url"
                            class="grow bg-transparent border-none focus:ring-0 text-sm placeholder:text-text-medium"
                            placeholder="https://..." :class="{
                                'outline-dashed outline-primary -outline-offset-2 rounded-md':
                                    props.form.errors.url,
                            }" />

                        <button type="button" @click="removeLink(index)"
                            class="p-2 text-text-medium hover:text-red-500 rounded-full transition-all">
                            <TrashIcon class="h-5 w-5" />
                        </button>
                    </div>
                </TransitionGroup>

                <button type="button" @click="addLink"
                    class="group flex items-center gap-2 text-sm font-semibold hover:text-primary transition-all px-4">
                    <PlusIcon class="h-4 w-4 stroke-[3px]" />
                    <span>Add another link</span>
                </button>
            </div>
        </div>

        <div class="flex items-center justify-end gap-4 pt-8 border-t border-text-low">
            <p v-if="form.recentlySuccessful" class="text-sm text-text-medium">
                successfully updated.
            </p>
            <SecondaryButton @click="goToHome">Cancel</SecondaryButton>
            <PrimaryButton :disabled="form.processing">
                {{ form.processing ? "Saving..." : (isUpdate ? "Update" : "Create Account") }}
            </PrimaryButton>
        </div>
    </form>
</template>

<style scoped>
.list-enter-active,
.list-leave-active {
    transition: all 0.3s ease;
}

.list-enter-from,
.list-leave-to {
    opacity: 0;
    transform: translateY(10px);
}
</style>
