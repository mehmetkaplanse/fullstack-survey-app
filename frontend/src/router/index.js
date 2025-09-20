import { createWebHistory, createRouter } from 'vue-router'
import Dashboard from '../views/Dashboard.vue'
import Login from '../views/Login.vue'
import Register from '../views/Register.vue'
import Surveys from '../views/Surveys.vue'
import DefaultLayout from '../components/DefaultLayout.vue'
import AuthLayout from '../components/AuthLayout.vue'
import {useAuthStore} from "../store/auth.js";
import Survey from '../views/Survey.vue'
import SurveyDetail from '../views/SurveyDetail.vue'
import Notifications from '../views/Notifications.vue'

const routes = [
    {
        path: '/',
        redirect: '/dashboard',
        name: 'dashboard-layout',
        component: DefaultLayout,
        meta: {requiresAuth: true},
        children: [
            {
                path: '/dashboard',
                name: 'dashboard',
                component: Dashboard
            },
            {
                path: '/surveys',
                name: 'surveys',
                component: Surveys
            },
            {
                path: '/surveys/create',
                name: 'surveys-create',
                component: Survey
            },
            {
                path: '/surveys/:id',
                name: 'survey',
                component: Survey
            },
            {
                path: '/survey-detail/:slug',
                name: 'survey-detail',
                component: SurveyDetail
            },
               {
                path: '/notifications',
                name: 'notifications',
                component: Notifications
            },
        ]
    },
    {
        path: '/auth/callback',
        redirect: '/login',
        name: 'auth-callback',
        component: AuthLayout,
        meta: {isGuest: true},
        children: [
            {
                path: '/login',
                name: 'login',
                component: Login
            },
            {
                path: '/register',
                name: 'register',
                component: Register
            },
        ]
    },
    {
        path: '/:pathMatch(.*)*',
        redirect: '/'
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})


router.beforeEach((to, from, next) => {
    const authStore = useAuthStore();
    if (to.meta.requiresAuth) {
        if (!authStore.token) {
            next({ name: 'login' });
        } else {
            next();
        }
    }
    else if(authStore.token && (to.meta.isGuest)) {
        next({name: 'dashboard'})
    }
    else {
        next();
    }
});


export default router;

