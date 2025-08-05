<script setup lang="ts">
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import CalendarEventStrip from '@/components/calendar/CalendarEventStrip.vue';
import { 
  Clock, 
  CheckCircle2, 
  XCircle, 
  AlertCircle,
  ChevronLeft,
  ChevronRight,
  Calendar as CalendarIcon,
  Users,
  MapPin
} from 'lucide-vue-next';
import { 
  format, 
  startOfWeek, 
  endOfWeek, 
  addDays, 
  isToday, 
  isSameDay, 
  parseISO, 
  differenceInDays,
  min,
  max
} from 'date-fns';
import type { CalendarEvent } from '@/types/calendar';

interface Props {
  currentDate: Date;
  events: CalendarEvent[];
  teamEvents?: CalendarEvent[];
  showTeamEvents?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  teamEvents: () => [],
  showTeamEvents: false,
});

const emit = defineEmits<{
  eventClick: [event: CalendarEvent];
  dateClick: [date: Date];
  editEvent: [event: CalendarEvent];
  startHorizontalResize: [mouseEvent: MouseEvent, eventData: CalendarEvent, direction: 'left' | 'right'];
  startVerticalResize: [mouseEvent: MouseEvent, eventData: CalendarEvent, direction: 'up' | 'down'];
  startMove: [mouseEvent: MouseEvent, eventData: CalendarEvent];
  prevWeek: [];
  nextWeek: [];
}>();

const weekStart = computed(() => startOfWeek(props.currentDate));
const weekEnd = computed(() => endOfWeek(props.currentDate));

const weekDays = computed(() => {
  const start = weekStart.value;
  const days = [];
  
  for (let i = 0; i < 7; i++) {
    const date = addDays(start, i);
    
    // Only include single-day events in day cells
    const dayEvents = props.events.filter(event => {
      const eventStart = parseISO(event.start);
      const eventEnd = parseISO(event.end);
      return isSameDay(date, eventStart) && differenceInDays(eventEnd, eventStart) === 0;
    });
    
    const dayTeamEvents = props.showTeamEvents ? props.teamEvents.filter(event => {
      const eventStart = parseISO(event.start);
      const eventEnd = parseISO(event.end);
      return isSameDay(date, eventStart) && differenceInDays(eventEnd, eventStart) === 0;
    }) : [];
    
    days.push({
      date,
      isToday: isToday(date),
      events: dayEvents,
      teamEvents: dayTeamEvents,
    });
  }
  
  return days;
});

// Calculate event strips for multi-day events
const eventStrips = computed(() => {
  const strips: Array<{
    event: CalendarEvent;
    startCol: number;
    spanCols: number;
    stripRow: number;
  }> = [];

  // Get all unique multi-day events
  const allEvents = [...props.events];
  if (props.showTeamEvents) {
    allEvents.push(...props.teamEvents);
  }

  const uniqueEvents = new Map<string | number, CalendarEvent>();
  allEvents.forEach(event => {
    uniqueEvents.set(event.id, event);
  });

  // Filter and sort multi-day events
  const multiDayEvents = Array.from(uniqueEvents.values())
    .filter(event => {
      const eventStart = parseISO(event.start);
      const eventEnd = parseISO(event.end);
      return differenceInDays(eventEnd, eventStart) > 0;
    })
    .sort((a, b) => {
      const startCompare = parseISO(a.start).getTime() - parseISO(b.start).getTime();
      if (startCompare !== 0) return startCompare;
      return (b.days || 1) - (a.days || 1);
    });

  // Track occupied positions
  const rowOccupancy: Array<Array<{ start: number; end: number }>> = [];

  multiDayEvents.forEach(event => {
    const eventStart = parseISO(event.start);
    const eventEnd = parseISO(event.end);

    // Check if event overlaps with this week
    if (!(eventEnd < weekStart.value || eventStart > weekEnd.value)) {
      // Calculate columns within the week
      const clampedStart = max([eventStart, weekStart.value]);
      const clampedEnd = min([eventEnd, weekEnd.value]);

      const startCol = weekDays.value.findIndex(day => isSameDay(day.date, clampedStart)) + 1;
      const endCol = weekDays.value.findIndex(day => isSameDay(day.date, clampedEnd)) + 1;

      if (startCol > 0 && endCol > 0) {
        const spanCols = endCol - startCol + 1;

        // Find available row
        let stripRow = 0;
        while (stripRow < rowOccupancy.length) {
          const conflicts = rowOccupancy[stripRow].some(occupied => 
            !(endCol < occupied.start || startCol > occupied.end)
          );
          if (!conflicts) break;
          stripRow++;
        }

        // Create row if needed
        if (stripRow >= rowOccupancy.length) {
          rowOccupancy.push([]);
        }

        // Mark as occupied
        rowOccupancy[stripRow].push({ start: startCol, end: endCol });

        // Add strip
        strips.push({
          event,
          startCol,
          spanCols,
          stripRow,
        });
      }
    }
  });

  return strips;
});

const maxStripRows = computed(() => {
  return Math.max(1, eventStrips.value.reduce((max, strip) => 
    Math.max(max, strip.stripRow + 1), 0
  ));
});

const weekDateRange = computed(() => {
  const start = startOfWeek(props.currentDate);
  const end = endOfWeek(props.currentDate);
  return `${format(start, 'MMM dd')} - ${format(end, 'MMM dd, yyyy')}`;
});

const getStatusIcon = (status: string) => {
  switch (status) {
    case 'approved': return CheckCircle2;
    case 'pending': return Clock;
    case 'rejected': return XCircle;
    default: return AlertCircle;
  }
};

