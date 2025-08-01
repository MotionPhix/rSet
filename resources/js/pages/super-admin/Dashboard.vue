<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { 
  UsersIcon, 
  BuildingIcon, 
  TrendingUpIcon, 
  DollarSignIcon,
  ServerIcon,
  BarChart3Icon
} from 'lucide-vue-next';

interface Props {
  stats: {
    totalCompanies: number;
    totalUsers: number;
    activeSubscriptions: number;
    monthlyRevenue: number;
    systemHealth: {
      status: string;
      uptime: string;
      memoryUsage: number;
      diskUsage: number;
    };
    recentActivity: Array<{
      id: number;
      action: string;
      company: string;
      time: string;
      type: string;
    }>;
  };
}

const props = defineProps<Props>();

const page = usePage<SharedData>();

const breadcrumbs = computed((): BreadcrumbItem[] => [
  {
    title: 'Super Admin Dashboard',
    href: route('super-admin.dashboard')
  }
]);

const systemHealthColor = computed(() => {
  switch (props.stats?.systemHealth?.status) {
    case 'excellent': return 'text-green-600';
    case 'good': return 'text-blue-600';
    case 'warning': return 'text-yellow-600';
    case 'critical': return 'text-red-600';
    default: return 'text-gray-600';
  }
});
</script>

<template>
  <Head title="Super Admin Dashboard" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
      <!-- Welcome Section -->
      <div class="space-y-2">
        <h1 class="text-3xl font-bold tracking-tight">
          LeaveHub System Administration
        </h1>
        <p class="text-muted-foreground">
          Monitor and manage the entire LeaveHub platform
        </p>
      </div>

      <!-- Stats Cards -->
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <!-- Total Companies -->
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Companies</CardTitle>
            <BuildingIcon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ props.stats?.totalCompanies || 0 }}</div>
            <p class="text-xs text-muted-foreground">
              Active client companies
            </p>
          </CardContent>
        </Card>

        <!-- Total Users -->
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Users</CardTitle>
            <UsersIcon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ props.stats?.totalUsers || 0 }}</div>
            <p class="text-xs text-muted-foreground">
              Across all companies
            </p>
          </CardContent>
        </Card>

        <!-- Active Subscriptions -->
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Active Subscriptions</CardTitle>
            <TrendingUpIcon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ props.stats?.activeSubscriptions || 0 }}</div>
            <p class="text-xs text-muted-foreground">
              Paying customers
            </p>
          </CardContent>
        </Card>

        <!-- Monthly Revenue -->
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Monthly Revenue</CardTitle>
            <DollarSignIcon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">${{ props.stats?.monthlyRevenue || 0 }}</div>
            <p class="text-xs text-muted-foreground">
              This month's earnings
            </p>
          </CardContent>
        </Card>
      </div>

      <!-- System Health & Activity -->
      <div class="grid gap-4 md:grid-cols-2">
        <!-- System Health -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <ServerIcon class="h-5 w-5" />
              System Health
            </CardTitle>
            <CardDescription>Current system status and performance</CardDescription>
          </CardHeader>
          <CardContent>
            <div class="space-y-4" v-if="props.stats?.systemHealth">
              <div class="flex items-center justify-between">
                <span class="text-sm font-medium">Status</span>
                <span :class="systemHealthColor" class="text-sm font-semibold capitalize">
                  {{ props.stats.systemHealth.status }}
                </span>
              </div>
              
              <div class="flex items-center justify-between">
                <span class="text-sm font-medium">Uptime</span>
                <span class="text-sm">{{ props.stats.systemHealth.uptime }}</span>
              </div>
              
              <div class="space-y-2">
                <div class="flex items-center justify-between">
                  <span class="text-sm font-medium">Memory Usage</span>
                  <span class="text-sm">{{ props.stats.systemHealth.memoryUsage }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div 
                    class="bg-blue-600 h-2 rounded-full" 
                    :style="{ width: props.stats.systemHealth.memoryUsage + '%' }"
                  ></div>
                </div>
              </div>
              
              <div class="space-y-2">
                <div class="flex items-center justify-between">
                  <span class="text-sm font-medium">Disk Usage</span>
                  <span class="text-sm">{{ props.stats.systemHealth.diskUsage }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div 
                    class="bg-green-600 h-2 rounded-full" 
                    :style="{ width: props.stats.systemHealth.diskUsage + '%' }"
                  ></div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Recent Activity -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <BarChart3Icon class="h-5 w-5" />
              Recent Activity
            </CardTitle>
            <CardDescription>Latest system-wide activities</CardDescription>
          </CardHeader>
          <CardContent>
            <div class="space-y-4" v-if="props.stats?.recentActivity?.length">
              <div class="flex items-start space-x-3" v-for="activity in props.stats.recentActivity" :key="activity.id">
                <div class="w-2 h-2 rounded-full mt-2" :class="{
                  'bg-green-500': activity.type === 'subscription',
                  'bg-blue-500': activity.type === 'company',
                  'bg-yellow-500': activity.type === 'user',
                  'bg-red-500': activity.type === 'system'
                }"></div>
                <div class="flex-1 space-y-1">
                  <p class="text-sm font-medium">{{ activity.action }}</p>
                  <p class="text-xs text-muted-foreground">
                    {{ activity.company }} • {{ activity.time }}
                  </p>
                </div>
              </div>
            </div>
            <div v-else class="text-sm text-muted-foreground">
              No recent activity
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Quick Actions -->
      <Card>
        <CardHeader>
          <CardTitle>Quick Actions</CardTitle>
          <CardDescription>Common administrative tasks</CardDescription>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a
              :href="route('super-admin.companies.index')"
              class="flex flex-col items-center p-4 rounded-lg border hover:bg-accent transition-colors"
            >
              <BuildingIcon class="h-8 w-8 mb-2" />
              <span class="text-sm font-medium text-center">Manage Companies</span>
            </a>

            <a
              :href="route('super-admin.subscriptions.index')"
              class="flex flex-col items-center p-4 rounded-lg border hover:bg-accent transition-colors"
            >
              <DollarSignIcon class="h-8 w-8 mb-2" />
              <span class="text-sm font-medium text-center">Subscriptions</span>
            </a>

            <a
              :href="route('super-admin.analytics.index')"
              class="flex flex-col items-center p-4 rounded-lg border hover:bg-accent transition-colors"
            >
              <BarChart3Icon class="h-8 w-8 mb-2" />
              <span class="text-sm font-medium text-center">Analytics</span>
            </a>

            <a
              :href="route('super-admin.system.index')"
              class="flex flex-col items-center p-4 rounded-lg border hover:bg-accent transition-colors"
            >
              <ServerIcon class="h-8 w-8 mb-2" />
              <span class="text-sm font-medium text-center">System Settings</span>
            </a>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
