import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { Task, TaskCreatePayload, TaskUpdatePayload, TaskReorderPayload } from '@@/types'
import { useApi } from '@@/composables/useApi'

export const useTaskStore = defineStore('tasks', () => {
  const api = useApi()
  
  const allTasks = ref<Task[]>([])
  const tasks = ref<Task[]>([])
  const isLoading = ref(false)
  const error = ref<string | null>(null)
  const selectedDate = ref<string | null>(null)

  const filteredTasks = computed(() => {
    if (!selectedDate.value) return tasks.value
    return tasks.value.filter(t => t.task_date === selectedDate.value)
  })

  const fetchTasks = async (date?: string) => {
    isLoading.value = true
    error.value = null
    
    try {
      const url = date ? `/tasks?date=${date}` : '/tasks'
      const response = await api.get<{ data: Task[] }>(url)
      
      if (!date) {
        allTasks.value = response.data
      }
      tasks.value = response.data
    }
    catch (err: any) {
      error.value = err.message || 'Failed to fetch tasks'
    }
    finally {
      isLoading.value = false
    }
  }

  const createTask = async (payload: TaskCreatePayload) => {
    try {
      const response = await api.post<{ data: Task }>('/tasks', payload)
      
      allTasks.value.push(response.data)
      
      // Re-filter tasks based on selected date
      if (selectedDate.value) {
        tasks.value = allTasks.value.filter(t => t.task_date === selectedDate.value)
      } else {
        tasks.value = [...allTasks.value]
      }
      
      return response.data
    }
    catch (err: any) {
      error.value = err.message || 'Failed to create task'
      throw err
    }
  }

  const updateTask = async (id: number, payload: TaskUpdatePayload) => {
    try {
      const response = await api.patch<{ data: Task }>(`/tasks/${id}`, payload)
      
      // Update in allTasks
      const allIndex = allTasks.value.findIndex(t => t.id === id)
      if (allIndex !== -1) allTasks.value[allIndex] = response.data
      
      // Update in tasks
      const index = tasks.value.findIndex(t => t.id === id)
      if (index !== -1) tasks.value[index] = response.data
      
      return response.data
    }
    catch (err: any) {
      error.value = err.message || 'Failed to update task'
      throw err
    }
  }

  const toggleTask = async (id: number) => {
    const task = tasks.value.find(t => t.id === id)
    if (!task) return
    await updateTask(id, { is_completed: !task.is_completed })
  }

  const deleteTask = async (id: number) => {
    try {
      await api.del(`/tasks/${id}`)
      
      // Remove from both arrays
      allTasks.value = allTasks.value.filter(t => t.id !== id)
      tasks.value = tasks.value.filter(t => t.id !== id)
    }
    catch (err: any) {
      error.value = err.message || 'Failed to delete task'
      throw err
    }
  }

  const searchTasks = async (keyword: string) => {
    if (!keyword.trim()) {
      await fetchTasks()
      return
    }
    
    isLoading.value = true
    try {
      const response = await api.get<{ data: Task[] }>(`/tasks/search?keyword=${encodeURIComponent(keyword)}`)
      allTasks.value = response.data
      tasks.value = response.data
      selectedDate.value = null
    }
    catch (err: any) {
      error.value = err.message || 'Failed to search tasks'
    }
    finally {
      isLoading.value = false
    }
  }

  const reorderTasks = async (payload: TaskReorderPayload) => {
    try {
      await api.patch('/tasks/reorder', payload)
      
      payload.tasks.forEach(({ id, sort_order }) => {
        const task = tasks.value.find(t => t.id === id)
        if (task) task.sort_order = sort_order
        
        const allTask = allTasks.value.find(t => t.id === id)
        if (allTask) allTask.sort_order = sort_order
      })
      
      tasks.value.sort((a, b) => a.sort_order - b.sort_order)
      allTasks.value.sort((a, b) => a.sort_order - b.sort_order)
    }
    catch (err: any) {
      error.value = err.message || 'Failed to reorder tasks'
      throw err
    }
  }

  // Set selected date and filter tasks
  const setSelectedDate = (date: string | null) => {
    selectedDate.value = date
    if (date) {
      tasks.value = allTasks.value.filter(t => t.task_date === date)
    } else {
      tasks.value = [...allTasks.value]
    }
  }

  return {
    allTasks,
    tasks,
    isLoading,
    error,
    selectedDate,
    filteredTasks,
    fetchTasks,
    createTask,
    updateTask,
    toggleTask,
    deleteTask,
    searchTasks,
    reorderTasks,
    setSelectedDate
  }
})
