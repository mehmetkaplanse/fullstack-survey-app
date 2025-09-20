import {createPinia} from "pinia";
import piniaPluginPersistedState from 'pinia-plugin-persistedstate'
import {useAuthStore} from "./auth.js";
import {useNotificationsStore} from "./notifications.js";

export {
    useAuthStore,
    useNotificationsStore
}

export default createPinia()
    .use(piniaPluginPersistedState);

