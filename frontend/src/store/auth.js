import {defineStore} from "pinia";
import {ref} from "vue";

export const useAuthStore = defineStore('auth', () => {
    const token = ref(null);
    const user = ref(null);
    const isAuthenticated = ref(false);

    const setToken = (t) => {
        token.value = t;
    }

    const clearToken = () => {
        token.value = null;
    }

    const setUser = (u) => {
        user.value = u;
    }

    const clearUser = () => {
        user.value = null;
    }

    const hasToken = () => {
        isAuthenticated.value = token.value !== null;
        return token.value !== null;
    }

    const logout = () => {
        token.value = null;
        isAuthenticated.value = false;
        user.value = null;
    }

    return {
        token,
        user,
        isAuthenticated,
        setToken,
        clearToken,
        hasToken,
        logout,
        setUser,
        clearUser
    };
}, {
    persist: {
        key: 'auth-store',
        storage: localStorage,
        paths: [
            'token',
            'isAuthenticated',
            'user'
        ]
    }
});
