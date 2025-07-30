<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { useDark, useToggle } from '@vueuse/core';
import { toast } from 'vue-sonner';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { Switch } from '@/components/ui/switch';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import {
  Palette,
  Sun,
  Moon,
  Monitor,
  Zap,
  Globe,
  Eye,
  Accessibility
} from 'lucide-vue-next';

const isDark = useDark();
const toggleDark = useToggle(isDark);

// Theme settings
const themeMode = ref(localStorage.getItem('theme-mode') || 'system');
const accentColor = ref(localStorage.getItem('accent-color') || 'blue');
const fontSize = ref(localStorage.getItem('font-size') || 'medium');
const sidebarCollapsed = ref(localStorage.getItem('sidebar-collapsed') === 'true');
const reducedMotion = ref(localStorage.getItem('reduced-motion') === 'true');
const highContrast = ref(localStorage.getItem('high-contrast') === 'true');

// Theme options
const themeOptions = [
  { value: 'light', label: 'Light', icon: Sun, description: 'Clean and bright interface' },
  { value: 'dark', label: 'Dark', icon: Moon, description: 'Easy on the eyes in low light' },
  { value: 'system', label: 'System', icon: Monitor, description: 'Follow your system preference' }
];

const accentColors = [
  { value: 'blue', label: 'Blue', color: 'bg-blue-500' },
  { value: 'green', label: 'Green', color: 'bg-green-500' },
  { value: 'purple', label: 'Purple', color: 'bg-purple-500' },
  { value: 'orange', label: 'Orange', color: 'bg-orange-500' },
  { value: 'red', label: 'Red', color: 'bg-red-500' },
  { value: 'pink', label: 'Pink', color: 'bg-pink-500' }
];

const fontSizes = [
  { value: 'small', label: 'Small', description: 'Compact text size' },
  { value: 'medium', label: 'Medium', description: 'Default text size' },
  { value: 'large', label: 'Large', description: 'Larger text for better readability' }
];

// Apply theme changes
const applyTheme = () => {
  // Apply theme mode
  if (themeMode.value === 'dark') {
    document.documentElement.classList.add('dark');
  } else if (themeMode.value === 'light') {
    document.documentElement.classList.remove('dark');
  } else {
    // System preference
    const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    if (systemDark) {
      document.documentElement.classList.add('dark');
    } else {
      document.documentElement.classList.remove('dark');
    }
  }

  // Apply accent color
  document.documentElement.setAttribute('data-accent-color', accentColor.value);

  // Apply font size
  document.documentElement.setAttribute('data-font-size', fontSize.value);

  // Apply accessibility settings
  if (reducedMotion.value) {
    document.documentElement.style.setProperty('--motion-reduce', 'true');
  } else {
    document.documentElement.style.removeProperty('--motion-reduce');
  }

  if (highContrast.value) {
    document.documentElement.classList.add('high-contrast');
  } else {
    document.documentElement.classList.remove('high-contrast');
  }
};

// Save preferences
const savePreferences = () => {
  localStorage.setItem('theme-mode', themeMode.value);
  localStorage.setItem('accent-color', accentColor.value);
  localStorage.setItem('font-size', fontSize.value);
  localStorage.setItem('sidebar-collapsed', sidebarCollapsed.value.toString());
  localStorage.setItem('reduced-motion', reducedMotion.value.toString());
  localStorage.setItem('high-contrast', highContrast.value.toString());

  applyTheme();
  toast.success('Appearance settings saved');
};

// Reset to defaults
const resetToDefaults = () => {
  themeMode.value = 'system';
  accentColor.value = 'blue';
  fontSize.value = 'medium';
  sidebarCollapsed.value = false;
  reducedMotion.value = false;
  highContrast.value = false;

  savePreferences();
  toast.success('Settings reset to defaults');
};

// Initialize theme on mount
onMounted(() => {
  applyTheme();
});

// Watch for system theme changes
if (typeof window !== 'undefined') {
  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
    if (themeMode.value === 'system') {
      applyTheme();
    }
  });
}
</script>

