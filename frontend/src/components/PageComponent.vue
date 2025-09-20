<script setup>
const props = defineProps({
    title: {
        type: String,
        required: true,
    },
});

</script>

<template>
    <header
        class="relative after:pointer-events-none after:absolute after:inset-x-0 after:inset-y-0 after:border-y after:border-white/10"
    >
        <div class="mx-auto w-full px-4 py-6 sm:px-6 lg:px-8 shadow-sm">
            <slot name="header"></slot>
        </div>
    </header>
    <main>
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <slot></slot>
        </div>
    </main>
</template>
