<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import DropdownSelector from './DropdownSelector.vue';

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: ''
  }
});
const emit = defineEmits(['update:modelValue']);

const provinces = ref([]);
const isLoading = ref(true);
const searchQuery = ref('');

onMounted(async () => {
  try {
    const response = await axios.get('/api/provinces');
    provinces.value = response.data;
  } catch (error) {
    console.error("Failed to fetch provinces:", error);
  } finally {
    isLoading.value = false;
  }
});

const filteredProvinces = computed(() => {
  if (!searchQuery.value) {
    return provinces.value;
  }
  const lowerCaseQuery = searchQuery.value.toLowerCase();
  return provinces.value.filter(province =>
    province.name_en.toLowerCase().includes(lowerCaseQuery)
  );
});

const formattedOptions = computed(() => {
  return filteredProvinces.value.map(province => ({
    name: province.name_en,
    value: province.id,
  }));
});

const selectedProvince = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
});

const placeholderText = computed(() => {
  return isLoading.value ? 'Loading...' : 'Please select a province';
});

function updateSearchQuery(query) {
  searchQuery.value = query;
}
</script>

<template>
  <DropdownSelector v-model="selectedProvince" :options="formattedOptions" :placeholder="placeholderText"
    :is-searchable="true" @search-change="updateSearchQuery" />
</template>