const getStatusColor = (status: string) => {
  switch (status) {
    case 'approved': return 'default';
    case 'pending': return 'secondary';
    case 'rejected': return 'destructive';
    default: return 'outline';
  }
};
</script>

<template>
  <Card>
    <CardHeader>
      <div class="flex items-center justify-between">
        <CardTitle class="flex items-center gap-2">
          <CalendarIcon class="h-5 w-5" />
          Week View
        </CardTitle>
        <div class="flex items-center gap-2">
          <Button variant="outline" size="sm" @click="emit('prevWeek')">
            <ChevronLeft class="h-4 w-4" />
          </Button>
          <span class="text-sm font-medium min-w-0">{{ weekDateRange }}</span>
          <Button variant="outline" size="sm" @click="emit('nextWeek')">
            <ChevronRight class="h-4 w-4" />
          </Button>
        </div>
      </div>
    </CardHeader>
    <CardContent class="p-0">
      <!-- Week header -->
      <div class="grid grid-cols-8 border-b">
        <div class="p-3 border-r bg-muted/50 text-sm font-medium">Time</div>
        <div
          v-for="day in weekDays"
          :key="day.date.getTime()"
          class="p-3 border-r last:border-r-0 text-center bg-muted/50"
          :class="{ 'bg-blue-100 dark:bg-blue-950': day.isToday }"
        >
          <div class="font-medium text-sm">{{ format(day.date, 'EEE') }}</div>
          <div
            class="text-lg mt-1"
            :class="{ 'font-bold text-blue-600': day.isToday }"
          >
            {{ format(day.date, 'd') }}
          </div>
        </div>
      </div>

      <!-- All-day events section -->
      <div class="grid grid-cols-8 border-b bg-muted/20 relative" :style="{ minHeight: `${(maxStripRows * 28) + 60}px` }">
        <div class="p-3 border-r text-sm font-medium text-muted-foreground">
          All Day
        </div>
        
        <!-- Day columns -->
        <div class="col-span-7 grid grid-cols-7 relative">
          <div
            v-for="day in weekDays"
            :key="`allday-${day.date.getTime()}`"
            class="p-2 border-r last:border-r-0 cursor-pointer hover:bg-accent/50 transition-colors"
            :class="{ 'bg-blue-50 dark:bg-blue-950/30': day.isToday }"
            @click="emit('dateClick', day.date)"
          >
            <!-- Single-day events only -->
            <div
              v-for="event in day.events"
              :key="event.id"
              class="text-xs p-1.5 rounded cursor-pointer hover:shadow-sm transition-all mb-1"
              :style="{ backgroundColor: event.backgroundColor, borderLeft: `3px solid ${event.borderColor}` }"
              @click.stop="emit('eventClick', event)"
            >
              <div class="font-medium truncate">{{ event.title }}</div>
              <div class="flex items-center gap-1 mt-0.5 opacity-75">
                <component :is="getStatusIcon(event.status || 'pending')" class="h-3 w-3" />
                <span class="capitalize">{{ event.status }}</span>
              </div>
            </div>

            <!-- Single-day team events -->
            <div
              v-for="event in day.teamEvents"
              :key="`team-${event.id}`"
              v-show="showTeamEvents"
              class="text-xs p-1.5 rounded cursor-pointer hover:shadow-sm transition-all border border-dashed mb-1"
              :style="{ backgroundColor: event.backgroundColor + '40', borderColor: event.borderColor }"
              @click.stop="emit('eventClick', event)"
            >
              <div class="flex items-center gap-1">
                <Users class="h-3 w-3" />
                <div class="font-medium truncate">{{ event.title }}</div>
              </div>
              <div class="opacity-75 truncate">{{ event.user_name }}</div>
            </div>
          </div>
          
          <!-- Multi-day event strips overlay -->
          <div class="absolute top-2 left-0 right-0 pointer-events-none">
            <CalendarEventStrip
              v-for="strip in eventStrips"
              :key="`strip-${strip.event.id}`"
              :event="strip.event"
              :start-col="strip.startCol"
              :span-cols="strip.spanCols"
              :row="strip.stripRow"
              :grid-start-date="weekStart"
              :can-edit="strip.event.extendedProps?.isOwnRequest && strip.event.status === 'pending'"
              class="pointer-events-auto"
              @event-click="emit('eventClick', strip.event)"
              @edit-event="emit('editEvent', strip.event)"
              @start-horizontal-resize="(mouseEvent, eventData, direction) => emit('startHorizontalResize', mouseEvent, eventData, direction)"
              @start-vertical-resize="(mouseEvent, eventData, direction) => emit('startVerticalResize', mouseEvent, eventData, direction)"
              @start-move="(mouseEvent, eventData) => emit('startMove', mouseEvent, eventData)"
            />
          </div>
        </div>
      </div>

      <!-- Time slots (for future hourly events) -->
      <div class="max-h-96 overflow-y-auto">
        <div v-for="hour in 9" :key="hour" class="grid grid-cols-8 border-b last:border-b-0">
          <div class="p-3 border-r text-sm text-muted-foreground bg-muted/20">
            {{ String(hour + 8).padStart(2, '0') }}:00
          </div>
          <div
            v-for="day in weekDays"
            :key="`${hour}-${day.date.getTime()}`"
            class="p-2 border-r last:border-r-0 min-h-[40px] hover:bg-accent/30 transition-colors cursor-pointer"
            :class="{ 'bg-blue-50/50 dark:bg-blue-950/20': day.isToday }"
            @click="emit('dateClick', day.date)"
          >
            <!-- Future: timed events could go here -->
          </div>
        </div>
      </div>
    </CardContent>
  </Card>
</template>
