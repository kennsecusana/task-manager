<script setup lang="ts">
import { ref, computed, nextTick } from 'vue'
import { Check, Trash2 } from 'lucide-vue-next'
import { useTaskStore } from '../../stores/tasks'
import type { Task } from '../../types'

interface Props {
  task: Task
}

const props = defineProps<Props>()
const emit = defineEmits<{
  toggle: [id: number]
  delete: [id: number]
}>()

const taskStore = useTaskStore()
const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']

const isEditing = ref(false)
const editText = ref('')
const textareaRef = ref<HTMLTextAreaElement | null>(null)

const formatDate = (dateStr: string) => {
  const date = new Date(dateStr + 'T00:00:00')
  date.setHours(0, 0, 0, 0)
  
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  
  const diffDays = Math.floor((today.getTime() - date.getTime()) / (1000 * 60 * 60 * 24))
  
  if (diffDays === 0) return 'Today'
  if (diffDays === 1) return 'Yesterday'
  
  const monthDay = `${MONTHS[date.getMonth()]} ${date.getDate()}`
  return date.getFullYear() !== today.getFullYear() ? `${monthDay}, ${date.getFullYear()}` : monthDay
}

const showDate = computed(() => taskStore.selectedDate === null)

const startEdit = (e: Event) => {
  e.stopPropagation()
  if (props.task.is_completed || isEditing.value) return
  isEditing.value = true
  editText.value = props.task.statement
  nextTick(() => {
    textareaRef.value?.focus()
    textareaRef.value?.select()
  })
}

const saveEdit = async () => {
  if (!editText.value.trim() || editText.value === props.task.statement) {
    cancelEdit()
    return
  }
  
  await taskStore.updateTask(props.task.id, { statement: editText.value.trim() })
  isEditing.value = false
}

const cancelEdit = () => {
  isEditing.value = false
  editText.value = ''
}

const handleKeydown = (e: KeyboardEvent) => {
  if (e.key === 'Enter' && !e.shiftKey) {
    e.preventDefault()
    saveEdit()
  } else if (e.key === 'Escape') {
    cancelEdit()
  }
}
</script>

<template>
  <div 
    class="task-card flex items-center gap-4 p-2 bg-white rounded-2xl border border-gray-200 transition-all"
    :class="{ 
      'hover:bg-gray-50 cursor-grab': !isEditing && !task.is_completed,
      'shadow-sm': isEditing,
      'cursor-default': isEditing
    }"
  >
    <button
      @click.stop="emit('toggle', task.id)"
      class="flex-shrink-0 w-4 h-4 rounded-full border-2 flex items-center justify-center transition-all"
      :class="task.is_completed ? 'bg-black border-black' : 'border-gray-300 hover:border-gray-400'"
    >
      <Check v-if="task.is_completed" class="w-2.5 h-2.5 text-white" :stroke-width="3" />
    </button>
    
    <div class="flex-1 flex flex-col">
      <p 
        v-if="!isEditing"
        @click="startEdit"
        class="text-base text-gray-800 leading-relaxed cursor-text hover:text-gray-600 transition-colors w-fit" 
        :class="task.is_completed ? 'line-through text-gray-400' : ''"
      >
        {{ task.statement }}
      </p>
      
      <textarea
        v-else
        ref="textareaRef"
        v-model="editText"
        @click.stop
        @blur="saveEdit"
        @keydown="handleKeydown"
        class="w-full text-base text-gray-800 leading-relaxed bg-transparent border-none outline-none resize-none p-0 focus:ring-0"
        rows="2"
      />
      
      <p v-if="showDate && !isEditing" class="text-xs text-gray-400 mt-1">
        {{ formatDate(task.task_date) }}
      </p>
    </div>
    
    <button
      @click.stop="emit('delete', task.id)"
      class="flex-shrink-0 w-9 h-9 flex items-center justify-center text-gray-300 hover:text-gray-400 transition-colors rounded-lg hover:bg-gray-50"
    >
      <Trash2 class="w-5 h-5" />
    </button>
  </div>
</template>