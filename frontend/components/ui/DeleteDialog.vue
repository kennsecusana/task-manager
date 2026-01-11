<script setup lang="ts">
import { DialogRoot, DialogPortal, DialogOverlay, DialogContent, DialogTitle, DialogDescription, DialogClose } from 'radix-vue'
import { X } from 'lucide-vue-next'

interface Props {
  open: boolean
  taskStatement: string
}

defineProps<Props>()
const emit = defineEmits<{
  'update:open': [value: boolean]
  confirm: []
}>()
</script>

<template>
  <DialogRoot :open="open" @update:open="emit('update:open', $event)">
    <DialogPortal>
      <DialogOverlay class="fixed inset-0 z-50 bg-black/50" />
      <DialogContent class="fixed left-1/2 top-1/2 z-50 w-full max-w-md -translate-x-1/2 -translate-y-1/2 rounded-xl bg-white p-6 shadow-xl">
        <DialogClose class="absolute right-4 top-4 text-gray-400 hover:text-gray-600 transition-colors">
          <X class="h-5 w-5" />
        </DialogClose>
        
        <DialogTitle class="text-lg font-semibold text-gray-900 mb-2">
          Delete Task
        </DialogTitle>
        
        <DialogDescription class="text-sm text-gray-600 mb-4">
          Are you sure you want to delete this task?
        </DialogDescription>
        
        <div class="bg-gray-50 rounded-lg p-3 mb-6">
          <p class="text-sm text-gray-700">{{ taskStatement }}</p>
        </div>
        
        <div class="flex gap-3">
          <button
            @click="emit('update:open', false)"
            class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors"
          >
            Cancel
          </button>
          <button
            @click="emit('confirm')"
            class="flex-1 px-4 py-2.5 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition-colors"
          >
            Delete
          </button>
        </div>
      </DialogContent>
    </DialogPortal>
  </DialogRoot>
</template>