<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { 
  BarChart3Icon, 
  TrendingUpIcon, 
  DollarSignIcon,
  BuildingIcon,
  UsersIcon,
  CalendarIcon
} from 'lucide-vue-next';
import ApexCharts from 'apexcharts';

interface Props {
  analytics: {
    revenue: {
      monthly: Array<{ month: string; amount: number; }>;
      yearly: Array<{ year: string; amount: number; }>;
      total: number;
      growth_rate: number;
    };
    companies: {
      total: number;
      new_this_month: number;
      churn_this_month: number;
      by_plan: Array<{ plan: string; count: number; percentage: number; }>;
      growth_trend: Array<{ month: string; new: number; churned: number; }>;
    };
    users: {
      total: number;
      active_monthly: number;
      new_this_month: number;
      by_role: Array<{ role: string; count: number; }>;
    };
    usage: {
      leave_requests_total: number;
      leave_requests_this_month: number;
      most_used_leave_types: Array<{ type: string; count: number; }>;
      peak_months: Array<{ month: string; requests: number; }>;
    };
  };
}

const props = defineProps<Props>();

const breadcrumbs = computed((): BreadcrumbItem[] => [
  {
    title: 'Super Admin',
    href: route('super-admin.dashboard')
  },
  {
    title: 'Analytics',
    href: route('super-admin.analytics.index')
  }
]);

// Chart configurations
const revenueChartOptions = computed(() => ({
  chart: {
    type: 'line',
    height: 350,
    background: 'transparent'
  },
  colors: ['#10b981'],
  series: [{
    name: 'Revenue',
    data: props.analytics.revenue.monthly.map(item => item.amount)
  }],
  xaxis: {
    categories: props.analytics.revenue.monthly.map(item => item.month)
  },
  yaxis: {
    labels: {
      formatter: function(val: number) {
        return '$' + val.toLocaleString();
      }
    }
  },
  tooltip: {
    y: {
      formatter: function(val: number) {
        return '$' + val.toLocaleString();
      }
    }
  }
}));

const companiesGrowthOptions = computed(() => ({
  chart: {
    type: 'area',
    height: 350,
    background: 'transparent'
  },
  colors: ['#3b82f6', '#ef4444'],
  series: [{
    name: 'New Companies',
    data: props.analytics.companies.growth_trend.map(item => item.new)
  }, {
    name: 'Churned',
    data: props.analytics.companies.growth_trend.map(item => item.churned)
  }],
  xaxis: {
    categories: props.analytics.companies.growth_trend.map(item => item.month)
  }
}));

const planDistributionOptions = computed(() => ({
  chart: {
    type: 'donut',
    height: 300,
    background: 'transparent'
  },
  colors: ['#8b5cf6', '#3b82f6', '#f59e0b', '#10b981'],
  labels: props.analytics.companies.by_plan.map(item => item.plan),
  series: props.analytics.companies.by_plan.map(item => item.count),
  legend: {
    position: 'bottom'
  }
}));

onMounted(() => {
  if (props.analytics) {
    // Revenue Chart
    const revenueChart = new ApexCharts(
      document.querySelector('#revenue-chart'),
      revenueChartOptions.value
    );
    revenueChart.render();

    // Companies Growth Chart
    const companiesChart = new ApexCharts(
      document.querySelector('#companies-growth-chart'),
      companiesGrowthOptions.value
    );
    companiesChart.render();

    // Plan Distribution Chart
    const planChart = new ApexCharts(
      document.querySelector('#plan-distribution-chart'),
      planDistributionOptions.value
    );
    planChart.render();
  }
});
</script>

