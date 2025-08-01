<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';
import { CalendarDaysIcon, ClockIcon, CheckCircleIcon, XCircleIcon, UsersIcon } from 'lucide-vue-next';
import ApexChart from 'vue3-apexcharts';

interface Props {
  stats: {
    total_requests: number;
    pending_requests: number;
    approved_requests: number;
    rejected_requests: number;
    days_taken: number;
  };
  recentRequests: Array<{
    id: number;
    leave_type: string;
    start_date: string;
    end_date: string;
    days_requested: number;
    status: string;
    reason: string;
    submitted_at: string;
  }>;
  leaveBalance: Array<{
    type: string;
    total_days: number;
    used_days: number;
    remaining_days: number;
    percentage_used: number;
  }>;
  monthlyUsage: Array<{
    month: string;
    days: number;
  }>;
  requestsByStatus: Array<{
    status: string;
    count: number;
  }>;
  upcomingLeave: Array<{
    id: number;
    leave_type: string;
    start_date: string;
    end_date: string;
    days_requested: number;
    days_until: number;
  }>;
  teamOnLeave: Array<{
    user_name: string;
    leave_type: string;
    start_date: string;
    end_date: string;
    days_requested: number;
  }>;
  user: {
    name: string;
    email: string;
    team: string | null;
  };
}

const props = defineProps<Props>();

const page = usePage<SharedData>();

// Chart configurations
const monthlyUsageChartOptions = ref({
  chart: {
    type: 'line',
    height: 350,
    toolbar: { show: false }
  },
  stroke: {
    curve: 'smooth',
    width: 3
  },
  colors: ['#3B82F6'],
  xaxis: {
    categories: props.monthlyUsage.map(item => item.month)
  },
  yaxis: {
    title: {
      text: 'Days Taken'
    }
  },
  grid: {
    borderColor: '#e0e0e0'
  },
  tooltip: {
    y: {
      formatter: (value: number) => `${value} days`
    }
  }
});

const monthlyUsageChartSeries = ref([
  {
    name: 'Days Taken',
    data: props.monthlyUsage.map(item => item.days)
  }
]);

const statusChartOptions = ref({
  chart: {
    type: 'donut',
    height: 300
  },
  colors: ['#F59E0B', '#10B981', '#EF4444'],
  labels: props.requestsByStatus.map(item => item.status),
  legend: {
    position: 'bottom'
  },
  plotOptions: {
    pie: {
      donut: {
        size: '65%'
      }
    }
  },
  tooltip: {
    y: {
      formatter: (value: number) => `${value} requests`
    }
  }
});

const statusChartSeries = ref(props.requestsByStatus.map(item => item.count));

// Helper functions
const getStatusBadgeVariant = (status: string) => {
  switch (status) {
    case 'approved': return 'default';
    case 'pending': return 'secondary';
    case 'rejected': return 'destructive';
    default: return 'outline';
  }
};

const getStatusIcon = (status: string) => {
  switch (status) {
    case 'approved': return CheckCircleIcon;
    case 'pending': return ClockIcon;
    case 'rejected': return XCircleIcon;
    default: return ClockIcon;
  }
};
</script>

