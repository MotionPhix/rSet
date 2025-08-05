<script setup lang="ts">
import { computed } from 'vue';
import CalendarDayCell from '@/components/calendar/CalendarDayCell.vue';
import CalendarEventStrip from '@/components/calendar/CalendarEventStrip.vue';
import { 
  format, 
  parseISO, 
  differenceInDays, 
  startOfWeek,
  endOfWeek,
  addDays,
  isSameDay,
  isAfter,
  isBefore,
  min,
  max,
  startOfDay,
  endOfDay
} from 'date-fns';

interface Event {
  id: number | string;
  title: string;
  start: string;
  end: string;
  type: string;
  status?: string;
  reason?: string;
  user_name?: string;
  user_id?: number;
  days?: number;
  color: string;
  backgroundColor: string;
  borderColor: string;
  allDay: boolean;
  isHoliday?: boolean;
  extendedProps?: {
    status?: string;
    type?: string;
    reason?: string;
    appliedAt?: string;
    isOwnRequest?: boolean;
    description?: string;
    isRecurring?: boolean;
    recurrenceType?: string;
    createdBy?: string;
  };
}

interface CalendarDay {
  date: Date;
  isCurrentMonth: boolean;
  isToday: boolean;
  events: Event[];
  teamEvents: Event[];
}

interface Props {
  monthDays: CalendarDay[];
  showTeamEvents: boolean;
  canEditEvents: boolean;
}

const props = defineProps<Props>();

const emit = defineEmits<{
  eventClick: [event: Event];
  dayClick: [date: Date];
  editEvent: [event: Event];
  startHorizontalResize: [mouseEvent: MouseEvent, eventData: Event, direction: 'left' | 'right'];
  startVerticalResize: [mouseEvent: MouseEvent, eventData: Event, direction: 'up' | 'down'];
}>();

// Calculate the grid start date (first day shown in calendar)
const gridStartDate = computed(() => {
  return props.monthDays[0]?.date || new Date();
});

// Get all unique multi-day events and their strips
const eventStrips = computed(() => {
  const strips: Array<{
    event: Event;
    startCol: number;
    spanCols: number;
    weekRow: number;
    stripRow: number;
  }> = [];

  // Get all unique events (avoid duplicates across days)
  const uniqueEvents = new Map<string, Event>();
  props.monthDays.forEach(day => {
    day.events.forEach(event => {
      uniqueEvents.set(String(event.id), event);
    });
    if (props.showTeamEvents) {
      day.teamEvents.forEach(event => {
        uniqueEvents.set(String(event.id), event);
      });
    }
  });

  // Filter only multi-day events and sort them
  const multiDayEvents = Array.from(uniqueEvents.values())
    .filter(event => {
      const eventStart = parseISO(event.start);
      const eventEnd = parseISO(event.end);
      return differenceInDays(eventEnd, eventStart) > 0;
    })
    .sort((a, b) => {
      const startCompare = parseISO(a.start).getTime() - parseISO(b.start).getTime();
      if (startCompare !== 0) return startCompare;
      return (b.days || 0) - (a.days || 0); // Longer events first for better visual organization
    });

  // Group calendar days by weeks
  const weeks: CalendarDay[][] = [];
  for (let i = 0; i < props.monthDays.length; i += 7) {
    weeks.push(props.monthDays.slice(i, i + 7));
  }

  // Track occupied strip positions for each week
  const weekStripOccupancy: Array<Array<Array<{ start: number; end: number }>>> = weeks.map(() => []);

  multiDayEvents.forEach(event => {
    const eventStart = parseISO(event.start);
    const eventEnd = parseISO(event.end);

    // Process each week where this event appears
    weeks.forEach((week, weekIndex) => {
      const weekStart = week[0].date;
      const weekEnd = week[6].date;

      // Check if event overlaps with this week
      if (!(eventEnd < weekStart || eventStart > weekEnd)) {
        // Calculate the actual start and end columns for this week
        const clampedStart = max([eventStart, weekStart]);
        const clampedEnd = min([eventEnd, weekEnd]);

        const startCol = week.findIndex(day => isSameDay(day.date, clampedStart)) + 1;
        const endCol = week.findIndex(day => isSameDay(day.date, clampedEnd)) + 1;

        if (startCol > 0 && endCol > 0) {
          const spanCols = endCol - startCol + 1;

          // Find the first available strip row for this week
          let stripRow = 0;
          while (stripRow < weekStripOccupancy[weekIndex].length) {
            const conflicts = weekStripOccupancy[weekIndex][stripRow].some(occupied => 
              !(endCol < occupied.start || startCol > occupied.end)
            );
            if (!conflicts) break;
            stripRow++;
          }

          // Create strip row if it doesn't exist
          if (stripRow >= weekStripOccupancy[weekIndex].length) {
            weekStripOccupancy[weekIndex].push([]);
          }

          // Mark this position as occupied
          weekStripOccupancy[weekIndex][stripRow].push({ start: startCol, end: endCol });

          // Add to strips
          strips.push({
            event,
            startCol,
            spanCols,
            weekRow: weekIndex,
            stripRow,
          });
        }
      }
    });
  });

  return strips;
});

