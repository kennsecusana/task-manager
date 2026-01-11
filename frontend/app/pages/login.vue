<script setup lang="ts">
  import { reactive, ref } from 'vue'
  import { useAuthStore } from '../../stores/auth'
  import type { LoginPayload } from '../../types'
  
  definePageMeta({
    layout: 'auth',
    middleware: ['auth']
  })
  
  const authStore = useAuthStore()
  
  const form = reactive<LoginPayload>({
    email: '',
    password: ''
  })
  
  const validationErrors = ref<Record<string, string[]>>({})
  const isSubmitting = ref(false)
  
  const handleLogin = async (): Promise<void> => {
    isSubmitting.value = true
    validationErrors.value = {}
    
    try {
      await authStore.login(form)
    }
    catch (error: any) {
      if (error.errors) {
        validationErrors.value = error.errors
      }
    }
    finally {
      isSubmitting.value = false
    }
  }
</script>
  
<template>
  <div class="min-h-screen flex flex-col items-center justify-center bg-white">

    <img
      src="/images/logo/GoTeam.png"
      alt="GoTeam"
      class="h-20 sm:h-20 w-auto"
    />

    <div class="w-full max-w-md bg-white rounded-2xl border border-gray-200 shadow-sm px-16 pt-8 pb-28">
      <div class="mb-8">
        <h2 class="text-center text-3xl font-bold text-gray-900">
          Sign In
        </h2>
        <p class="mt-2 text-center text-sm text-gray-900">
          Login to continue using this app
        </p>
      </div>

      <form @submit.prevent="handleLogin">
        <div v-if="authStore.error" class="rounded-lg bg-red-50 border border-red-200 p-3 mb-5">
          <p class="text-sm text-red-800">{{ authStore.error }}</p>
        </div>

        <div class="space-y-5">
          <div>
            <label for="email" class="block text-sm font-medium text-gray-900 mb-1">Email</label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              class="block w-full px-4 py-2.5 bg-white border border-gray-300 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent transition"
              placeholder=""
            >
            <p v-if="validationErrors.email" class="mt-1 text-sm text-red-600">
              {{ validationErrors.email[0] }}
            </p>
          </div>

          <div>
            <div class="flex items-center justify-between mb-1">
              <label for="password" class="block text-sm font-medium text-gray-900">Password</label>
              <a href="#" class="text-xs text-gray-900">Forgot your password?</a>
            </div>
            <input
              id="password"
              v-model="form.password"
              type="password"
              required
              class="block w-full px-4 py-2.5 bg-white border border-gray-300 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent transition"
              placeholder=""
            >
            <p v-if="validationErrors.password" class="mt-1 text-sm text-red-600">
              {{ validationErrors.password[0] }}
            </p>
          </div>
        </div>

        <button
          type="submit"
          :disabled="isSubmitting"
          class="w-full mt-8 py-2.5 px-4 bg-black text-white text-sm font-medium rounded-2xl hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900 disabled:opacity-50 disabled:cursor-not-allowed transition"
        >
          <span v-if="isSubmitting">Logging in...</span>
          <span v-else>Login</span>
        </button>
      </form>
    </div>
  </div>
</template>