<script setup lang="ts">
import { ref, watch } from 'vue'
import { Search, X } from 'lucide-vue-next'
import { useAuthStore } from '../../stores/auth'
import { useTaskStore } from '../../stores/tasks'

const authStore = useAuthStore()
const taskStore = useTaskStore()
const searchQuery = ref('')
const showLogoutMenu = ref(false)

let searchTimeout: ReturnType<typeof setTimeout> | null = null

const performSearch = async (query: string) => {
  await (query.trim() ? taskStore.searchTasks(query) : taskStore.fetchTasks())
}

const clearSearch = async () => {
  searchQuery.value = ''
  await taskStore.fetchTasks()
}

watch(searchQuery, (value) => {
  if (searchTimeout) clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => performSearch(value), 300)
})

const toggleLogoutMenu = () => {
  showLogoutMenu.value = !showLogoutMenu.value
}

const handleLogout = async () => {
  await authStore.logout()
}
</script>

<template>
  <header class="border-b border-gray-200">
    <div class="px-8 py-4 flex items-center gap-6">

      <div class="flex-shrink-0">
        <img
          src="/images/logo/GoTeam.png"
          alt="GoTeam"
          class="h-10 sm:h-10 w-auto"
        />
      </div>

      <div class="flex-1 max-w-md mx-auto relative">
        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search"
          class="w-full pl-10 pr-10 py-2 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent"
          @keyup.enter="performSearch(searchQuery)"
        />
        <button
          v-if="searchQuery"
          @click="clearSearch"
          class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
        >
          <X class="w-4 h-4" />
        </button>
      </div>

      <div class="relative flex-shrink-0">
        <button @click="toggleLogoutMenu" class="focus:outline-none">
          <img
            v-if="authStore.user?.image_url"
            :src="authStore.user.image_url"
            :alt="authStore.user.name"
            class="w-10 h-10 rounded-full object-cover cursor-pointer hover:ring-2 hover:ring-gray-300 transition-all"
          />
          <div v-else class="px-4 py-2 text-sm font-medium text-gray-900 cursor-pointer hover:bg-gray-100 rounded-lg transition-all">
            {{ authStore.user?.name }}
          </div>
        </button>

        <div v-if="showLogoutMenu" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
          <button @click="handleLogout" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
            Logout
          </button>
        </div>
      </div>
    </div>
  </header>
</template>