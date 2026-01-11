<script setup lang="ts">
import TheHeader from '../../components/layout/TheHeader.vue'
import TheSidebar from '../../components/layout/TheSidebar.vue'
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex flex-col">
    <TheHeader class="sticky top-0 z-10" />
    
    <div class="flex flex-1">
      <TheSidebar />

      <!-- Main Content -->
      <main class="flex-1 p-8">
        <slot />
      </main>
    </div>
  </div>
</template>