<script setup>
import { useAuthStore } from '@/store/auth';
import {Icon} from '@iconify/vue';
import { onMounted } from 'vue';
import api from '../services/api';
import { useRouter } from 'vue-router';

const auth = useAuthStore();
const router = useRouter();

const handleGoogleLogin = async () => {
    try {
        window.location.href = import.meta.env.VITE_API_BASE_URL + '/auth/google';
    } catch (error) {
        console.error('Google login failed:', error);
    }
};

onMounted(async () => {
    try {
        const urlParams = new URLSearchParams(window.location.search);
        const key = urlParams.get("oauth_key");
        const status = urlParams.get("status");
        const message = urlParams.get("message");

        if (key) {
            const response = await api.post('/api/auth/login-or-register', {
                oauth_key: key
            });

            if (response.status === 200) {
                const { token, user } = response.data;
                auth.setToken(token);
                auth.setUser(user);
                router.push({ name: 'dashboard' });
            }
        }
    } catch (error) {
        console.error('Error during Google login:', error);
    }
})

</script>

<template>
    <div class="sm:mx-auto sm:w-full sm:max-w-sm">
        <img class="mx-auto h-10 w-auto"
            src="https://tailwindcss.com/plus-assets/img/logos/mark.svg?color=indigo&shade=500" alt="Your Company" />
        <h2 class="mt-10 text-center text-2xl/9 font-bold tracking-tight text-white">Sign in to your account</h2>
    </div>

    <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
        <div class="space-y-6">
            <div>
                <label for="email" class="block text-sm/6 font-medium text-gray-100">Email address</label>
                <div class="mt-2">
                    <input type="email" name="email" id="email" required=""
                        class="block w-full rounded-md bg-white/5 px-3 py-1.5 text-base text-white outline-1 -outline-offset-1 outline-white/10 placeholder:text-gray-500 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-500 sm:text-sm/6" />
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between">
                    <label for="password" class="block text-sm/6 font-medium text-gray-100">Password</label>
                    <div class="text-sm">
                        <a href="#" class="font-semibold text-indigo-400 hover:text-indigo-300">Forgot password?</a>
                    </div>
                </div>
                <div class="mt-2">
                    <input type="password" name="password" id="password"  required=""
                        class="block w-full rounded-md bg-white/5 px-3 py-1.5 text-base text-white outline-1 -outline-offset-1 outline-white/10 placeholder:text-gray-500 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-500 sm:text-sm/6" />
                </div>
            </div>

            <div>
                <button
                    class="flex w-full justify-center rounded-md bg-indigo-500 px-3 py-3 text-base font-semibold text-white hover:bg-indigo-400 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500">
                    Sign in
                </button>
            </div>
            <div>
                <button @click="handleGoogleLogin"
                    class="flex items-center gap-2 w-full justify-center rounded-md bg-white px-3 py-3 text-base font-semibold text-black cursor-pointer">
                    <icon icon="devicon:google" />
                    Google ile Devam Ettttt
                </button>
            </div>
        </div>

        <p class="mt-10 text-center text-sm/6 text-gray-400">
            Not a member?
            {{ ' ' }}
            <a href="#" class="font-semibold text-indigo-400 hover:text-indigo-300">Start a 14 day free trial</a>
        </p>
    </div>
</template>

<style scoped></style>