<template>
  <Head title="Employee Dashboard" />

  <AppLayout :breadcrumbs="[
      { title: 'Dashboard', href: route('dashboard') }
    ]">

    <div class="space-y-6 p-6 max-w-4xl">
      <!-- Welcome Section -->
      <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg p-6 text-white">
        <h1 class="text-2xl font-bold mb-2">Welcome back, {{ props.user.name }}!</h1>
        <p class="text-blue-100">Here's your leave overview for this year.</p>
        <div class="mt-4 text-sm">
          <span v-if="props.user.team" class="bg-blue-500/30 px-2 py-1 rounded">{{ props.user.team }} Team</span>
        </div>
      </div>

      <!-- Quick Stats -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Requests</CardTitle>
            <CalendarDaysIcon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ props.stats.total_requests }}</div>
            <p class="text-xs text-muted-foreground">All time requests</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Pending</CardTitle>
            <ClockIcon class="h-4 w-4 text-yellow-500" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-yellow-600">{{ props.stats.pending_requests }}</div>
            <p class="text-xs text-muted-foreground">Awaiting approval</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Approved</CardTitle>
            <CheckCircleIcon class="h-4 w-4 text-green-500" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-green-600">{{ props.stats.approved_requests }}</div>
            <p class="text-xs text-muted-foreground">Successfully approved</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Days Taken</CardTitle>
            <CalendarDaysIcon class="h-4 w-4 text-blue-500" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-blue-600">{{ props.stats.days_taken }}</div>
            <p class="text-xs text-muted-foreground">This year</p>
          </CardContent>
        </Card>
      </div>

      <!-- Charts Row -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Monthly Usage Chart -->
        <Card>
          <CardHeader>
            <CardTitle>Monthly Leave Usage</CardTitle>
            <CardDescription>Your leave usage over the past 12 months</CardDescription>
          </CardHeader>
          <CardContent>
            <ApexChart
              type="line"
              height="300"
              :options="monthlyUsageChartOptions"
              :series="monthlyUsageChartSeries"
            />
          </CardContent>
        </Card>

        <!-- Status Distribution Chart -->
        <Card>
          <CardHeader>
            <CardTitle>Request Status Distribution</CardTitle>
            <CardDescription>Breakdown of your leave requests by status</CardDescription>
          </CardHeader>
          <CardContent>
            <ApexChart
              type="donut"
              height="300"
              :options="statusChartOptions"
              :series="statusChartSeries"
            />
          </CardContent>
        </Card>
      </div>

      <!-- Leave Balance -->
      <Card>
        <CardHeader>
          <CardTitle>Leave Balance</CardTitle>
          <CardDescription>Your remaining leave balance by type</CardDescription>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div v-for="balance in props.leaveBalance" :key="balance.type" class="space-y-2">
              <div class="flex justify-between items-center">
                <span class="text-sm font-medium">{{ balance.type }}</span>
                <span class="text-sm text-muted-foreground">
                  {{ balance.remaining_days }}/{{ balance.total_days }} days
                </span>
              </div>
              <Progress :value="balance.percentage_used" class="h-2" />
              <div class="text-xs text-muted-foreground">
                {{ balance.used_days }} used, {{ balance.remaining_days }} remaining
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Recent Activity and Upcoming Leave -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Requests -->
        <Card>
          <CardHeader>
            <CardTitle>Recent Requests</CardTitle>
            <CardDescription>Your latest leave requests</CardDescription>
          </CardHeader>
          <CardContent>
            <div class="space-y-4">
              <div v-if="props.recentRequests.length === 0" class="text-center text-muted-foreground py-4">
                No recent requests
              </div>
              <div v-for="request in props.recentRequests" :key="request.id" class="flex items-start space-x-3 p-3 rounded-lg border">
                <component :is="getStatusIcon(request.status)" class="h-5 w-5 mt-0.5" :class="{
                  'text-green-500': request.status === 'approved',
                  'text-yellow-500': request.status === 'pending',
                  'text-red-500': request.status === 'rejected'
                }" />
                <div class="flex-1 min-w-0">
                  <div class="flex items-center justify-between">
                    <p class="text-sm font-medium">{{ request.leave_type }}</p>
                    <Badge :variant="getStatusBadgeVariant(request.status)">{{ request.status }}</Badge>
                  </div>
                  <p class="text-sm text-muted-foreground">
                    {{ request.start_date }} - {{ request.end_date }} ({{ request.days_requested }} days)
                  </p>
                  <p class="text-xs text-muted-foreground mt-1">{{ request.reason }}</p>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Upcoming Leave & Team Status -->
        <div class="space-y-6">
          <!-- Upcoming Leave -->
          <Card>
            <CardHeader>
              <CardTitle>Upcoming Leave</CardTitle>
              <CardDescription>Your approved future leave</CardDescription>
            </CardHeader>
            <CardContent>
              <div class="space-y-3">
                <div v-if="props.upcomingLeave.length === 0" class="text-center text-muted-foreground py-4">
                  No upcoming leave scheduled
                </div>
                <div v-for="leave in props.upcomingLeave" :key="leave.id" class="flex items-center space-x-3 p-3 rounded-lg bg-green-50 border border-green-200">
                  <CalendarDaysIcon class="h-5 w-5 text-green-600" />
                  <div class="flex-1">
                    <p class="text-sm font-medium">{{ leave.leave_type }}</p>
                    <p class="text-sm text-muted-foreground">
                      {{ leave.start_date }} - {{ leave.end_date }}
                    </p>
                    <p class="text-xs text-green-600">In {{ leave.days_until }} days</p>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Team on Leave -->
          <Card v-if="props.teamOnLeave.length > 0">
            <CardHeader>
              <CardTitle>Team Members on Leave</CardTitle>
              <CardDescription>Current and upcoming team leave</CardDescription>
            </CardHeader>
            <CardContent>
              <div class="space-y-3">
                <div v-for="member in props.teamOnLeave" :key="member.user_name" class="flex items-center space-x-3 p-3 rounded-lg bg-blue-50 border border-blue-200">
                  <UsersIcon class="h-5 w-5 text-blue-600" />
                  <div class="flex-1">
                    <p class="text-sm font-medium">{{ member.user_name }}</p>
                    <p class="text-sm text-muted-foreground">
                      {{ member.leave_type }} - {{ member.start_date }} to {{ member.end_date }}
                    </p>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
