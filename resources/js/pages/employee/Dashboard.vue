<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { CalendarIcon, UsersIcon, FileCheckIcon, TrendingUpIcon } from 'lucide-vue-next';
import ApexCharts from 'apexcharts';

interface Props {
  stats: {
    totalEmployees: number;
    pendingRequests: number;
    approvedThisMonth: number;
    leaveTypes: number;
    myLeaveBalance: {
      total: number;
      used: number;
      remaining: number;
    };
    recentActivity: Array<{
      id: number;
      user_name: string;
      action: string;
      time: string;
      status: string;
    }>;
    leavesByMonth: Array<{
      month: string;
      approved: number;
      pending: number;
      rejected: number;
    }>;
    leavesByType: Array<{
      name: string;
      count: number;
      color: string;
    }>;
  };
}

const props = defineProps<Props>();

const page = usePage<SharedData>();

// Get user info and company
const user = computed(() => page.props.auth.user);
const company = computed(() => page.props.auth.company);
const userRoles = computed(() => user.value?.roles?.map(role => role.name) || []);
const isAdmin = computed(() => userRoles.value.includes('admin'));
const isHR = computed(() => userRoles.value.includes('hr'));
const isManager = computed(() => userRoles.value.includes('manager'));

// Role-aware breadcrumbs
const breadcrumbs = computed((): BreadcrumbItem[] => [
  {
    title: 'Dashboard',
    href: isAdmin.value ? route('admin.dashboard') : route('dashboard')
  }
]);

// Welcome message based on role
const welcomeMessage = computed(() => {
  if (isAdmin.value) return `Welcome back, ${user.value?.name}! Here's your company overview.`;
  if (isHR.value) return `Welcome back, ${user.value?.name}! Here's your HR dashboard.`;
  if (isManager.value) return `Welcome back, ${user.value?.name}! Here's your team overview.`;
  return `Welcome back, ${user.value?.name}! Here's your dashboard.`;
});

// Apex Charts Configuration
const leaveBalanceChartOptions = computed(() => ({
  chart: {
    type: 'donut',
    height: 280,
    background: 'transparent'
  },
  colors: ['#10b981', '#f59e0b', '#ef4444'],
  labels: ['Used', 'Remaining', 'Pending'],
  series: [
    props.stats.myLeaveBalance.used,
    props.stats.myLeaveBalance.remaining,
    props.stats.myLeaveBalance.total - props.stats.myLeaveBalance.used - props.stats.myLeaveBalance.remaining
  ],
  legend: {
    position: 'bottom'
  },
  responsive: [{
    breakpoint: 480,
    options: {
      chart: {
        width: 200
      },
      legend: {
        position: 'bottom'
      }
    }
  }],
  tooltip: {
    y: {
      formatter: function(val: number) {
        return val + ' days';
      }
    }
  }
}));

const monthlyLeaveChartOptions = computed(() => ({
  chart: {
    type: 'column',
    height: 350,
    background: 'transparent'
  },
  colors: ['#10b981', '#f59e0b', '#ef4444'],
  series: [{
    name: 'Approved',
    data: props.stats.leavesByMonth.map(item => item.approved)
  }, {
    name: 'Pending',
    data: props.stats.leavesByMonth.map(item => item.pending)
  }, {
    name: 'Rejected',
    data: props.stats.leavesByMonth.map(item => item.rejected)
  }],
  xaxis: {
    categories: props.stats.leavesByMonth.map(item => item.month)
  },
  plotOptions: {
    bar: {
      horizontal: false,
      columnWidth: '55%',
      endingShape: 'rounded'
    }
  },
  dataLabels: {
    enabled: false
  },
  stroke: {
    show: true,
    width: 2,
    colors: ['transparent']
  },
  fill: {
    opacity: 1
  },
  tooltip: {
    y: {
      formatter: function(val: number) {
        return val + ' requests';
      }
    }
  }
}));

const leaveTypeChartOptions = computed(() => ({
  chart: {
    type: 'pie',
    height: 300,
    background: 'transparent'
  },
  colors: props.stats.leavesByType.map(item => item.color),
  labels: props.stats.leavesByType.map(item => item.name),
  series: props.stats.leavesByType.map(item => item.count),
  legend: {
    position: 'bottom'
  },
  responsive: [{
    breakpoint: 480,
    options: {
      chart: {
        width: 200
      },
      legend: {
        position: 'bottom'
      }
    }
  }]
}));

// Initialize charts on component mount
onMounted(() => {
  // Only render charts if we have data
  if (props.stats) {
    const leaveBalanceChart = new ApexCharts(
      document.querySelector('#leave-balance-chart'),
      leaveBalanceChartOptions.value
    );
    leaveBalanceChart.render();

    const monthlyLeaveChart = new ApexCharts(
      document.querySelector('#monthly-leave-chart'),
      monthlyLeaveChartOptions.value
    );
    monthlyLeaveChart.render();

    const leaveTypeChart = new ApexCharts(
      document.querySelector('#leave-type-chart'),
      leaveTypeChartOptions.value
    );
    leaveTypeChart.render();
  }
});
</script>

