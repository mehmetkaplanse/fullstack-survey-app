import axios from "axios";
import { useAuthStore } from "../store/auth";


const api = axios.create({
    baseURL: import.meta.env.VITE_API_BASE_URL,
    withCredentials: true,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    }
});

api.interceptors.request.use(
    (config) => {
        const authStore = useAuthStore();
        
        const token = authStore.token;
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
        
        return config;
    },
    (err) => {
        return Promise.reject(err);
    }
);

api.interceptors.response.use(
    (response) => {
        return response;
    },
    (err) => {
        if (err.response) {
            switch (err.response.status) {
                case 401:
                    const authStore = useAuthStore();
                    authStore.clearToken();
                    window.location.href = "/login";
                    break;
                case 403:
                    console.error("Bu işlemi yapmaya yetkiniz yok.");
                    break;
                case 404:
                    console.error("İstediğiniz Sayfa/Kaynak bulunamadı.");
                    break;
                default:
                    if (err.response.data && err.response.data.message) {
                        console.error(err.response.data.message);
                    }
                    break;
            }
        }
        return Promise.reject(err);
    }
);

export default api;