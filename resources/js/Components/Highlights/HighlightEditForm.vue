<script setup>
import { ref, computed, inject, watch, nextTick } from "vue";

const props = defineProps({
    form: Object,
    highlight: Object,
    concerts: Array,
});

const emit = defineEmits(["submit"]);
const isDarkMode = inject("isDarkMode", ref(false));
const errorSummary = ref(null);

const submit = () => {
    emit("submit");
};

const photoInput = ref(null);
const uploadedPhotoUrl = ref(null);

const selectNewPhoto = () => {
    photoInput.value.click();
};

const updatePhotoPreview = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    props.form.picture_url = file;

    const reader = new FileReader();
    reader.onload = (e) => {
        uploadedPhotoUrl.value = e.target.result;
    };
    reader.readAsDataURL(file);
};

const initialImageUrl = computed(() => {
    if (props.highlight.picture_url) {
        if (props.highlight.picture_url.startsWith("http")) {
            return props.highlight.picture_url;
        }
        return `/storage/${props.highlight.picture_url}`;
    }
    return null;
});

const photoPreview = computed(() => {
    if (uploadedPhotoUrl.value) {
        return uploadedPhotoUrl.value;
    }
    if (initialImageUrl.value) {
        return initialImageUrl.value;
    }
    return isDarkMode.value
        ? "https://placehold.co/800x200/1c1423/ffffff80?text=SOUNDSCAPE"
        : "https://placehold.co/800x200/e5e7eb/00000080?text=SOUNDSCAPE";
});

const is_concert = ref(props.highlight.concert_id ? true : false);

const fieldLabels = {
    title: "Name",
    picture_url: "Picture",
    concert_id: "Concert",
    link: "Link",
};

watch(
    () => props.form.errors,
    (newErrors) => {
        if (newErrors && Object.keys(newErrors).length > 0) {
            nextTick(() => {
                if (errorSummary.value) {
                    errorSummary.value.scrollIntoView({
                        behavior: "smooth",
                        block: "start",
                    });
                }
            });
        }
    },
    { deep: true }
);
</script>

<template>
    <div ref="errorSummary" v-if="Object.keys(props.form.errors).length"
        class="max-w-xl lg:max-w-full mx-auto bg-card lg:shadow-xl rounded-md mb-4 p-6">
        <div>
            <div class="flex flex-col w-full space-y-2">
                <span class="text-lg font-semibold text-primary">Oops! Something went wrong.</span>
                <span class="text-sm">Please check the fields with dashed borders.</span>
                <span></span>
                <ul
                    class="list-disc list-inside space-y-1 pl-5 bg-background p-4 rounded-md outline-dashed -outline-offset-4 text-primary">
                    <li v-for="(errorMessages, fieldName) in props.form.errors" :key="fieldName"
                        class="text-sm text-primary">
                        <strong class="font-bold">{{ fieldLabels[fieldName] || fieldName }}:</strong>
                        <span class="ml-1 text-text">{{ errorMessages }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="bg-card shadow-xl rounded-md p-6 space-y-2">
        <div class="flex-none w-full relative">
            <div v-if="photoPreview" class="relative cursor-pointer" :class="{
                'outline-dashed outline-primary -outline-offset-4 rounded-md':
                    props.form.errors.picture_url,
            }">
                <img :src="photoPreview" class="max-h-96 w-full object-fill rounded-md" />
                <input ref="photoInput" type="file" class="hidden" @change="updatePhotoPreview" accept="image/*" />
                <button type="button" @click.prevent="selectNewPhoto" class="absolute inset-0" />
            </div>
        </div>

        <input type="text" id="title" v-model="props.form.title"
            class="mt-2 w-full bg-background rounded-md font-medium border-none focus:ring-transparent placeholder:font-normal placeholder:text-text-medium"
            :class="{
                'outline-dashed outline-primary -outline-offset-4 rounded-md':
                    props.form.errors.title,
            }" placeholder="NameConcert" />

        <div class="flex flex-col-reverse lg:flex-row gap-2 lg:gap-0 items-center">
            <div class="w-full">
                <div v-if="is_concert" class="w-full lg:w-2/3">
                    <select v-model="props.form.concert_id" id="concert_id"
                        class="block w-full rounded-md bg-background shadow-sm border-0 text-text-medium font-medium focus:outline-none focus:ring-0 focus:border-0"
                        :class="{
                            'outline-dashed outline-primary -outline-offset-4 rounded-md':
                                props.form.errors.concert_id,
                        }">
                        <option :value="null" disabled hidden>
                            Select a concert
                        </option>
                        <option v-for="concert in concerts" :key="concert.id" :value="concert.id">
                            {{ concert.name }}
                        </option>
                    </select>
                </div>
                <div v-else class="w-full lg:w-2/3">
                    <input v-model="props.form.link" type="url" id="link"
                        class="block w-full rounded-md bg-background shadow-sm border-0 placeholder:text-text-medium font-medium focus:outline-none focus:ring-0 focus:border-0"
                        :class="{
                            'outline-dashed outline-primary -outline-offset-4 rounded-md':
                                props.form.errors.link,
                        }" placeholder="https://example.com" />
                </div>
            </div>
            <div class="flex justify-end w-full lg:w-fit items-center space-x-2">
                <span class="text-sm font-medium whitespace-nowrap">Link to Concert?</span>
                <button type="button" @click="is_concert = !is_concert"
                    class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full focus:outline-none cursor-pointer transition-colors ease-in-out duration-200"
                    :class="is_concert ? 'bg-primary' : 'bg-background'">
                    <span aria-hidden="true"
                        class="pointer-events-none inline-block h-5 w-5 rounded-full shadow transform ring-0 transition ease-in-out duration-200"
                        :class="{
                            'translate-x-5 bg-white': is_concert,
                            'translate-x-0 bg-white': !is_concert,
                        }"></span>
                </button>
            </div>
        </div>

        <div class="flex justify-center lg:justify-end">
            <button type="submit" @click="submit" :disabled="props.form.processing"
                class="inline-flex justify-center py-1 px-7 border border-transparent shadow-sm text-sm font-semibold rounded-md text-white bg-primary hover:bg-primary-hover focus:outline-none disabled:opacity-50 transition-colors duration-150">
                Update Highlight
            </button>
        </div>
    </div>
</template>
