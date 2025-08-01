<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { toast } from 'vue-sonner';
import { useTheme } from '@/composables/useTheme';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { Switch } from '@/components/ui/switch';
import { Separator } from '@/components/ui/separator';
import {
  Palette,
  Sun,
  Moon,
  Monitor,
  Eye,
  Accessibility
} from 'lucide-vue-next';

// Use the global theme composable
const {
  themeSettings,
  updateThemeMode,
  updateAccentColor,
  updateFontSize,
  updateSidebarCollapsed,
  updateReducedMotion,
  updateHighContrast,
  resetToDefaults: resetThemeToDefaults,
} = useTheme();

// Local reactive references for form handling
const themeMode = computed({
  get: () => themeSettings.value.mode,
  set: (value) => updateThemeMode(value)
});

const accentColor = computed({
  get: () => themeSettings.value.accentColor,
  set: (value) => updateAccentColor(value)
});

const fontSize = computed({
  get: () => themeSettings.value.fontSize,
  set: (value) => updateFontSize(value)
});

const sidebarCollapsed = computed({
  get: () => themeSettings.value.sidebarCollapsed,
  set: (value) => updateSidebarCollapsed(value)
});

const reducedMotion = computed({
  get: () => themeSettings.value.reducedMotion,
  set: (value) => updateReducedMotion(value)
});

const highContrast = computed({
  get: () => themeSettings.value.highContrast,
  set: (value) => updateHighContrast(value)
});

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

// Actions
const savePreferences = () => {
  // Settings are automatically saved via the composable
  toast.success('Appearance settings saved');
};

const resetToDefaults = () => {
  resetThemeToDefaults();
  toast.success('Settings reset to defaults');
};
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
          <RadioGroup v-model="accentColor" class="grid gap-3 md:grid-cols-3">
            <div
              v-for="color in accentColors"
              :key="color.value"
              class="flex items-center space-x-2"
            >
              <RadioGroupItem :value="color.value" :id="`accent-${color.value}`" />
              <Label
                :for="`accent-${color.value}`"
                class="flex items-center gap-2 cursor-pointer flex-1 p-3 rounded-lg border hover:bg-accent transition-colors"
              >
                <div class="w-4 h-4 rounded-full" :class="color.color"></div>
                <span class="text-sm">{{ color.label }}</span>
              </Label>
            </div>
          </RadioGroup>
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
