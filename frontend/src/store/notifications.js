import {defineStore} from "pinia";
import {ref} from "vue";
import api from "../services/api.js";

export const useNotificationsStore = defineStore('notifications', () => {
    const notifications = ref([]);

    const getNotifications = async () => {
        try {
            const response = await api.get(('/notifications'));
            notifications.value = response.data.data;
            return response.data.data;
        } catch (Error) {
            console.error("Error fetching notifications:", Error);
        }
    }

    return {
        notifications,
        getNotifications,
    };
});
