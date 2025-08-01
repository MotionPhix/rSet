<script setup lang="ts">
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
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
import { format, startOfWeek, endOfWeek, addDays, isToday, isSameDay } from 'date-fns';

interface Event {
  id: number;
  title: string;
  start: string;
  end: string;
  status: string;
  user_name: string;
  days: number;
  backgroundColor: string;
  borderColor: string;
  extendedProps: {
    isOwnRequest: boolean;
  };
}

interface Props {
  currentDate: Date;
  events: Event[];
  teamEvents?: Event[];
  showTeamEvents?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  teamEvents: () => [],
  showTeamEvents: false,
});

const emit = defineEmits<{
  eventClick: [event: Event];
  dateClick: [date: Date];
  prevWeek: [];
  nextWeek: [];
}>();

const weekDays = computed(() => {
  const start = startOfWeek(props.currentDate);
  const days = [];
  
  for (let i = 0; i < 7; i++) {
    const date = addDays(start, i);
    const dayEvents = props.events.filter(event => {
      const eventStart = new Date(event.start);
      const eventEnd = new Date(event.end);
      return date >= eventStart && date < eventEnd;
    });
    
    const dayTeamEvents = props.showTeamEvents ? props.teamEvents.filter(event => {
      const eventStart = new Date(event.start);
      const eventEnd = new Date(event.end);
      return date >= eventStart && date < eventEnd;
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
      <div class="grid grid-cols-8 border-b bg-muted/20">
        <div class="p-3 border-r text-sm font-medium text-muted-foreground">
          All Day
        </div>
        <div
          v-for="day in weekDays"
          :key="`allday-${day.date.getTime()}`"
          class="p-2 border-r last:border-r-0 min-h-[80px] space-y-1 cursor-pointer hover:bg-accent/50 transition-colors"
          :class="{ 'bg-blue-50 dark:bg-blue-950/30': day.isToday }"
          @click="emit('dateClick', day.date)"
        >
          <!-- Personal events -->
          <div
            v-for="event in day.events"
            :key="event.id"
            class="text-xs p-1.5 rounded cursor-pointer hover:shadow-sm transition-all"
            :style="{ backgroundColor: event.backgroundColor, borderLeft: `3px solid ${event.borderColor}` }"
            @click.stop="emit('eventClick', event)"
          >
            <div class="font-medium truncate">{{ event.title }}</div>
            <div class="flex items-center gap-1 mt-0.5 opacity-75">
              <component :is="getStatusIcon(event.status)" class="h-3 w-3" />
              <span class="capitalize">{{ event.status }}</span>
              <span v-if="event.days > 1" class="text-xs">({{ event.days }}d)</span>
            </div>
          </div>

          <!-- Team events -->
          <div
            v-for="event in day.teamEvents"
            :key="`team-${event.id}`"
            v-show="showTeamEvents"
            class="text-xs p-1.5 rounded cursor-pointer hover:shadow-sm transition-all border border-dashed"
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
