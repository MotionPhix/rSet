<script setup lang="ts">
import { computed } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';
import { 
  Calendar as CalendarIcon,
  Clock, 
  CheckCircle2, 
  XCircle, 
  AlertCircle,
  Users,
  TrendingUp,
  Activity
} from 'lucide-vue-next';
import { format, parseISO, isThisMonth, isToday, isFuture } from 'date-fns';

interface Event {
  id: number;
  title: string;
  start: string;
  end: string;
  status: string;
  days: number;
  user_name: string;
  type: string;
  extendedProps: {
    isOwnRequest: boolean;
  };
}

interface Props {
  events: Event[];
  teamEvents?: Event[];
  showTeamStats?: boolean;
  currentPeriod?: string;
}

const props = withDefaults(defineProps<Props>(), {
  teamEvents: () => [],
  showTeamStats: false,
  currentPeriod: 'This Month',
});

const personalStats = computed(() => {
  const thisMonth = props.events.filter(event => isThisMonth(parseISO(event.start)));
  const approved = thisMonth.filter(event => event.status === 'approved');
  const pending = thisMonth.filter(event => event.status === 'pending');
  const rejected = thisMonth.filter(event => event.status === 'rejected');
  
  const totalDays = approved.reduce((sum, event) => sum + event.days, 0);
  const upcomingEvents = props.events.filter(event => isFuture(parseISO(event.start)));
  
  return {
    totalRequests: thisMonth.length,
    approvedRequests: approved.length,
    pendingRequests: pending.length,
    rejectedRequests: rejected.length,
    totalDaysThisMonth: totalDays,
    upcomingEvents: upcomingEvents.length,
    approvalRate: thisMonth.length > 0 ? Math.round((approved.length / thisMonth.length) * 100) : 0,
  };
});

const teamStats = computed(() => {
  if (!props.showTeamStats) return null;
  
  const thisMonth = props.teamEvents.filter(event => isThisMonth(parseISO(event.start)));
  const approved = thisMonth.filter(event => event.status === 'approved');
  const pending = thisMonth.filter(event => event.status === 'pending');
  
  const totalDays = approved.reduce((sum, event) => sum + event.days, 0);
  const uniqueMembers = new Set(thisMonth.map(event => event.user_name)).size;
  
  return {
    totalRequests: thisMonth.length,
    approvedRequests: approved.length,
    pendingRequests: pending.length,
    totalDaysThisMonth: totalDays,
    activeMembers: uniqueMembers,
  };
});

const todaysEvents = computed(() => {
  return props.events.filter(event => {
    const start = parseISO(event.start);
    const end = parseISO(event.end);
    const today = new Date();
    return today >= start && today <= end && event.status === 'approved';
  });
});

const getStatusIcon = (status: string) => {
  switch (status) {
    case 'approved': return CheckCircle2;
    case 'pending': return Clock;
    case 'rejected': return XCircle;
    default: return AlertCircle;
  }
};
</script>

