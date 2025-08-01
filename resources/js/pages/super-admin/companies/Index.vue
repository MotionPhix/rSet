<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { 
  BuildingIcon, 
  UsersIcon, 
  CalendarIcon,
  DollarSignIcon,
  MoreHorizontalIcon,
  EyeIcon,
  EditIcon,
  TrashIcon
} from 'lucide-vue-next';

interface Company {
  id: number;
  name: string;
  email: string;
  phone: string;
  website: string;
  employees_count: number;
  subscription_plan: string;
  subscription_status: 'active' | 'expired' | 'cancelled';
  subscription_expires_at: string;
  created_at: string;
  monthly_revenue: number;
  leave_requests_count: number;
}

interface Props {
  companies: {
    data: Company[];
    links: any[];
    meta: any;
  };
  stats: {
    total: number;
    active: number;
    expired: number;
    total_revenue: number;
  };
}

const props = defineProps<Props>();

const breadcrumbs = computed((): BreadcrumbItem[] => [
  {
    title: 'Super Admin',
    href: route('super-admin.dashboard')
  },
  {
    title: 'Companies',
    href: route('super-admin.companies.index')
  }
]);

const getStatusColor = (status: string) => {
  switch (status) {
    case 'active': return 'bg-green-100 text-green-800';
    case 'expired': return 'bg-red-100 text-red-800';
    case 'cancelled': return 'bg-gray-100 text-gray-800';
    default: return 'bg-gray-100 text-gray-800';
  }
};

const getPlanColor = (plan: string) => {
  switch (plan.toLowerCase()) {
    case 'premium': return 'bg-purple-100 text-purple-800';
    case 'business': return 'bg-blue-100 text-blue-800';
    case 'basic': return 'bg-yellow-100 text-yellow-800';
    case 'free': return 'bg-gray-100 text-gray-800';
    default: return 'bg-gray-100 text-gray-800';
  }
};
</script>

<template>
  <Head title="Companies Management" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div class="space-y-1">
          <h1 class="text-3xl font-bold tracking-tight">Companies Management</h1>
          <p class="text-muted-foreground">
            Manage all client companies and their subscriptions
          </p>
        </div>
        <Button>
          <BuildingIcon class="h-4 w-4 mr-2" />
          Add Company
        </Button>
      </div>

      <!-- Stats Cards -->
      <div class="grid gap-4 md:grid-cols-4">
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Companies</CardTitle>
            <BuildingIcon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ props.stats?.total || 0 }}</div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Active Subscriptions</CardTitle>
            <CalendarIcon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ props.stats?.active || 0 }}</div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Expired</CardTitle>
            <CalendarIcon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ props.stats?.expired || 0 }}</div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Revenue</CardTitle>
            <DollarSignIcon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">${{ props.stats?.total_revenue || 0 }}</div>
          </CardContent>
        </Card>
      </div>

      <!-- Companies Table -->
      <Card>
        <CardHeader>
          <CardTitle>All Companies</CardTitle>
          <CardDescription>
            Manage client companies, subscriptions, and billing
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="space-y-4">
            <div 
              v-for="company in props.companies.data" 
              :key="company.id"
              class="flex items-center justify-between p-4 border rounded-lg hover:bg-accent/50 transition-colors"
            >
              <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                  <BuildingIcon class="h-6 w-6 text-primary" />
                </div>
                
                <div class="space-y-1">
                  <div class="flex items-center space-x-2">
                    <h3 class="font-semibold">{{ company.name }}</h3>
                    <Badge :class="getStatusColor(company.subscription_status)">
                      {{ company.subscription_status }}
                    </Badge>
                    <Badge :class="getPlanColor(company.subscription_plan)">
                      {{ company.subscription_plan }}
                    </Badge>
                  </div>
                  
                  <div class="flex items-center space-x-4 text-sm text-muted-foreground">
                    <span class="flex items-center">
                      <UsersIcon class="h-3 w-3 mr-1" />
                      {{ company.employees_count }} employees
                    </span>
                    <span class="flex items-center">
                      <CalendarIcon class="h-3 w-3 mr-1" />
                      {{ company.leave_requests_count }} requests
                    </span>
                    <span class="flex items-center">
                      <DollarSignIcon class="h-3 w-3 mr-1" />
                      ${{ company.monthly_revenue }}/mo
                    </span>
                  </div>
                  
                  <div class="text-xs text-muted-foreground">
                    {{ company.email }} • {{ company.website }}
                  </div>
                </div>
              </div>

              <div class="flex items-center space-x-2">
                <Button variant="ghost" size="sm" :href="route('super-admin.companies.show', company.id)">
                  <EyeIcon class="h-4 w-4" />
                </Button>
                
                <Button variant="ghost" size="sm">
                  <EditIcon class="h-4 w-4" />
                </Button>
                
                <Button variant="ghost" size="sm">
                  <MoreHorizontalIcon class="h-4 w-4" />
                </Button>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="props.companies.links" class="flex items-center justify-center mt-6">
            <div class="flex space-x-1">
              <template v-for="link in props.companies.links" :key="link.label">
                <Button
                  v-if="link.url"
                  variant="ghost"
                  size="sm"
                  :href="link.url"
                  :class="{ 'bg-primary text-primary-foreground': link.active }"
                  v-html="link.label"
                />
                <span v-else class="px-3 py-1 text-sm text-muted-foreground" v-html="link.label" />
              </template>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
