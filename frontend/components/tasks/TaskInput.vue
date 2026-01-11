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
  <div class="relative">
    <input
      v-model="statement"
      type="text"
      placeholder="What else do you need to do?"
      class="w-full px-6 py-4 pr-16 text-gray-800 placeholder-gray-400 bg-white border border-gray-200 rounded-2xl focus:outline-none focus:border-gray-400 transition-colors"
      @keyup.enter="handleSubmit"
    />
    <button
      @click="handleSubmit"
      :disabled="!statement.trim()"
      class="absolute right-3 top-1/2 -translate-y-1/2 w-8 h-8 flex items-center justify-center bg-black text-white rounded-full disabled:opacity-30 hover:scale-105 transition-all"
    >
      <ArrowUp class="w-5 h-5" />
    </button>
  </div>
</template>
