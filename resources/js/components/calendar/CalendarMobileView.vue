<script setup lang="ts">
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { 
  Clock, 
  CheckCircle2, 
  XCircle, 
  AlertCircle,
  Calendar as CalendarIcon,
  Users,
  ChevronDown,
  ChevronUp
} from 'lucide-vue-next';
import { format, isToday, parseISO } from 'date-fns';
import { ref } from 'vue';

interface Event {
  id: number;
  title: string;
  start: string;
  end: string;
  status: string;
  user_name: string;
  days: number;
  type: string;
  backgroundColor: string;
  borderColor: string;
  extendedProps: {
    isOwnRequest: boolean;
    appliedAt: string;
  };
}

interface Props {
  events: Event[];
  teamEvents?: Event[];
  showTeamEvents?: boolean;
  limit?: number;
}

const props = withDefaults(defineProps<Props>(), {
  teamEvents: () => [],
  showTeamEvents: false,
  limit: 5,
});

const emit = defineEmits<{
  eventClick: [event: Event];
}>();

const showAll = ref(false);
const showAllTeam = ref(false);

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

const sortedEvents = computed(() => {
  return [...props.events].sort((a, b) => new Date(a.start).getTime() - new Date(b.start).getTime());
});

const sortedTeamEvents = computed(() => {
  return [...props.teamEvents].sort((a, b) => new Date(a.start).getTime() - new Date(b.start).getTime());
});

const visibleEvents = computed(() => {
  return showAll.value ? sortedEvents.value : sortedEvents.value.slice(0, props.limit);
});

const visibleTeamEvents = computed(() => {
  return showAllTeam.value ? sortedTeamEvents.value : sortedTeamEvents.value.slice(0, props.limit);
});

const hiddenEventsCount = computed(() => Math.max(0, sortedEvents.value.length - props.limit));
const hiddenTeamEventsCount = computed(() => Math.max(0, sortedTeamEvents.value.length - props.limit));

const isEventToday = (event: Event) => {
  const eventStart = parseISO(event.start);
  const eventEnd = parseISO(event.end);
  const today = new Date();
  return today >= eventStart && today <= eventEnd;
};

const getEventDateRange = (event: Event) => {
  const start = parseISO(event.start);
  const end = parseISO(event.end);
  
  if (format(start, 'yyyy-MM-dd') === format(end, 'yyyy-MM-dd')) {
    return format(start, 'MMM dd, yyyy');
  }
  
  if (format(start, 'yyyy') === format(end, 'yyyy')) {
    return `${format(start, 'MMM dd')} - ${format(end, 'MMM dd, yyyy')}`;
  }
  
  return `${format(start, 'MMM dd, yyyy')} - ${format(end, 'MMM dd, yyyy')}`;
};
</script>

