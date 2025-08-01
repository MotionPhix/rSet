<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { 
  CalendarIcon,
  UsersIcon,
  TrendingUpIcon,
  BarChart3Icon,
  ClockIcon,
  CheckCircleIcon
} from 'lucide-vue-next';
import ApexCharts from 'apexcharts';

interface Props {
  analytics: {
    leave_requests: {
      total: number;
      approved: number;
      pending: number;
      rejected: number;
      monthly_trend: Array<{ month: string; count: number; }>;
    };
    employees: {
      total: number;
      on_leave_today: number;
      most_active_team: string;
      by_team: Array<{ team: string; count: number; }>;
    };
    leave_types: {
      most_used: Array<{ type: string; count: number; percentage: number; }>;
      monthly_breakdown: Array<{ month: string; [key: string]: number | string; }>;
    };
    trends: {
      peak_months: Array<{ month: string; requests: number; }>;
      approval_rate: number;
      average_duration: number;
    };
  };
}

const props = defineProps<Props>();

const breadcrumbs = computed((): BreadcrumbItem[] => [
  {
    title: 'Admin',
    href: route('admin.dashboard')
  },
  {
    title: 'Analytics',
    href: route('admin.analytics.index')
  }
]);

// Chart configurations
const leaveRequestsChartOptions = computed(() => ({
  chart: {
    type: 'line',
    height: 350,
    background: 'transparent'
  },
  colors: ['#3b82f6'],
  series: [{
    name: 'Leave Requests',
    data: props.analytics?.leave_requests?.monthly_trend?.map(item => item.count) || []
  }],
  xaxis: {
    categories: props.analytics?.leave_requests?.monthly_trend?.map(item => item.month) || []
  },
  stroke: {
    curve: 'smooth',
    width: 3
  }
}));

const leaveTypesChartOptions = computed(() => ({
  chart: {
    type: 'donut',
    height: 300,
    background: 'transparent'
  },
  colors: ['#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4'],
  labels: props.analytics?.leave_types?.most_used?.map(item => item.type) || [],
  series: props.analytics?.leave_types?.most_used?.map(item => item.count) || [],
  legend: {
    position: 'bottom'
  }
}));

const teamDistributionOptions = computed(() => ({
  chart: {
    type: 'bar',
    height: 350,
    background: 'transparent'
  },
  colors: ['#6366f1'],
  series: [{
    name: 'Employees',
    data: props.analytics?.employees?.by_team?.map(item => item.count) || []
  }],
  xaxis: {
    categories: props.analytics?.employees?.by_team?.map(item => item.team) || []
  }
}));

onMounted(() => {
  if (props.analytics) {
    // Leave Requests Trend Chart
    const requestsChart = new ApexCharts(
      document.querySelector('#leave-requests-chart'),
      leaveRequestsChartOptions.value
    );
    requestsChart.render();

    // Leave Types Distribution Chart
    const typesChart = new ApexCharts(
      document.querySelector('#leave-types-chart'),
      leaveTypesChartOptions.value
    );
    typesChart.render();

    // Team Distribution Chart
    const teamChart = new ApexCharts(
      document.querySelector('#team-distribution-chart'),
      teamDistributionOptions.value
    );
    teamChart.render();
  }
});
</script>

