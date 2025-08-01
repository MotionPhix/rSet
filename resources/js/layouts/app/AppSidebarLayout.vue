<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import ThemeProvider from '@/components/ThemeProvider.vue';
import { Toaster } from 'vue-sonner';
import type { BreadcrumbItemType } from '@/types';

interface Props {
  breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
  breadcrumbs: () => []
});
</script>

<template>
  <ThemeProvider>
    <AppShell variant="sidebar">
      <AppSidebar />
      <AppContent variant="sidebar" class="overflow-x-hidden">
        <AppSidebarHeader :breadcrumbs="breadcrumbs" />
        <slot />
      </AppContent>
    </AppShell>
    <Toaster position="top-right" />
  </ThemeProvider>
</template>
