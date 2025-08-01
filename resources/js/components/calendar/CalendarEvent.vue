<script setup lang="ts">
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { 
  CheckCircle2, 
  Clock, 
  XCircle, 
  AlertCircle,
  Users,
  MapPin,
  User
} from 'lucide-vue-next';
import { format, parseISO, differenceInDays } from 'date-fns';

interface Event {
  id: number;
  title: string;
  start: string;
  end: string;
  type: string;
  status: string;
  reason?: string;
  user_name: string;
  user_id: number;
  days: number;
  color: string;
  backgroundColor: string;
  borderColor: string;
  allDay: boolean;
  extendedProps: {
    status: string;
    type: string;
    reason?: string;
    appliedAt: string;
    isOwnRequest: boolean;
  };
}

interface Props {
  event: Event;
  variant?: 'default' | 'compact' | 'detailed';
  showUser?: boolean;
  interactive?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'default',
  showUser: false,
  interactive: true,
});

const emit = defineEmits<{
  click: [event: Event];
  view: [event: Event];
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

const eventDuration = computed(() => {
  const start = parseISO(props.event.start);
  const end = parseISO(props.event.end);
  const days = differenceInDays(end, start) + 1;
  return days === 1 ? '1 day' : `${days} days`;
});

const isTeamEvent = computed(() => !props.event.extendedProps.isOwnRequest);
</script>

<template>
  <!-- Compact variant for calendar cells -->
  <div
    v-if="variant === 'compact'"
    class="text-xs p-1.5 rounded transition-all"
    :class="{
      'cursor-pointer hover:shadow-sm hover:scale-105': interactive,
      'border border-dashed': isTeamEvent,
    }"
    :style="{ 
      backgroundColor: isTeamEvent ? event.backgroundColor + '40' : event.backgroundColor, 
      borderLeftColor: event.borderColor,
      borderLeftWidth: '3px',
      borderLeftStyle: 'solid'
    }"
    @click="interactive && emit('click', event)"
  >
    <div class="flex items-center gap-1 mb-0.5">
      <Users v-if="isTeamEvent" class="h-3 w-3 shrink-0" />
      <div class="font-medium truncate" :title="event.title">{{ event.title }}</div>
    </div>
    <div class="flex items-center gap-1 opacity-75">
      <component :is="getStatusIcon(event.status)" class="h-3 w-3 shrink-0" />
      <span class="capitalize">{{ event.status }}</span>
      <span v-if="event.days > 1" class="text-xs opacity-60">({{ event.days }}d)</span>
    </div>
  </div>

  <!-- Default card variant -->
  <Card
    v-else-if="variant === 'default'"
    class="transition-all"
    :class="{
      'cursor-pointer hover:shadow-md hover:scale-105': interactive,
      'border-dashed': isTeamEvent,
    }"
    @click="interactive && emit('click', event)"
  >
    <CardContent class="p-4">
      <div class="flex justify-between items-start mb-2">
        <div class="flex items-center gap-2">
          <div
            class="w-3 h-3 rounded-full"
            :style="{ backgroundColor: event.borderColor }"
          ></div>
          <h4 class="font-medium">{{ event.title }}</h4>
          <Users v-if="isTeamEvent" class="h-4 w-4 text-muted-foreground" />
        </div>
        <Badge :variant="getStatusColor(event.status)">
          <component :is="getStatusIcon(event.status)" class="h-3 w-3 mr-1" />
          {{ event.status }}
        </Badge>
      </div>

      <div class="space-y-1 text-sm text-muted-foreground">
        <div class="flex items-center gap-1">
          <MapPin class="h-3 w-3" />
          {{ format(parseISO(event.start), 'MMM dd') }} - {{ format(parseISO(event.end), 'MMM dd') }}
          <span class="ml-auto">{{ eventDuration }}</span>
        </div>
        
        <div v-if="showUser || isTeamEvent" class="flex items-center gap-1">
          <User class="h-3 w-3" />
          {{ event.user_name }}
        </div>
        
        <div class="text-xs">{{ event.type }}</div>
      </div>

      <div v-if="event.reason && variant !== 'compact'" class="mt-2 p-2 bg-muted/50 rounded text-xs">
        {{ event.reason }}
      </div>
    </CardContent>
  </Card>

  <!-- Detailed variant with full information -->
  <Card
    v-else-if="variant === 'detailed'"
    class="transition-all"
    :class="{
      'cursor-pointer hover:shadow-md': interactive,
    }"
    @click="interactive && emit('click', event)"
  >
    <CardContent class="p-6">
      <div class="flex justify-between items-start mb-4">
        <div class="flex items-center gap-3">
          <div
            class="w-4 h-4 rounded-full"
            :style="{ backgroundColor: event.borderColor }"
          ></div>
          <div>
            <h3 class="font-semibold text-lg">{{ event.title }}</h3>
            <p class="text-sm text-muted-foreground">{{ event.type }}</p>
          </div>
          <Users v-if="isTeamEvent" class="h-5 w-5 text-muted-foreground" />
        </div>
        <Badge :variant="getStatusColor(event.status)" class="text-sm">
          <component :is="getStatusIcon(event.status)" class="h-4 w-4 mr-1" />
          {{ event.status }}
        </Badge>
      </div>

      <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
          <div class="text-sm font-medium">Start Date</div>
          <div class="text-sm text-muted-foreground">{{ format(parseISO(event.start), 'EEEE, MMM dd, yyyy') }}</div>
        </div>
        <div>
          <div class="text-sm font-medium">End Date</div>
          <div class="text-sm text-muted-foreground">{{ format(parseISO(event.end), 'EEEE, MMM dd, yyyy') }}</div>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
          <div class="text-sm font-medium">Duration</div>
          <div class="text-sm text-muted-foreground">{{ eventDuration }}</div>
        </div>
        <div v-if="showUser || isTeamEvent">
          <div class="text-sm font-medium">Employee</div>
          <div class="text-sm text-muted-foreground">{{ event.user_name }}</div>
        </div>
      </div>

      <div v-if="event.reason" class="mb-4">
        <div class="text-sm font-medium mb-1">Reason</div>
        <div class="text-sm text-muted-foreground p-3 bg-muted/50 rounded">{{ event.reason }}</div>
      </div>

      <div class="text-xs text-muted-foreground">
        Applied on {{ event.extendedProps.appliedAt }}
      </div>

      <div v-if="interactive" class="flex gap-2 mt-4">
        <Button size="sm" @click.stop="emit('view', event)">
          View Details
        </Button>
      </div>
    </CardContent>
  </Card>
</template>