<template>
  <Head title="Company Analytics" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
      <!-- Header -->
      <div class="space-y-1">
        <h1 class="text-3xl font-bold tracking-tight">Company Analytics</h1>
        <p class="text-muted-foreground">
          Insights into your company's leave management and employee trends
        </p>
      </div>

      <!-- Key Metrics -->
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Employees</CardTitle>
            <UsersIcon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ props.analytics?.employees?.total || 0 }}</div>
            <p class="text-xs text-muted-foreground">
              {{ props.analytics?.employees?.on_leave_today || 0 }} on leave today
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Leave Requests</CardTitle>
            <CalendarIcon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ props.analytics?.leave_requests?.total || 0 }}</div>
            <p class="text-xs text-muted-foreground">
              {{ props.analytics?.leave_requests?.pending || 0 }} pending approval
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Approval Rate</CardTitle>
            <CheckCircleIcon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ props.analytics?.trends?.approval_rate || 0 }}%</div>
            <p class="text-xs text-muted-foreground">
              {{ props.analytics?.leave_requests?.approved || 0 }} approved this month
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Avg Duration</CardTitle>
            <ClockIcon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ props.analytics?.trends?.average_duration || 0 }} days</div>
            <p class="text-xs text-muted-foreground">
              Average leave duration
            </p>
          </CardContent>
        </Card>
      </div>

      <Tabs default-value="requests" class="w-full">
        <TabsList>
          <TabsTrigger value="requests">Leave Requests</TabsTrigger>
          <TabsTrigger value="types">Leave Types</TabsTrigger>
          <TabsTrigger value="teams">Team Analytics</TabsTrigger>
        </TabsList>

        <!-- Leave Requests Analytics -->
        <TabsContent value="requests" class="space-y-4">
          <div class="grid gap-4 md:grid-cols-2">
            <Card>
              <CardHeader>
                <CardTitle>Monthly Leave Requests Trend</CardTitle>
                <CardDescription>Leave requests over the past 12 months</CardDescription>
              </CardHeader>
              <CardContent>
                <div id="leave-requests-chart" class="w-full"></div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Request Status Breakdown</CardTitle>
                <CardDescription>Current status of all leave requests</CardDescription>
              </CardHeader>
              <CardContent>
                <div class="space-y-4">
                  <div class="flex items-center justify-between">
                    <span class="text-sm font-medium">Approved</span>
                    <div class="flex items-center space-x-2">
                      <div class="w-20 bg-gray-200 rounded-full h-2">
                        <div 
                          class="bg-green-500 h-2 rounded-full" 
                          :style="{ width: `${(props.analytics?.leave_requests?.approved / props.analytics?.leave_requests?.total) * 100 || 0}%` }"
                        ></div>
                      </div>
                      <span class="text-sm">{{ props.analytics?.leave_requests?.approved || 0 }}</span>
                    </div>
                  </div>

                  <div class="flex items-center justify-between">
                    <span class="text-sm font-medium">Pending</span>
                    <div class="flex items-center space-x-2">
                      <div class="w-20 bg-gray-200 rounded-full h-2">
                        <div 
                          class="bg-yellow-500 h-2 rounded-full" 
                          :style="{ width: `${(props.analytics?.leave_requests?.pending / props.analytics?.leave_requests?.total) * 100 || 0}%` }"
                        ></div>
                      </div>
                      <span class="text-sm">{{ props.analytics?.leave_requests?.pending || 0 }}</span>
                    </div>
                  </div>

                  <div class="flex items-center justify-between">
                    <span class="text-sm font-medium">Rejected</span>
                    <div class="flex items-center space-x-2">
                      <div class="w-20 bg-gray-200 rounded-full h-2">
                        <div 
                          class="bg-red-500 h-2 rounded-full" 
                          :style="{ width: `${(props.analytics?.leave_requests?.rejected / props.analytics?.leave_requests?.total) * 100 || 0}%` }"
                        ></div>
                      </div>
                      <span class="text-sm">{{ props.analytics?.leave_requests?.rejected || 0 }}</span>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
        </TabsContent>

        <!-- Leave Types Analytics -->
        <TabsContent value="types" class="space-y-4">
          <div class="grid gap-4 md:grid-cols-2">
            <Card>
              <CardHeader>
                <CardTitle>Most Used Leave Types</CardTitle>
                <CardDescription>Distribution of leave types used</CardDescription>
              </CardHeader>
              <CardContent>
                <div id="leave-types-chart" class="w-full"></div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Leave Type Usage</CardTitle>
                <CardDescription>Breakdown by popularity</CardDescription>
              </CardHeader>
              <CardContent>
                <div class="space-y-4">
                  <div 
                    v-for="leaveType in props.analytics?.leave_types?.most_used" 
                    :key="leaveType.type"
                    class="flex items-center justify-between"
                  >
                    <span class="font-medium">{{ leaveType.type }}</span>
                    <div class="flex items-center space-x-2">
                      <span class="text-sm text-muted-foreground">{{ leaveType.count }} requests</span>
                      <span class="text-sm font-medium">{{ leaveType.percentage }}%</span>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
        </TabsContent>

        <!-- Team Analytics -->
        <TabsContent value="teams" class="space-y-4">
          <div class="grid gap-4 md:grid-cols-2">
            <Card>
              <CardHeader>
                <CardTitle>Employees by Team</CardTitle>
                <CardDescription>Team size distribution</CardDescription>
              </CardHeader>
              <CardContent>
                <div id="team-distribution-chart" class="w-full"></div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Team Insights</CardTitle>
                <CardDescription>Key team metrics</CardDescription>
              </CardHeader>
              <CardContent>
                <div class="space-y-4">
                  <div class="flex items-center justify-between">
                    <span class="text-sm font-medium">Most Active Team</span>
                    <span class="text-sm font-bold">{{ props.analytics?.employees?.most_active_team || 'N/A' }}</span>
                  </div>

                  <div class="space-y-2">
                    <h4 class="font-semibold">Team Breakdown</h4>
                    <div 
                      v-for="team in props.analytics?.employees?.by_team" 
                      :key="team.team"
                      class="flex items-center justify-between text-sm"
                    >
                      <span>{{ team.team }}</span>
                      <span class="font-medium">{{ team.count }} employees</span>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
        </TabsContent>
      </Tabs>
    </div>
  </AppLayout>
</template>
