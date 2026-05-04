<template>
    <div class="p-8 bg-gray-50 min-h-screen">
        <h1 class="text-4xl font-light text-gray-900 mb-8">Phone numbers</h1>

        <!-- Filters Section -->
        <div class="flex flex-col gap-2 mb-6">
            <div class="flex gap-4">
                <div class="flex flex-col gap-1">
                    <select
                        v-model="filters.country"
                        @change="fetchData()"
                        class="border p-2 rounded bg-white shadow-sm focus:ring-2"
                        :class="errors.country ? 'border-red-500' : 'border-gray-300'"
                    >
                        <option value="">Select country</option>
                        <option v-for="country in countries" :key="country.label" :value="country.value">
                            {{ country.label }}
                        </option>
                    </select>
                    <!-- Display Country Errors -->
                    <span v-if="errors.country" class="text-red-500 text-xs">
                        {{ errors.country.join(', ') }}
                    </span>
                </div>

                <div class="flex flex-col gap-1">
                    <select
                        v-model="filters.state"
                        @change="fetchData()"
                        class="border p-2 rounded bg-white shadow-sm focus:ring-2"
                        :class="errors.state ? 'border-red-500' : 'border-gray-300'"
                    >
                        <option value="">Filter by state</option>
                        <option v-for="state in phoneStates" :key="state.label" :value="state.value">
                            {{ state.label }}
                        </option>
                    </select>
                    <!-- Display State Errors -->
                    <span v-if="errors.state" class="text-red-500 text-xs">
                        {{ errors.state.join(', ') }}
                    </span>
                </div>

                <div class="flex flex-col gap-1">
                    <select
                        v-model="filters.per_page"
                        @change="fetchData()"
                        class="border p-2 rounded bg-white shadow-sm focus:ring-2"
                    >
                        <option value="">Per page</option>
                        <option v-for="size in [5, 10, 15, 20]" :key="size" :value="size">
                            {{ size }}
                        </option>
                    </select>
                    <span v-if="errors.page" class="text-red-500 text-xs">
                        {{ errors.page.join(', ') }}
                    </span>
                    <span v-if="errors.per_page" class="text-red-500 text-xs">
                        {{ errors.per_page.join(', ') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="bg-white shadow-md rounded-sm overflow-hidden border border-gray-300">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-100 border-b border-gray-300">
                <tr>
                    <th class="p-3 border-r border-gray-300">Country</th>
                    <th class="p-3 border-r border-gray-300">State</th>
                    <th class="p-3 border-r border-gray-300">Country code</th>
                    <th class="p-3">Phone num.</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="phone in results" :key="phone.number" class="border-b border-gray-200 hover:bg-gray-50">
                    <td class="p-3 border-r border-gray-300">{{ phone.country_name }}</td>
                    <td class="p-3 border-r border-gray-300 font-bold"
                        :class="phone.state === 'OK' ? 'text-green-600' : 'text-red-600'">
                        {{ phone.state }}
                    </td>
                    <td class="p-3 border-r border-gray-300">{{ phone.country_code }}</td>
                    <td class="p-3">{{ phone.number }}</td>
                </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        <div class="mt-6 flex justify-end gap-2">
            <button
                @click="fetchData(pagination.current_page - 1)"
                :disabled="!pagination.has_prev"
                class="px-4 py-2 bg-white border border-gray-300 rounded shadow-sm hover:bg-gray-100 disabled:opacity-50"
            >
                &lsaquo; Prev
            </button>
            <button
                @click="fetchData(pagination.current_page + 1)"
                :disabled="!pagination.has_next"
                class="px-4 py-2 bg-white border border-gray-300 rounded shadow-sm hover:bg-gray-100 disabled:opacity-50"
            >
                Next &rsaquo;
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

// State
const results = ref([]);
const errors = ref({});
const countries = ref([]);
const phoneStates = ref([]);

const filters = ref({
    country: '',
    state: '',
    per_page: 5
});
const pagination = ref({
    current_page: 1,
    has_next: false,
    has_prev: false,
});

const fetchData = async (page = 1) => {
    errors.value = {};

    try {
        const params = {
            page: page,
            per_page: filters.value.per_page
        };

        if (filters.value.country) {
            params.country = filters.value.country;
        }

        if (filters.value.state) {
            params.state = filters.value.state;
        }

        const query = new URLSearchParams(params).toString();

        const response = await fetch(`/api/phone-numbers?${query}`, {
            headers: {
                'Accept': 'application/json',
            }
        });
        const data = await response.json();

        if (response.status === 422) {
            errors.value = data.errors;
            return;
        }

        results.value = data.data;
        console.log(data.links.next);
        pagination.value = {
            current_page: data.meta.current_page,  // correct path
            has_next:     data.links.next !== null, // replaces last_page
            has_prev:     data.links.prev !== null,
        };
    } catch (error) {
        console.error("Error fetching phone numbers:", error);
    }
};

const fetchDropdowns = async () => {
    try {
        const response = await fetch('/api/dropdowns');
        const data = await response.json();
        countries.value = data.countries;
        phoneStates.value = data.phone_states;
    } catch (error) {
        console.error("Error fetching dropdowns:", error);
    }
};

onMounted(() => {
    fetchData();
    fetchDropdowns()
});
</script>
