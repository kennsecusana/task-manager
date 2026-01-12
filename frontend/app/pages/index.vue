<script setup lang="ts">
import { ref, watch } from 'vue'
import Sortable from 'sortablejs'
import { useAuthStore } from '@@/stores/auth'
import { useTaskStore } from '@@/stores/tasks'
import EmptyState from '@@/components/tasks/EmptyState.vue'
import TaskItem from '@@/components/tasks/TaskItem.vue'
import TaskInput from '@@/components/tasks/TaskInput.vue'
import DeleteDialog from '@@/components/ui/DeleteDialog.vue'

definePageMeta({ middleware: ['auth'] })

const authStore = useAuthStore()
const taskStore = useTaskStore()
const taskListRef = ref<HTMLElement | null>(null)
const showDeleteModal = ref(false)
const taskToDelete = ref<{ id: number; statement: string } | null>(null)
let sortableInstance: Sortable | null = null

const getLocalDateString = (date: Date): string => {
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

onMounted(async () => {
  await taskStore.fetchTasks()
  const today = getLocalDateString(new Date())
  taskStore.setSelectedDate(today)
})

watch(taskListRef, (newVal) => {
  if (newVal && taskStore.filteredTasks.length > 0) {
    if (sortableInstance) sortableInstance.destroy()
    
    sortableInstance = new Sortable(newVal, {
      animation: 150,
      ghostClass: 'dragging-ghost',
      chosenClass: 'dragging-chosen',
      handle: '.task-card',
      onEnd: async ({ oldIndex, newIndex }) => {
        if (oldIndex === undefined || newIndex === undefined || oldIndex === newIndex) return
        
        const tasks = [...taskStore.filteredTasks]
        const [movedTask] = tasks.splice(oldIndex, 1)
        tasks.splice(newIndex, 0, movedTask!)
        
        await taskStore.reorderTasks({
          tasks: tasks.map((task, index) => ({ id: task.id, sort_order: index + 1 }))
        })
      }
    })
  }
}, { immediate: true })

onBeforeUnmount(() => {
  if (sortableInstance) sortableInstance.destroy()
})

const handleCreateTask = async (statement: string) => {
  const today = getLocalDateString(new Date())
  await taskStore.createTask({
    statement,
    task_date: taskStore.selectedDate ?? today
  })
}

const handleDeleteTask = (id: number) => {
  const task = taskStore.filteredTasks.find(t => t.id === id)
  if (task) {
    taskToDelete.value = { id, statement: task.statement }
    showDeleteModal.value = true
  }
}

const confirmDelete = async () => {
  if (taskToDelete.value) {
    await taskStore.deleteTask(taskToDelete.value.id)
    showDeleteModal.value = false
    taskToDelete.value = null
  }
}
</script>

<template>
  <div>
    <div v-if="taskStore.isLoading" class="flex items-center justify-center h-64">
      <div class="text-gray-500">Loading tasks...</div>
    </div>
    
    <div v-else-if="taskStore.error" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
      <p class="text-red-800">{{ taskStore.error }}</p>
    </div>
    
    <EmptyState v-else-if="taskStore.filteredTasks.length === 0" @submit="handleCreateTask" />
    
    <div v-else class="space-y-3 w-full max-w-4xl mx-auto">
      <div ref="taskListRef">
        <TaskItem
          v-for="task in taskStore.filteredTasks"
          :key="task.id"
          :task="task"
          class="mb-3"
          @toggle="taskStore.toggleTask"
          @delete="handleDeleteTask"
        />
      </div>
      
      <div class="fixed bottom-0 pb-6 w-full max-w-4xl">
          <TaskInput @submit="handleCreateTask" />
      </div>
    </div>

    <DeleteDialog
      v-model:open="showDeleteModal"
      :task-statement="taskToDelete?.statement ?? ''"
      @confirm="confirmDelete"
    />
  </div>
</template>