<script setup lang="ts">
import { onMounted, onUnmounted } from 'vue'
import { useTheme } from '@/composables/useTheme'

const { applyThemeSettings, themeSettings } = useTheme()

// Apply theme on mount
onMounted(() => {
  applyThemeSettings()
})

// Listen for theme changes from other tabs/windows
const handleStorageChange = (e: StorageEvent) => {
  if (e.key === 'leavehub-theme-settings') {
    applyThemeSettings()
  }
}

onMounted(() => {
  window.addEventListener('storage', handleStorageChange)
})

onUnmounted(() => {
  window.removeEventListener('storage', handleStorageChange)
})
</script>

<template>
  <slot />
</template>
