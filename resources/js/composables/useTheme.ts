import { ref, computed, watch, nextTick } from 'vue'
import { useDark, useToggle, useStorage } from '@vueuse/core'

export interface ThemeSettings {
  mode: 'light' | 'dark' | 'system'
  accentColor: string
  fontSize: 'small' | 'medium' | 'large'
  sidebarCollapsed: boolean
  reducedMotion: boolean
  highContrast: boolean
}

const defaultSettings: ThemeSettings = {
  mode: 'system',
  accentColor: 'blue',
  fontSize: 'medium',
  sidebarCollapsed: false,
  reducedMotion: false,
  highContrast: false
}

// Global reactive state using VueUse storage
const themeSettings = useStorage('leavehub-theme-settings', defaultSettings, localStorage, {
  mergeDefaults: true
})

const isDark = useDark({
  attribute: 'data-theme',
  valueDark: 'dark',
  valueLight: 'light'
})

const toggleDark = useToggle(isDark)

export function useTheme() {
  // Computed properties for easy access
  const currentTheme = computed(() => themeSettings.value)
  
  const isSystemDark = computed(() => {
    if (typeof window === 'undefined') return false
    return window.matchMedia('(prefers-color-scheme: dark)').matches
  })

  const effectiveTheme = computed(() => {
    if (themeSettings.value.mode === 'system') {
      return isSystemDark.value ? 'dark' : 'light'
    }
    return themeSettings.value.mode
  })

  // Apply all theme settings to the document
  const applyThemeSettings = async () => {
    await nextTick()
    
    const root = document.documentElement
    
    // Apply theme mode
    if (themeSettings.value.mode === 'dark') {
      root.setAttribute('data-theme', 'dark')
      root.classList.add('dark')
    } else if (themeSettings.value.mode === 'light') {
      root.setAttribute('data-theme', 'light')
      root.classList.remove('dark')
    } else {
      // System preference
      const systemDark = isSystemDark.value
      root.setAttribute('data-theme', systemDark ? 'dark' : 'light')
      if (systemDark) {
        root.classList.add('dark')
      } else {
        root.classList.remove('dark')
      }
    }

    // Apply accent color
    root.setAttribute('data-accent-color', themeSettings.value.accentColor)
    root.style.setProperty('--accent-color', getAccentColorValue(themeSettings.value.accentColor))

    // Apply font size
    root.setAttribute('data-font-size', themeSettings.value.fontSize)
    root.style.setProperty('--font-size-base', getFontSizeValue(themeSettings.value.fontSize))

    // Apply sidebar state
    root.setAttribute('data-sidebar-collapsed', themeSettings.value.sidebarCollapsed.toString())

    // Apply accessibility settings
    if (themeSettings.value.reducedMotion) {
      root.style.setProperty('--motion-duration', '0s')
      root.style.setProperty('--motion-delay', '0s')
      root.classList.add('reduce-motion')
    } else {
      root.style.removeProperty('--motion-duration')
      root.style.removeProperty('--motion-delay')
      root.classList.remove('reduce-motion')
    }

    if (themeSettings.value.highContrast) {
      root.classList.add('high-contrast')
    } else {
      root.classList.remove('high-contrast')
    }

    // Emit custom event for components that need to react to theme changes
    window.dispatchEvent(new CustomEvent('theme-changed', { 
      detail: themeSettings.value 
    }))
  }

  // Get CSS custom property values for colors
  const getAccentColorValue = (color: string): string => {
    const colors: Record<string, string> = {
      blue: '217 87% 50%',
      green: '142 87% 50%',
      purple: '256 87% 50%',
      orange: '33 87% 50%',
      red: '0 87% 50%',
      pink: '322 87% 50%'
    }
    return colors[color] || colors.blue
  }

  // Get font size values
  const getFontSizeValue = (size: string): string => {
    const sizes: Record<string, string> = {
      small: '0.875rem',
      medium: '1rem',
      large: '1.125rem'
    }
    return sizes[size] || sizes.medium
  }

  // Update individual settings
  const updateThemeMode = (mode: ThemeSettings['mode']) => {
    themeSettings.value.mode = mode
  }

  const updateAccentColor = (color: string) => {
    themeSettings.value.accentColor = color
  }

  const updateFontSize = (size: ThemeSettings['fontSize']) => {
    themeSettings.value.fontSize = size
  }

  const updateSidebarCollapsed = (collapsed: boolean) => {
    themeSettings.value.sidebarCollapsed = collapsed
  }

  const updateReducedMotion = (reduced: boolean) => {
    themeSettings.value.reducedMotion = reduced
  }

  const updateHighContrast = (highContrast: boolean) => {
    themeSettings.value.highContrast = highContrast
  }

  // Update all settings at once
  const updateSettings = (newSettings: Partial<ThemeSettings>) => {
    Object.assign(themeSettings.value, newSettings)
  }

  // Reset to defaults
  const resetToDefaults = () => {
    Object.assign(themeSettings.value, defaultSettings)
  }

  // Watch for changes and apply them
  watch(themeSettings, applyThemeSettings, { deep: true, immediate: true })

  // Watch for system theme changes
  if (typeof window !== 'undefined') {
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)')
    const handleSystemThemeChange = () => {
      if (themeSettings.value.mode === 'system') {
        applyThemeSettings()
      }
    }
    
    mediaQuery.addEventListener('change', handleSystemThemeChange)
  }

  return {
    // State
    themeSettings: currentTheme,
    effectiveTheme,
    isDark,
    
    // Actions
    toggleDark,
    updateThemeMode,
    updateAccentColor,
    updateFontSize,
    updateSidebarCollapsed,
    updateReducedMotion,
    updateHighContrast,
    updateSettings,
    resetToDefaults,
    applyThemeSettings,
    
    // Utilities
    getAccentColorValue,
    getFontSizeValue
  }
}