<template>
  <Head title="Appearance Settings" />

  <SettingsLayout>
    <div class="space-y-6 p-6">
      <!-- Header -->
      <HeadingSmall
        title="Appearance Settings"
        description="Customize the look and feel of your workspace"
      />

      <!-- Theme Selection -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Palette class="h-5 w-5" />
            Theme
          </CardTitle>
          <CardDescription>
            Choose your preferred color scheme
          </CardDescription>
        </CardHeader>
        <CardContent>
          <RadioGroup v-model="themeMode" class="grid gap-4 md:grid-cols-3">
            <div
              v-for="option in themeOptions"
              :key="option.value"
              class="flex items-center space-x-2"
            >
              <RadioGroupItem :value="option.value" :id="option.value" />
              <Label
                :for="option.value"
                class="flex items-center gap-3 cursor-pointer flex-1 p-3 rounded-lg border hover:bg-accent transition-colors"
              >
                <component :is="option.icon" class="h-5 w-5" />
                <div>
                  <div class="font-medium">{{ option.label }}</div>
                  <div class="text-sm text-muted-foreground">{{ option.description }}</div>
                </div>
              </Label>
            </div>
          </RadioGroup>
        </CardContent>
      </Card>

      <!-- Accent Color -->
      <Card>
        <CardHeader>
          <CardTitle>Accent Color</CardTitle>
          <CardDescription>
            Choose your preferred accent color for buttons and highlights
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="grid gap-3 md:grid-cols-6">
            <div
              v-for="color in accentColors"
              :key="color.value"
              class="flex items-center space-x-2"
            >
              <RadioGroup v-model="accentColor">
                <div class="flex items-center space-x-2">
                  <RadioGroupItem :value="color.value" :id="color.value" />
                  <Label
                    :for="color.value"
                    class="flex items-center gap-2 cursor-pointer"
                  >
                    <div class="w-4 h-4 rounded-full" :class="color.color"></div>
                    <span class="text-sm">{{ color.label }}</span>
                  </Label>
                </div>
              </RadioGroup>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Display Settings -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Monitor class="h-5 w-5" />
            Display Settings
          </CardTitle>
          <CardDescription>
            Adjust text size and layout preferences
          </CardDescription>
        </CardHeader>
        <CardContent class="space-y-6">
          <!-- Font Size -->
          <div class="space-y-3">
            <Label>Font Size</Label>
            <RadioGroup v-model="fontSize" class="grid gap-3">
              <div
                v-for="size in fontSizes"
                :key="size.value"
                class="flex items-center space-x-2"
              >
                <RadioGroupItem :value="size.value" :id="`font-${size.value}`" />
                <Label
                  :for="`font-${size.value}`"
                  class="flex items-center justify-between cursor-pointer flex-1 p-3 rounded-lg border hover:bg-accent transition-colors"
                >
                  <div>
                    <div class="font-medium">{{ size.label }}</div>
                    <div class="text-sm text-muted-foreground">{{ size.description }}</div>
                  </div>
                  <div class="text-right">
                    <div :class="{ 'text-sm': size.value === 'small', 'text-base': size.value === 'medium', 'text-lg': size.value === 'large' }">
                      Sample text
                    </div>
                  </div>
                </Label>
              </div>
            </RadioGroup>
          </div>

          <Separator />

          <!-- Sidebar Settings -->
          <div class="flex items-center justify-between">
            <div class="space-y-1">
              <Label>Collapsed Sidebar</Label>
              <div class="text-sm text-muted-foreground">
                Start with a collapsed sidebar by default
              </div>
            </div>
            <Switch v-model="sidebarCollapsed" />
          </div>
        </CardContent>
      </Card>

      <!-- Accessibility Settings -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Accessibility class="h-5 w-5" />
            Accessibility
          </CardTitle>
          <CardDescription>
            Options to improve accessibility and usability
          </CardDescription>
        </CardHeader>
        <CardContent class="space-y-6">
          <!-- Reduced Motion -->
          <div class="flex items-center justify-between">
            <div class="space-y-1">
              <Label>Reduce Motion</Label>
              <div class="text-sm text-muted-foreground">
                Minimize animations and transitions
              </div>
            </div>
            <Switch v-model="reducedMotion" />
          </div>

          <Separator />

          <!-- High Contrast -->
          <div class="flex items-center justify-between">
            <div class="space-y-1">
              <Label>High Contrast</Label>
              <div class="text-sm text-muted-foreground">
                Increase contrast for better visibility
              </div>
            </div>
            <Switch v-model="highContrast" />
          </div>
        </CardContent>
      </Card>

      <!-- Preview -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Eye class="h-5 w-5" />
            Preview
          </CardTitle>
          <CardDescription>
            See how your changes will look
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="space-y-4 p-4 border rounded-lg">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-semibold">Sample Content</h3>
              <Button size="sm">Action Button</Button>
            </div>
            <p class="text-muted-foreground">
              This is a preview of how text and components will appear with your current settings.
              The theme, colors, and font size will be applied throughout the application.
            </p>
            <div class="flex gap-2">
              <Button variant="outline" size="sm">Secondary</Button>
              <Button variant="destructive" size="sm">Delete</Button>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Action Buttons -->
      <div class="flex justify-between">
        <Button variant="outline" @click="resetToDefaults">
          Reset to Defaults
        </Button>
        <Button @click="savePreferences">
          Save Changes
        </Button>
      </div>
    </div>
  </SettingsLayout>
</template>

<style scoped>
/* Sample styles for different font sizes */
[data-font-size="small"] {
  font-size: 0.875rem;
}

[data-font-size="medium"] {
  font-size: 1rem;
}

[data-font-size="large"] {
  font-size: 1.125rem;
}

/* High contrast mode */
.high-contrast {
  --background: 255 255 255;
  --foreground: 0 0 0;
  --muted: 245 245 245;
  --muted-foreground: 64 64 64;
  --border: 0 0 0;
}

.dark.high-contrast {
  --background: 0 0 0;
  --foreground: 255 255 255;
  --muted: 23 23 23;
  --muted-foreground: 191 191 191;
  --border: 255 255 255;
}
</style>