<template>
  <div class="space-y-4">
    <!-- Personal Events -->
    <Card v-if="events.length > 0">
      <CardContent class="p-4">
        <div class="flex items-center gap-2 mb-4">
          <CalendarIcon class="h-4 w-4 text-muted-foreground" />
          <h3 class="font-medium">My Leave Requests</h3>
          <Badge variant="outline">{{ events.length }}</Badge>
        </div>
        
        <div class="space-y-3">
          <div
            v-for="event in visibleEvents"
            :key="event.id"
            class="p-3 rounded-lg border cursor-pointer hover:shadow-sm transition-all"
            :class="{
              'ring-2 ring-blue-500 ring-opacity-50': isEventToday(event),
              'bg-blue-50 dark:bg-blue-950/30': isEventToday(event),
            }"
            :style="{ borderLeftColor: event.borderColor, borderLeftWidth: '4px' }"
            @click="emit('eventClick', event)"
          >
            <div class="flex items-start justify-between mb-2">
              <div class="flex-1 min-w-0">
                <h4 class="font-medium truncate">{{ event.title }}</h4>
                <p class="text-sm text-muted-foreground">{{ event.type }}</p>
              </div>
              <Badge :variant="getStatusColor(event.status)" class="ml-2">
                <component :is="getStatusIcon(event.status)" class="h-3 w-3 mr-1" />
                {{ event.status }}
              </Badge>
            </div>
            
            <div class="text-sm text-muted-foreground space-y-1">
              <div>📅 {{ getEventDateRange(event) }}</div>
              <div>⏱️ {{ event.days }} {{ event.days === 1 ? 'day' : 'days' }}</div>
              <div class="text-xs">Applied {{ event.extendedProps.appliedAt }}</div>
            </div>
          </div>
          
          <!-- Show more button -->
          <Button
            v-if="hiddenEventsCount > 0"
            variant="ghost"
            size="sm"
            class="w-full"
            @click="showAll = !showAll"
          >
            <component :is="showAll ? ChevronUp : ChevronDown" class="h-4 w-4 mr-1" />
            {{ showAll ? 'Show Less' : `Show ${hiddenEventsCount} More` }}
          </Button>
        </div>
      </CardContent>
    </Card>

    <!-- Team Events -->
    <Card v-if="showTeamEvents && teamEvents.length > 0">
      <CardContent class="p-4">
        <div class="flex items-center gap-2 mb-4">
          <Users class="h-4 w-4 text-muted-foreground" />
          <h3 class="font-medium">Team Leave</h3>
          <Badge variant="outline">{{ teamEvents.length }}</Badge>
        </div>
        
        <div class="space-y-3">
          <div
            v-for="event in visibleTeamEvents"
            :key="`team-${event.id}`"
            class="p-3 rounded-lg border border-dashed cursor-pointer hover:shadow-sm transition-all"
            :class="{
              'ring-2 ring-blue-500 ring-opacity-30': isEventToday(event),
              'bg-blue-50/50 dark:bg-blue-950/20': isEventToday(event),
            }"
            :style="{ borderLeftColor: event.borderColor, borderLeftWidth: '4px' }"
            @click="emit('eventClick', event)"
          >
            <div class="flex items-start justify-between mb-2">
              <div class="flex-1 min-w-0">
                <h4 class="font-medium truncate">{{ event.user_name }}</h4>
                <p class="text-sm text-muted-foreground">{{ event.type }}</p>
              </div>
              <Badge :variant="getStatusColor(event.status)" class="ml-2">
                <component :is="getStatusIcon(event.status)" class="h-3 w-3 mr-1" />
                {{ event.status }}
              </Badge>
            </div>
            
            <div class="text-sm text-muted-foreground space-y-1">
              <div>📅 {{ getEventDateRange(event) }}</div>
              <div>⏱️ {{ event.days }} {{ event.days === 1 ? 'day' : 'days' }}</div>
            </div>
          </div>
          
          <!-- Show more button -->
          <Button
            v-if="hiddenTeamEventsCount > 0"
            variant="ghost"
            size="sm"
            class="w-full"
            @click="showAllTeam = !showAllTeam"
          >
            <component :is="showAllTeam ? ChevronUp : ChevronDown" class="h-4 w-4 mr-1" />
            {{ showAllTeam ? 'Show Less' : `Show ${hiddenTeamEventsCount} More` }}
          </Button>
        </div>
      </CardContent>
    </Card>

    <!-- Empty state -->
    <Card v-if="events.length === 0 && (!showTeamEvents || teamEvents.length === 0)">
      <CardContent class="p-8 text-center">
        <CalendarIcon class="h-12 w-12 mx-auto mb-4 text-muted-foreground opacity-50" />
        <h3 class="font-medium mb-2">No leave requests</h3>
        <p class="text-sm text-muted-foreground">
          {{ showTeamEvents ? 'No leave requests found for you or your team.' : 'You have no leave requests for this period.' }}
        </p>
      </CardContent>
    </Card>
  </div>
</template>
