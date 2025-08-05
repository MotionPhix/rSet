<script setup lang="ts">
import {
  DateFormatter,
  getLocalTimeZone,
  CalendarDate,
  DateValue,
  parseDate,
  today
} from '@internationalized/date';
import { CalendarIcon } from 'lucide-vue-next'
import { cn } from '@/lib/utils'
import { Button } from '@/components/ui/button'
import { Calendar } from '@/components/ui/calendar'
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
import { computed, getCurrentInstance } from 'vue'

const props = defineProps<{
  placeholder?: string
  min?: string
}>()

// The component now accepts and emits ISO date strings
const value = defineModel<string | null>({ required: true })

const df = new DateFormatter('en-MW', {
  dateStyle: 'medium',
})

// Helper function to convert ISO string to DateValue
const stringToDateValue = (dateString: string | null): DateValue | null => {
  if (!dateString) return null
  try {
    return parseDate(dateString)
  } catch {
    return null
  }
}

// Helper function to convert DateValue to ISO string
const dateValueToString = (dateValue: DateValue | null): string | null => {
  if (!dateValue) return null
  try {
    // DateValue toString() method returns ISO format (YYYY-MM-DD)
    return dateValue.toString()
  } catch {
    return null
  }
}

const displayDate = computed(() => {
  if (!value.value) return ''

  try {
    const dateValue = stringToDateValue(value.value)
    if (!dateValue) return ''
    
    const date = dateValue instanceof CalendarDate
      ? dateValue.toDate(getLocalTimeZone())
      : new Date(dateValue.toString())

    if (isNaN(date.getTime())) return ''

    return df.format(date)
  } catch {
    return ''
  }
})

// Internal DateValue for the calendar component
const calendarValue = computed({
  get: () => stringToDateValue(value.value),
  set: (newValue: DateValue | null) => {
    value.value = dateValueToString(newValue)
  }
})

// Safe value for the calendar component (never null)
const safeCalendarValue = computed({
  get: () => calendarValue.value || undefined,
  set: (newValue: DateValue | undefined) => {
    calendarValue.value = newValue || null
  }
})

// Convert min prop to DateValue if provided
const minDateValue = computed(() => {
  if (!props.min) return undefined
  return stringToDateValue(props.min) || undefined
})
</script>

<template>
  <Popover>
    <PopoverTrigger as-child>
      <Button
        variant="outline"
        :class="cn(
          'w-full justify-start text-left font-normal',
          !value && 'text-muted-foreground',
        )">
        <CalendarIcon class="mr-2 h-4 w-4" />
        {{ displayDate || placeholder || "Pick a date" }}
      </Button>
    </PopoverTrigger>

    <PopoverContent class="w-auto p-0">
      <Calendar
        v-model="safeCalendarValue"
        :default-value="safeCalendarValue || today(getLocalTimeZone())"
        :min-value="minDateValue"
        initial-focus
      />
    </PopoverContent>
  </Popover>
</template>
