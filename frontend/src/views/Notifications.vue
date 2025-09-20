<script setup>
import { Icon } from '@iconify/vue';
import PageComponent from '../components/PageComponent.vue';
import { useAuthStore } from '../store/auth';
import { onMounted, ref } from 'vue';
import api from '../services/api';

const auth = useAuthStore();
const notifications = ref([]);
const loading = ref(false);

const fetchNotifications = async () => {
    loading.value = true;
    try {
        const response = await api.get('/api/notifications');
        notifications.value = response.data.data;
    } catch (error) {
        console.error(error);
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    fetchNotifications();
    console.log(notifications.value);
      window.Echo.private(`App.Models.User.${auth.user.id}`)
    .notification((notification) => {
      console.log("Yeni bildirim geldi:", notification);
      notifications.value.unshift(notification);
    });
})
</script>

<template>
    <PageComponent title="Dashboard">
        <template v-slot:header>
            <div class="flex items-center gap-2">
                <Icon icon="mdi:bell-outline" class="h-6 w-6 text-indigo-600 mt-1" />
                <h1 class="text-3xl font-bold text-gray-900">
                    Notifications
                </h1>
            </div>
        </template>
        <div class="mt-6 space-y-4">
            <div v-if="loading" class="flex justify-center items-center py-10">
                <Icon icon="mingcute:loading-fill" class="animate-spin text-4xl text-blue-500" />
            </div>

            <div
                v-for="notification in notifications"
                v-if="!loading"
                :key="notification.id"
                class="flex items-start p-4 rounded-xl shadow-sm bg-white border border-gray-200 hover:shadow-md transition"
            >

                <div class="flex-1">
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold text-gray-900 text-base">
                            {{ notification.data.title }}
                        </h3>
                        <span class="text-xs text-gray-400">
                            {{ new Date(notification.created_at).toLocaleString() }}
                        </span>
                    </div>
                    <p class="mt-1 text-gray-600 text-sm">
                        {{ notification.data.message }}
                    </p>
                </div>
            </div>

            <div v-if="notifications.length === 0 && !loading" class="text-center text-gray-500 rounded-xl">
                <Icon icon="mdi:bell-off-outline" class="h-6 w-6 text-gray-400 mx-auto mb-2" />
                Henüz bildiriminiz yok.
            </div>
        </div>

    </PageComponent>
</template>

<style scoped>
</style>
