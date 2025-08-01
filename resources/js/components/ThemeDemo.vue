<script setup lang="ts">
import { computed } from 'vue'
import { useTheme } from '@/composables/useTheme'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'

const { themeSettings, effectiveTheme } = useTheme()

const themeInfo = computed(() => ({
  mode: themeSettings.value.mode,
  effective: effectiveTheme.value,
  accent: themeSettings.value.accentColor,
  fontSize: themeSettings.value.fontSize,
  motionReduced: themeSettings.value.reducedMotion,
  highContrast: themeSettings.value.highContrast,
  sidebarCollapsed: themeSettings.value.sidebarCollapsed
}))
</script>

<template>
  <Card class="max-w-md">
    <CardHeader>
      <CardTitle>Theme System Demo</CardTitle>
    </CardHeader>
    <CardContent class="space-y-4">
      <div class="space-y-2">
        <p class="text-sm text-muted-foreground">Current Settings:</p>
        <div class="grid grid-cols-2 gap-2 text-xs">
          <Badge variant="outline">{{ themeInfo.mode }} theme</Badge>
          <Badge variant="outline">{{ themeInfo.accent }} accent</Badge>
          <Badge variant="outline">{{ themeInfo.fontSize }} text</Badge>
          <Badge :variant="themeInfo.motionReduced ? 'default' : 'outline'">
            {{ themeInfo.motionReduced ? 'No motion' : 'Motion' }}
          </Badge>
          <Badge :variant="themeInfo.highContrast ? 'default' : 'outline'">
            {{ themeInfo.highContrast ? 'High contrast' : 'Normal contrast' }}
          </Badge>
          <Badge :variant="themeInfo.sidebarCollapsed ? 'default' : 'outline'">
            {{ themeInfo.sidebarCollapsed ? 'Collapsed' : 'Expanded' }}
          </Badge>
        </div>
      </div>
      
      <div class="space-y-2">
        <p class="text-sm text-muted-foreground">Sample UI Elements:</p>
        <div class="flex gap-2">
          <Button size="sm">Primary Button</Button>
          <Button variant="outline" size="sm">Secondary</Button>
        </div>
        <div class="p-3 bg-accent rounded text-accent-foreground text-sm">
          This background uses the accent color
        </div>
      </div>
      
      <div class="text-xs text-muted-foreground">
        Effective theme: <code>{{ themeInfo.effective }}</code>
      </div>
    </CardContent>
  </Card>
</template>
