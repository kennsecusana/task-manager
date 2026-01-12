<script setup lang="ts">
import { computed } from 'vue'
import { useTaskStore } from '@@/stores/tasks'

const taskStore = useTaskStore()

const DAYS = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']
const MONTHS = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']

const getLocalDateString = (date: Date): string => {
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

const parseLocalDate = (dateStr: string): Date => {
  const [year, month, day] = dateStr.split('-').map(Number)
  const date = new Date(year!, month! - 1, day!)
  date.setHours(0, 0, 0, 0)
  return date
}

const taskDates = computed(() => {
  const todayStr = getLocalDateString(new Date())
  
  const dates = [...new Set(taskStore.allTasks.map(t => t.task_date).filter((date): date is string => date !== undefined))]
  
  // Always include today, even if no tasks
  if (!dates.includes(todayStr)) {
    dates.push(todayStr)
  }
  
  return dates.sort((a, b) => new Date(b).getTime() - new Date(a).getTime())
})

const formatDate = (dateStr: string) => {
  const date = parseLocalDate(dateStr)
  
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  
  const yesterday = new Date(today)
  yesterday.setDate(yesterday.getDate() - 1)
  
  if (date.getTime() === today.getTime()) return 'Today'
  if (date.getTime() === yesterday.getTime()) return 'Yesterday'
  
  return `${DAYS[date.getDay()]}, ${MONTHS[date.getMonth()]} ${date.getDate()}`
}

const getWeekOfMonth = (date: Date) => {
  const firstDay = new Date(date.getFullYear(), date.getMonth(), 1)
  const firstSunday = firstDay.getDay() === 0 ? 1 : 8 - firstDay.getDay()
  const currentDay = date.getDate()
  
  if (currentDay < firstSunday) return 1
  return Math.floor((currentDay - firstSunday) / 7) + 2
}

const getSection = (dateStr: string) => {
  const date = parseLocalDate(dateStr)
  
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  
  const yesterday = new Date(today)
  yesterday.setDate(yesterday.getDate() - 1)
  
  const lastWeekStart = new Date(yesterday)
  lastWeekStart.setDate(lastWeekStart.getDate() - 6)
  
  if (date.getTime() === today.getTime()) return ''
  if (date.getTime() === yesterday.getTime()) return ''
  if (date >= lastWeekStart && date < yesterday) return 'Last week'
  
  const taskYear = date.getFullYear()
  const taskMonth = date.getMonth()
  const currentYear = today.getFullYear()
  const currentMonth = today.getMonth()
  
  if (taskYear === currentYear && taskMonth <= currentMonth) {
    const weekNum = getWeekOfMonth(date)
    const ordinal = ['', '1st', '2nd', '3rd', '4th', '5th'][weekNum] || `${weekNum}th`
    return `${ordinal} Week of ${MONTHS[taskMonth]}`
  }
  
  if (taskYear < currentYear) return taskYear.toString()
  
  return ''
}

const selectDate = (date: string) => {
  taskStore.setSelectedDate(date)
}
</script>

<template>
  <aside class="w-72 h-[calc(100vh-4rem)] overflow-y-auto sticky top-16">
    <nav class="px-4 py-6">
      <div v-if="taskDates.length === 0" class="text-sm text-gray-600">
        No tasks yet
      </div>
      
      <template v-else>
        <div v-for="(date, index) in taskDates" :key="date">
          <div 
            v-if="index === 0 || getSection(date) !== getSection(taskDates[index - 1]!)"
            class="text-xs text-gray-400 font-medium mt-6 mb-2 px-3 first:mt-0"
          >
            {{ getSection(date) }}
          </div>
          
          <button
            @click="selectDate(date)"
            class="w-full text-left px-4 py-2.5 rounded-2xl text-sm transition-colors mb-1"
            :class="taskStore.selectedDate === date ? 'bg-black text-white font-medium' : 'text-gray-700 hover:bg-gray-100'"
          >
            {{ formatDate(date) }}
          </button>
        </div>
      </template>
    </nav>
  </aside>
</template>