<template>
  <div class="space-y-4">
    <!-- Today's Status -->
    <Card v-if="todaysEvents.length > 0">
      <CardContent class="p-4">
        <div class="flex items-center gap-2 mb-3">
          <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
          <h3 class="font-medium text-green-700 dark:text-green-400">You're on leave today</h3>
        </div>
        <div class="space-y-2">
          <div
            v-for="event in todaysEvents"
            :key="event.id"
            class="text-sm p-2 bg-green-50 dark:bg-green-950/30 rounded border border-green-200 dark:border-green-800"
          >
            <div class="font-medium">{{ event.title }}</div>
            <div class="text-green-600 dark:text-green-400">{{ event.type }}</div>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Personal Stats -->
    <Card>
      <CardHeader class="pb-3">
        <CardTitle class="flex items-center gap-2 text-sm">
          <CalendarIcon class="h-4 w-4" />
          My Leave Stats - {{ currentPeriod }}
        </CardTitle>
      </CardHeader>
      <CardContent class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-1">
            <div class="text-2xl font-bold">{{ personalStats.totalRequests }}</div>
            <div class="text-xs text-muted-foreground">Total Requests</div>
          </div>
          <div class="space-y-1">
            <div class="text-2xl font-bold text-green-600">{{ personalStats.totalDaysThisMonth }}</div>
            <div class="text-xs text-muted-foreground">Days Taken</div>
          </div>
        </div>

        <div class="space-y-2">
          <div class="flex justify-between text-sm">
            <span>Approval Rate</span>
            <span>{{ personalStats.approvalRate }}%</span>
          </div>
          <Progress :value="personalStats.approvalRate" class="h-2" />
        </div>

        <div class="grid grid-cols-3 gap-2 text-xs">
          <div class="text-center p-2 bg-green-50 dark:bg-green-950/30 rounded">
            <div class="font-medium text-green-700 dark:text-green-400">{{ personalStats.approvedRequests }}</div>
            <div class="text-green-600 dark:text-green-500">Approved</div>
          </div>
          <div class="text-center p-2 bg-yellow-50 dark:bg-yellow-950/30 rounded">
            <div class="font-medium text-yellow-700 dark:text-yellow-400">{{ personalStats.pendingRequests }}</div>
            <div class="text-yellow-600 dark:text-yellow-500">Pending</div>
          </div>
          <div class="text-center p-2 bg-red-50 dark:bg-red-950/30 rounded">
            <div class="font-medium text-red-700 dark:text-red-400">{{ personalStats.rejectedRequests }}</div>
            <div class="text-red-600 dark:text-red-500">Rejected</div>
          </div>
        </div>

        <div v-if="personalStats.upcomingEvents > 0" class="flex items-center gap-2 text-sm text-muted-foreground">
          <TrendingUp class="h-4 w-4" />
          {{ personalStats.upcomingEvents }} upcoming leave{{ personalStats.upcomingEvents !== 1 ? 's' : '' }}
        </div>
      </CardContent>
    </Card>

    <!-- Team Stats -->
    <Card v-if="teamStats">
      <CardHeader class="pb-3">
        <CardTitle class="flex items-center gap-2 text-sm">
          <Users class="h-4 w-4" />
          Team Stats - {{ currentPeriod }}
        </CardTitle>
      </CardHeader>
      <CardContent class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-1">
            <div class="text-2xl font-bold">{{ teamStats.totalRequests }}</div>
            <div class="text-xs text-muted-foreground">Team Requests</div>
          </div>
          <div class="space-y-1">
            <div class="text-2xl font-bold text-blue-600">{{ teamStats.activeMembers }}</div>
            <div class="text-xs text-muted-foreground">Active Members</div>
          </div>
        </div>

        <div class="grid grid-cols-3 gap-2 text-xs">
          <div class="text-center p-2 bg-green-50 dark:bg-green-950/30 rounded">
            <div class="font-medium text-green-700 dark:text-green-400">{{ teamStats.approvedRequests }}</div>
            <div class="text-green-600 dark:text-green-500">Approved</div>
          </div>
          <div class="text-center p-2 bg-yellow-50 dark:bg-yellow-950/30 rounded">
            <div class="font-medium text-yellow-700 dark:text-yellow-400">{{ teamStats.pendingRequests }}</div>
            <div class="text-yellow-600 dark:text-yellow-500">Pending</div>
          </div>
          <div class="text-center p-2 bg-blue-50 dark:bg-blue-950/30 rounded">
            <div class="font-medium text-blue-700 dark:text-blue-400">{{ teamStats.totalDaysThisMonth }}</div>
            <div class="text-blue-600 dark:text-blue-500">Total Days</div>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Quick Actions -->
    <Card>
      <CardHeader class="pb-3">
        <CardTitle class="flex items-center gap-2 text-sm">
          <Activity class="h-4 w-4" />
          Quick Actions
        </CardTitle>
      </CardHeader>
      <CardContent>
        <div class="text-xs text-muted-foreground">
          Click on any date to create a new leave request, or click on an existing event to view details.
        </div>
      </CardContent>
    </Card>
  </div>
</template>
