<script setup>
import { Dialog, DialogPanel, DialogTitle, DialogDescription } from '@headlessui/vue'

const props = defineProps({
  title: { type: String, required: true },
  description: { type: String, default: '' },
  open: { type: Boolean, required: true }
})

const emit = defineEmits(['close'])

</script>

<template>
  <Dialog as="div" class="relative z-50" :open="props.open" @close="emit('close')">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-xs" aria-hidden="true"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
      <DialogPanel class="mx-auto max-w-md rounded-lg bg-white p-6 shadow-xl w-full">
        <DialogTitle class="text-xl font-bold text-gray-900">
          {{ props.title }}
        </DialogTitle>
        <DialogDescription v-if="props.description" class="text-base text-gray-600 mt-2">
          {{ props.description }}
        </DialogDescription>

        <!-- Body slot -->
        <div class="mt-4 text-sm text-gray-700">
          <slot />
        </div>

      </DialogPanel>
    </div>
  </Dialog>
</template>