<template>
  <Head title="System Analytics" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
      <!-- Header -->
      <div class="space-y-1">
        <h1 class="text-3xl font-bold tracking-tight">System Analytics</h1>
        <p class="text-muted-foreground">
          Comprehensive insights into platform performance and usage
        </p>
      </div>

      <!-- Key Metrics -->
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Revenue</CardTitle>
            <DollarSignIcon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">${{ props.analytics?.revenue?.total?.toLocaleString() || 0 }}</div>
            <p class="text-xs text-muted-foreground">
              <span :class="props.analytics?.revenue?.growth_rate >= 0 ? 'text-green-600' : 'text-red-600'">
                {{ props.analytics?.revenue?.growth_rate >= 0 ? '+' : '' }}{{ props.analytics?.revenue?.growth_rate || 0 }}%
              </span>
              from last month
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Companies</CardTitle>
            <BuildingIcon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ props.analytics?.companies?.total || 0 }}</div>
            <p class="text-xs text-muted-foreground">
              +{{ props.analytics?.companies?.new_this_month || 0 }} new this month
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Users</CardTitle>
            <UsersIcon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ props.analytics?.users?.total || 0 }}</div>
            <p class="text-xs text-muted-foreground">
              {{ props.analytics?.users?.active_monthly || 0 }} active monthly
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Leave Requests</CardTitle>
            <CalendarIcon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ props.analytics?.usage?.leave_requests_total || 0 }}</div>
            <p class="text-xs text-muted-foreground">
              {{ props.analytics?.usage?.leave_requests_this_month || 0 }} this month
            </p>
          </CardContent>
        </Card>
      </div>

      <Tabs default-value="revenue" class="w-full">
        <TabsList>
          <TabsTrigger value="revenue">Revenue Analytics</TabsTrigger>
          <TabsTrigger value="companies">Companies Growth</TabsTrigger>
          <TabsTrigger value="usage">Platform Usage</TabsTrigger>
        </TabsList>

        <!-- Revenue Analytics -->
        <TabsContent value="revenue" class="space-y-4">
          <div class="grid gap-4 md:grid-cols-2">
            <Card>
              <CardHeader>
                <CardTitle>Monthly Revenue Trend</CardTitle>
                <CardDescription>Revenue growth over the past 12 months</CardDescription>
              </CardHeader>
              <CardContent>
                <div id="revenue-chart" class="w-full"></div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Revenue by Plan</CardTitle>
                <CardDescription>Subscription plan distribution</CardDescription>
              </CardHeader>
              <CardContent>
                <div id="plan-distribution-chart" class="w-full"></div>
              </CardContent>
            </Card>
          </div>
        </TabsContent>

        <!-- Companies Growth -->
        <TabsContent value="companies" class="space-y-4">
          <div class="grid gap-4 md:grid-cols-2">
            <Card>
              <CardHeader>
                <CardTitle>Company Growth Trend</CardTitle>
                <CardDescription>New companies vs churn rate</CardDescription>
              </CardHeader>
              <CardContent>
                <div id="companies-growth-chart" class="w-full"></div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Plan Distribution</CardTitle>
                <CardDescription>Companies by subscription plan</CardDescription>
              </CardHeader>
              <CardContent>
                <div class="space-y-4">
                  <div 
                    v-for="plan in props.analytics?.companies?.by_plan" 
                    :key="plan.plan"
                    class="flex items-center justify-between"
                  >
                    <span class="font-medium">{{ plan.plan }}</span>
                    <div class="flex items-center space-x-2">
                      <span class="text-sm text-muted-foreground">{{ plan.count }} companies</span>
                      <span class="text-sm font-medium">{{ plan.percentage }}%</span>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
        </TabsContent>

        <!-- Platform Usage -->
        <TabsContent value="usage" class="space-y-4">
          <div class="grid gap-4 md:grid-cols-2">
            <Card>
              <CardHeader>
                <CardTitle>User Distribution by Role</CardTitle>
                <CardDescription>Users across different roles</CardDescription>
              </CardHeader>
              <CardContent>
                <div class="space-y-4">
                  <div 
                    v-for="role in props.analytics?.users?.by_role" 
                    :key="role.role"
                    class="flex items-center justify-between"
                  >
                    <span class="font-medium capitalize">{{ role.role }}</span>
                    <span class="text-sm font-medium">{{ role.count }} users</span>
                  </div>
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Most Used Leave Types</CardTitle>
                <CardDescription>Popular leave types across all companies</CardDescription>
              </CardHeader>
              <CardContent>
                <div class="space-y-4">
                  <div 
                    v-for="leaveType in props.analytics?.usage?.most_used_leave_types" 
                    :key="leaveType.type"
                    class="flex items-center justify-between"
                  >
                    <span class="font-medium">{{ leaveType.type }}</span>
                    <span class="text-sm font-medium">{{ leaveType.count }} requests</span>
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
