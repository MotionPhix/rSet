<script setup lang="ts">
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { 
  CheckCircle2, 
  Clock, 
  XCircle, 
  AlertCircle,
  Users,
  Eye 
} from 'lucide-vue-next';
import { format, parseISO } from 'date-fns';

interface Event {
  id: string | number;
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

interface Props {
  events: Event[];
  teamEvents?: Event[];
  isToday?: boolean;
  isCurrentMonth?: boolean;
  date: Date;
  maxVisible?: number;
  showTeamEvents?: boolean;
  compact?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  teamEvents: () => [],
  isToday: false,
  isCurrentMonth: true,
  maxVisible: 3,
  showTeamEvents: false,
  compact: false,
});

const emit = defineEmits<{
  eventClick: [event: Event];
  dayClick: [date: Date];
}>();

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

const visibleEvents = computed(() => {
  const personal = props.events.slice(0, props.maxVisible);
  const team = props.showTeamEvents ? props.teamEvents.slice(0, Math.max(0, props.maxVisible - personal.length)) : [];
  return { personal, team };
});

const totalHiddenEvents = computed(() => {
  const totalEvents = props.events.length + (props.showTeamEvents ? props.teamEvents.length : 0);
  return Math.max(0, totalEvents - props.maxVisible);
});
</script>

<template>
  <div
    :data-date="format(date, 'yyyy-MM-dd')"
    class="min-h-[100px] border-r border-b last:border-r-0 relative group cursor-pointer hover:bg-accent/30 transition-colors"
    :class="{
      'bg-muted/20': !isCurrentMonth,
      'bg-blue-50 dark:bg-blue-950/30': isToday && isCurrentMonth,
      'min-h-[80px]': compact,
    }"
    @click="emit('dayClick', date)"
  >
    <!-- Date number -->
    <div class="p-2">
      <div
        class="w-6 h-6 flex items-center justify-center rounded-full text-sm font-medium"
        :class="{
          'bg-blue-600 text-white': isToday,
          'text-muted-foreground': !isCurrentMonth,
          'text-foreground': isCurrentMonth && !isToday,
        }"
      >
        {{ format(date, 'd') }}
      </div>
    </div>

    <!-- Events for this day -->
    <div class="px-1 pb-1 space-y-1">
      <!-- Personal events -->
      <div
        v-for="event in visibleEvents.personal"
        :key="event.id"
        class="text-xs p-1.5 rounded cursor-pointer hover:shadow-sm transition-all hover:scale-105"
        :style="{ backgroundColor: event.backgroundColor, borderLeft: `3px solid ${event.borderColor}` }"
        @click.stop="emit('eventClick', event)"
      >
        <div class="font-medium truncate" :title="event.title">{{ event.title }}</div>
        <div class="flex items-center gap-1 opacity-75 mt-0.5">
          <component :is="getStatusIcon(event.status || 'pending')" class="h-3 w-3 shrink-0" />
          <span class="capitalize truncate">{{ event.status || 'holiday' }}</span>
          <span v-if="event.days && event.days > 1" class="text-xs opacity-60">({{ event.days }}d)</span>
        </div>
      </div>

      <!-- Team events (if team view is enabled) -->
      <div
        v-for="event in visibleEvents.team"
        :key="`team-${event.id}`"
        class="text-xs p-1.5 rounded cursor-pointer hover:shadow-sm transition-all hover:scale-105 border border-dashed"
        :style="{ backgroundColor: event.backgroundColor + '40', borderColor: event.borderColor }"
        @click.stop="emit('eventClick', event)"
      >
        <div class="flex items-center gap-1">
          <Users class="h-3 w-3 shrink-0" />
          <div class="font-medium truncate" :title="event.title">{{ event.title }}</div>
        </div>
        <div class="opacity-75 mt-0.5 truncate">{{ event.user_name || 'Team Member' }}</div>
      </div>

      <!-- More indicator -->
      <div
        v-if="totalHiddenEvents > 0"
        class="text-xs text-muted-foreground px-1 py-0.5 hover:text-foreground transition-colors cursor-pointer"
        @click.stop="emit('dayClick', date)"
      >
        +{{ totalHiddenEvents }} more
      </div>
    </div>

    <!-- Quick action button (on hover) -->
    <div class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity">
      <Button 
        variant="ghost" 
        size="icon" 
        class="h-6 w-6 bg-background/80 backdrop-blur-sm hover:bg-background"
        @click.stop="emit('dayClick', date)"
      >
        <Eye class="h-3 w-3" />
      </Button>
    </div>
  </div>
</template>
