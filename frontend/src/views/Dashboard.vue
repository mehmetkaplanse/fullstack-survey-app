<script setup>
import { Icon } from '@iconify/vue';
import PageComponent from '../components/PageComponent.vue';
import { useAuthStore } from '../store/auth';
import { onMounted, ref } from 'vue';
import api from '../services/api';

const auth = useAuthStore();
const dashboard = ref({});
const loading = ref(true);

const fetchDashboard = async () => {
    try {
        loading.value = true;
        const response = await api.get('/api/dashboard');
        dashboard.value = response.data;
    } catch (error) {
        console.error(error);
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    fetchDashboard();
})
</script>

<template>
    <PageComponent title="Dashboard">
        <template v-slot:header>
            <h1 class="text-3xl font-bold text-gray-900">
                Dashboard
            </h1>
        </template>
        <h1 class="text-xl">
            Welcome <span class="font-bold">{{ auth.user?.name }}</span> 🎉
        </h1>

        <div v-if="loading" class="flex justify-center items-center py-10">
            <Icon icon="mingcute:loading-fill" class="animate-spin text-4xl text-blue-500" />
        </div>

        <div v-if="!loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 py-6">
            <div class="relative overflow-hidden rounded-xl bg-white shadow-md ring-1 ring-gray-200 p-5 hover:shadow-lg transition-shadow">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-xl font-medium text-gray-500">Total Surveys</h2>
                        <p class="mt-2 text-3xl font-bold text-gray-900">
                            {{ dashboard.totalSurveys }}
                        </p>
                    </div>
                    <div class="shrink-0 inline-flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
                        <Icon icon="mdi:clipboard-text-outline" class="h-7 w-7" />
                    </div>
                </div>
            </div>
            <div class="relative overflow-hidden rounded-xl bg-white shadow-md ring-1 ring-gray-200 p-5 hover:shadow-lg transition-shadow">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-xl font-medium text-gray-500">Total Questions</h2>
                        <p class="mt-2 text-3xl font-bold text-gray-900">
                            {{ dashboard.totalQuestions }}
                        </p>
                    </div>
                    <div class="shrink-0 inline-flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
                        <Icon icon="mdi:clipboard-text-outline" class="h-7 w-7" />
                    </div>
                </div>
            </div>
            <div class="relative overflow-hidden rounded-xl bg-white shadow-md ring-1 ring-gray-200 p-5 hover:shadow-lg transition-shadow">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-xl font-medium text-gray-500">Answered Surveys</h2>
                        <p class="mt-2 text-3xl font-bold text-gray-900">
                            -
                        </p>
                    </div>
                    <div class="shrink-0 inline-flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
                        <Icon icon="mdi:clipboard-text-outline" class="h-7 w-7" />
                    </div>
                </div>
            </div>
        </div>
    </PageComponent>
</template>

<style scoped>
</style>