// Calculate maximum strip rows needed for each week
const maxStripRowsByWeek = computed(() => {
  const maxRows: number[] = [];
  const weekCount = Math.ceil(props.monthDays.length / 7);
  
  for (let weekIndex = 0; weekIndex < weekCount; weekIndex++) {
    const weekStrips = eventStrips.value.filter(strip => strip.weekRow === weekIndex);
    const maxRow = weekStrips.reduce((max, strip) => Math.max(max, strip.stripRow + 1), 0);
    maxRows.push(Math.max(1, maxRow)); // At least 1 row for spacing
  }
  
  return maxRows;
});

// Group days by weeks for proper grid layout
const weekRows = computed(() => {
  const weeks: CalendarDay[][] = [];
  for (let i = 0; i < props.monthDays.length; i += 7) {
    weeks.push(props.monthDays.slice(i, i + 7));
  }
  return weeks;
});

// Filter events for individual day cells (only single-day events)
const getDayEvents = (day: CalendarDay) => {
  return day.events.filter(event => {
    const eventStart = parseISO(event.start);
    const eventEnd = parseISO(event.end);
    // Only show single day events in day cells
    return differenceInDays(eventEnd, eventStart) === 0;
  });
};

const getDayTeamEvents = (day: CalendarDay) => {
  return day.teamEvents.filter(event => {
    const eventStart = parseISO(event.start);
    const eventEnd = parseISO(event.end);
    return differenceInDays(eventEnd, eventStart) === 0;
  });
};
</script>

<template>
  <div class="calendar-month-view">
    <!-- Calendar Header -->
    <div class="grid grid-cols-7 border-b">
      <div 
        v-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']"
        :key="day"
        class="p-2 text-sm font-medium text-muted-foreground text-center bg-muted/30"
      >
        {{ day }}
      </div>
    </div>

    <!-- Calendar Body -->
    <div class="calendar-body border-collapse">
      <!-- Render each week row -->
      <div 
        v-for="(week, weekIndex) in weekRows" 
        :key="weekIndex"
        class="calendar-week relative"
        :style="{ minHeight: `${(maxStripRowsByWeek[weekIndex] * 26) + 90}px` }"
      >
        <!-- Day cells grid -->
        <div class="grid grid-cols-7 h-full border-collapse">
          <CalendarDayCell
            v-for="day in week"
            :key="format(day.date, 'yyyy-MM-dd')"
            :date="day.date"
            :is-current-month="day.isCurrentMonth"
            :is-today="day.isToday"
            :events="getDayEvents(day)"
            :team-events="props.showTeamEvents ? getDayTeamEvents(day) : []"
            :show-team-events="props.showTeamEvents"
            @event-click="emit('eventClick', $event)"
            @day-click="emit('dayClick', $event)"
            class="border-r border-b border-gray-200"
          />
        </div>

        <!-- Event strips overlay -->
        <div class="absolute top-12 left-0 right-0 pointer-events-none">
          <CalendarEventStrip
            v-for="strip in eventStrips.filter(s => s.weekRow === weekIndex)"
            :key="`${strip.event.id}-${weekIndex}-${strip.stripRow}`"
            :event="strip.event"
            :start-col="strip.startCol"
            :span-cols="strip.spanCols"
            :row="strip.stripRow"
            :grid-start-date="week[0].date"
            :can-edit="props.canEditEvents && strip.event.extendedProps?.isOwnRequest && strip.event.status === 'pending'"
            class="pointer-events-auto"
            @event-click="emit('eventClick', strip.event)"
            @edit-event="emit('editEvent', strip.event)"
            @start-horizontal-resize="(mouseEvent, eventData, direction) => emit('startHorizontalResize', mouseEvent, eventData, direction)"
            @start-vertical-resize="(mouseEvent, eventData, direction) => emit('startVerticalResize', mouseEvent, eventData, direction)"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  position: relative;
}

.calendar-body {
  border-collapse: collapse;
}

.calendar-week {
  border-collapse: collapse;
}

/* Remove any margin/padding that might create gaps */
.calendar-week + .calendar-week {
  margin-top: 0;
  padding-top: 0;
}
</style>
