<script setup>
import {
    Disclosure,
    DisclosureButton,
    DisclosurePanel,
    Menu,
    MenuButton,
    MenuItem,
    MenuItems,
} from "@headlessui/vue";
import { Bars3Icon, BellIcon, XMarkIcon } from "@heroicons/vue/24/outline";
import { useAuthStore } from "../store/auth";
import { useRouter } from "vue-router";
import api from "../services/api";

const router = useRouter();
const authStore = useAuthStore();
const currentUser = authStore.user;
const user = {
    name: currentUser?.name,
    email: currentUser?.email,
    imageUrl: currentUser?.avatar,
    unreadNotificationsCount: currentUser?.unread_notifications
};

const navigation = [
    { name: "Dashboard", to: { name: "dashboard" } },
    { name: "Surveys", to: { name: "surveys" } },
];

const logout = () => {
    try {
        api.post('/api/auth/logout').then(() => {
            authStore.logout();
            localStorage.removeItem('auth_token');
            window.location.href = '/login';
        }).catch(() => {
            console.error('Logout failed');
        });
    } catch (error) {
        console.error('Error during logout:', error);
    }
};
</script>

<template>
    <div class="min-h-full">
        <Disclosure as="nav" class="bg-gray-800" v-slot="{ open }">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center">
                        <div class="shrink-0 cursor-pointer" @click="router.push({name: 'dashboard'})">
                            <img
                                class="size-8"
                                src="@/assets/vue.svg"
                                alt="Your Company"
                            />
                        </div>
                        <div class="hidden md:block">
                            <div class="ml-10 flex items-baseline space-x-4">
                                <router-link
                                    v-for="item in navigation"
                                    :key="item.name"
                                    :to="item.to"
                                    active-class="bg-gray-900 text-white"
                                    :class="[
                                        this.$route.name === item.to.name
                                            ? ''
                                            : 'text-gray-300 hover:bg-white/5 hover:text-white',
                                        'rounded-md px-3 py-2 text-sm font-medium cursor-pointer',
                                    ]"
                                    >{{ item.name }}
                                </router-link
                                >
                            </div>
                        </div>
                    </div>
                    <div class="hidden md:block">
                        <div class="ml-4 flex items-center md:ml-6">
                            <button
                                type="button"
                                @click="$router.push({ name: 'notifications' })"
                                class="relative rounded-full p-1 cursor-pointer text-gray-400 hover:text-white focus:outline-2 focus:outline-offset-2 focus:outline-indigo-500"
                            >
                                <span class="absolute -inset-1.5" />
                                <span class="sr-only">View notifications</span>
                                <BellIcon class="size-6" aria-hidden="true" />
                                <span
                                    v-if="user.unreadNotificationsCount > 0"
                                    class="absolute -top-1 -right-1 min-w-5 h-5 px-1 flex items-center justify-center rounded-full bg-red-500 text-white text-xs font-bold border-2 border-white"
                                >
                                    {{ user.unreadNotificationsCount }}
                                </span>
                            </button>


                            <!-- Profile dropdown -->
                            <Menu as="div" class="relative ml-3">
                                <MenuButton
                                    class="relative flex max-w-xs items-center rounded-full focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500"
                                >
                                    <span class="absolute -inset-1.5" />
                                    <span class="sr-only">Open user menu</span>
                                    <img
                                        class="size-8 rounded-full outline -outline-offset-1 outline-white/10"
                                        :src="user?.imageUrl"
                                        alt=""
                                    />
                                </MenuButton>

                                <transition
                                    enter-active-class="transition ease-out duration-100"
                                    enter-from-class="transform opacity-0 scale-95"
                                    enter-to-class="transform scale-100"
                                    leave-active-class="transition ease-in duration-75"
                                    leave-from-class="transform scale-100"
                                    leave-to-class="transform opacity-0 scale-95"
                                >
                                    <MenuItems
                                        class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-gray-800 py-1 outline-1 -outline-offset-1 outline-white/10"
                                    >
                                        <MenuItem
                                            v-slot="{ active }"
                                        >
                                            <a
                                                @click="logout"
                                                :class="[
                                                    'block px-4 py-2 text-sm text-gray-300 cursor-pointer',
                                                ]"
                                                >{{ currentUser?.name }}</a
                                            >
                                        </MenuItem>
                                        <MenuItem
                                            v-slot="{ active }"
                                        >
                                            <a
                                                @click="logout"
                                                :class="[
                                                    'block px-4 py-2 text-sm text-red-500 cursor-pointer',
                                                ]"
                                                >Sign out</a
                                            >
                                        </MenuItem>
                                    </MenuItems>
                                </transition>
                            </Menu>
                        </div>
                    </div>
                    <div class="-mr-2 flex md:hidden">
                        <!-- Mobile menu button -->
                        <DisclosureButton
                            class="relative inline-flex items-center justify-center rounded-md p-2 text-gray-400 hover:bg-white/5 hover:text-white focus:outline-2 focus:outline-offset-2 focus:outline-indigo-500"
                        >
                            <span class="absolute -inset-0.5" />
                            <span class="sr-only">Open main menu</span>
                            <Bars3Icon
                                v-if="!open"
                                class="block size-6"
                                aria-hidden="true"
                            />
                            <XMarkIcon
                                v-else
                                class="block size-6"
                                aria-hidden="true"
                            />
                        </DisclosureButton>
                    </div>
                </div>
            </div>

            <DisclosurePanel class="md:hidden">
                <div class="space-y-1 px-2 pt-2 pb-3 sm:px-3">
                    <router-link
                        v-for="item in navigation"
                        :key="item.name"
                        :to="item.to"
                        active-class="bg-gray-900 text-white"
                        :class="[
                            this.$route.name === item.to.name
                                ? ''
                                : 'text-gray-300 hover:bg-white/5 hover:text-white',
                            'block rounded-md px-3 py-2 text-base font-medium cursor-pointer',
                        ]"
                        >{{ item.name }}
                    </router-link
                    >
                </div>
                <div class="border-t border-white/10 pt-4 pb-3">
                    <div class="flex items-center px-5">
                        <div class="shrink-0">
                            <img
                                class="size-10 rounded-full outline -outline-offset-1 outline-white/10"
                                :src="user?.imageUrl"
                                alt=""
                            />
                        </div>
                        <div class="ml-3">
                            <div class="text-base/5 font-medium text-white">
                                {{ user.name }}
                            </div>
                            <div class="text-sm font-medium text-gray-400">
                                {{ user.email }}
                            </div>
                        </div>
                        <button
                            type="button"
                            @click="$router.push({ name: 'notifications' })"
                            class="relative ml-auto cursor-pointer shrink-0 rounded-full p-1 text-gray-400 hover:text-white focus:outline-2 focus:outline-offset-2 focus:outline-indigo-500"
                        >
                            <span class="absolute -inset-1.5" />
                            <span class="sr-only">View notifications</span>
                            <BellIcon class="size-6" aria-hidden="true" />
                            <span
                                v-if="user.unreadNotificationsCount > 0"
                                class="absolute -top-1 -right-1 min-w-5 h-5 px-1 flex items-center justify-center rounded-full bg-red-500 text-white text-xs font-bold border-2 border-white"
                            >
                                {{ user.unreadNotificationsCount }}
                            </span>
                        </button>
                    </div>
                    <div class="mt-3 space-y-1 px-2">
                        <a
                            @click="logout"
                            class="block rounded-md px-3 py-2 text-base font-medium text-gray-400 hover:bg-white/5 hover:text-white cursor-pointer"
                            >Sign out</a
                        >
                    </div>
                </div>
            </DisclosurePanel>
        </Disclosure>

        <router-view></router-view>
    </div>
</template>