<template>
  <Head title="Dashboard" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
      <!-- Welcome Section -->
      <div class="space-y-2">
        <h1 class="text-3xl font-bold tracking-tight">
          {{ company?.name || 'Dashboard' }}
        </h1>
        <p class="text-muted-foreground">
          {{ welcomeMessage }}
        </p>
      </div>

      <!-- Stats Cards -->
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <!-- Total Employees (Admin/HR only) -->
        <Card v-if="isAdmin || isHR">
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Employees</CardTitle>
            <UsersIcon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ props.stats?.totalEmployees || 0 }}</div>
            <p class="text-xs text-muted-foreground">
              +2 from last month
            </p>
          </CardContent>
        </Card>

        <!-- Pending Requests -->
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">
              {{ isAdmin || isHR || isManager ? 'Pending Requests' : 'My Requests' }}
            </CardTitle>
            <CalendarIcon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ props.stats?.pendingRequests || 0 }}</div>
            <p class="text-xs text-muted-foreground">
              {{ isAdmin || isHR || isManager ? 'Awaiting approval' : 'Pending approval' }}
            </p>
          </CardContent>
        </Card>

        <!-- Approved This Month -->
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Approved This Month</CardTitle>
            <TrendingUpIcon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ props.stats?.approvedThisMonth || 0 }}</div>
            <p class="text-xs text-muted-foreground">
              +12% from last month
            </p>
          </CardContent>
        </Card>

        <!-- Leave Types (Admin/HR only) -->
        <Card v-if="isAdmin || isHR">
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Leave Types</CardTitle>
            <FileCheckIcon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ props.stats?.leaveTypes || 0 }}</div>
            <p class="text-xs text-muted-foreground">
              Active leave types
            </p>
          </CardContent>
        </Card>
      </div>

      <!-- Quick Actions -->
      <div class="grid gap-4 md:grid-cols-2">
        <!-- Recent Activity -->
        <Card>
          <CardHeader>
            <CardTitle>Recent Activity</CardTitle>
            <CardDescription>Latest leave requests and updates</CardDescription>
          </CardHeader>
          <CardContent>
            <div class="space-y-4" v-if="props.stats?.recentActivity?.length">
              <div class="flex items-center space-x-4" v-for="activity in props.stats.recentActivity"
                   :key="activity.id">
                <div class="w-2 h-2 rounded-full" :class="{
                                    'bg-green-500': activity.status === 'approved',
                                    'bg-yellow-500': activity.status === 'pending',
                                    'bg-red-500': activity.status === 'rejected'
                                }"></div>
                <div class="flex-1 space-y-1">
                  <p class="text-sm font-medium">
                    {{ activity.user_name }}'s {{ activity.action }}
                  </p>
                  <p class="text-xs text-muted-foreground">
                    {{ activity.time }}
                  </p>
                </div>
              </div>
            </div>
            <div v-else class="text-sm text-muted-foreground">
              No recent activity
            </div>
          </CardContent>
        </Card>

        <!-- Quick Actions -->
        <Card>
          <CardHeader>
            <CardTitle>Quick Actions</CardTitle>
            <CardDescription>Common tasks and shortcuts</CardDescription>
          </CardHeader>
          <CardContent>
            <div class="grid gap-2">
              <a
                :href="route('leave-requests.create')"
                class="flex items-center justify-between p-3 rounded-lg border hover:bg-accent transition-colors"
              >
                <span class="text-sm font-medium">Request Leave</span>
                <CalendarIcon class="h-4 w-4" />
              </a>

              <a
                :href="route('leave-requests.index')"
                class="flex items-center justify-between p-3 rounded-lg border hover:bg-accent transition-colors"
              >
                <span class="text-sm font-medium">View My Requests</span>
                <FileCheckIcon class="h-4 w-4" />
              </a>

              <a
                :href="route('employee.reports.index')"
                class="flex items-center justify-between p-3 rounded-lg border hover:bg-accent transition-colors"
              >
                <span class="text-sm font-medium">My Reports</span>
                <TrendingUpIcon class="h-4 w-4" />
              </a>

              <a
                v-if="isManager"
                :href="route('team.leave-requests.index')"
                class="flex items-center justify-between p-3 rounded-lg border hover:bg-accent transition-colors"
              >
                <span class="text-sm font-medium">Team Requests</span>
                <UsersIcon class="h-4 w-4" />
              </a>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Charts Section (only show if we have data) -->
      <div v-if="props.stats" class="space-y-6">
        <!-- Leave Balance Chart -->
        <Card v-if="props.stats.myLeaveBalance">
          <CardHeader>
            <CardTitle>My Leave Balance</CardTitle>
            <CardDescription>Your current leave allocation and usage</CardDescription>
          </CardHeader>
          <CardContent>
            <div id="leave-balance-chart" class="w-full"></div>
          </CardContent>
        </Card>

        <!-- Monthly Leave Requests Chart -->
        <Card v-if="props.stats.leavesByMonth?.length">
          <CardHeader>
            <CardTitle>Monthly Leave Requests</CardTitle>
            <CardDescription>Leave request trends over time</CardDescription>
          </CardHeader>
          <CardContent>
            <div id="monthly-leave-chart" class="w-full"></div>
          </CardContent>
        </Card>

        <!-- Leave Types Distribution Chart -->
        <Card v-if="props.stats.leavesByType?.length">
          <CardHeader>
            <CardTitle>Leave Types Distribution</CardTitle>
            <CardDescription>Breakdown of leave requests by type</CardDescription>
          </CardHeader>
          <CardContent>
            <div id="leave-type-chart" class="w-full"></div>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
