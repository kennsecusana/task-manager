<script setup lang="ts">
import { ref } from 'vue'
import { ArrowUp } from 'lucide-vue-next'

const emit = defineEmits<{
  submit: [statement: string]
}>()

const statement = ref('')

const handleSubmit = () => {
  if (!statement.value.trim()) return
  emit('submit', statement.value.trim())
  statement.value = ''
}
</script>

<template>
  <div class="flex flex-col items-center justify-center min-h-[60vh]">
    <h2 class="text-4xl font-bold text-gray-900 mb-8">
      What do you have in mind?
    </h2>
    
    <div class="relative w-full max-w-4xl">
      <textarea
        v-model="statement"
        placeholder="Write the task you plan to do today here..."
        rows="5"
        class="w-full px-6 py-2 pr-16 text-gray-800 placeholder-gray-400 bg-white border border-gray-200 rounded-2xl focus:outline-none focus:border-gray-400 transition-colors resize-none"
      />
      <button
        @click="handleSubmit"
        :disabled="!statement.trim()"
        class="absolute right-3 bottom-3 w-8 h-8 flex items-center justify-center bg-black text-white rounded-full disabled:opacity-30 hover:scale-105 transition-all"
      >
        <ArrowUp class="w-6 h-6" />
      </button>
    </div>
  </div>
</template>
