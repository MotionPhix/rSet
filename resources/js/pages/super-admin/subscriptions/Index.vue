<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { 
  CreditCardIcon, 
  DollarSignIcon, 
  TrendingUpIcon,
  CalendarIcon,
  BuildingIcon,
  UsersIcon,
  PlusIcon
} from 'lucide-vue-next';

interface Subscription {
  id: number;
  company: {
    id: number;
    name: string;
    email: string;
  };
  plan: string;
  status: 'active' | 'expired' | 'cancelled' | 'pending';
  amount: number;
  currency: string;
  started_at: string;
  expires_at: string;
  next_billing_date: string;
  payment_method: string;
}

interface Plan {
  id: number;
  name: string;
  description: string;
  price: number;
  currency: string;
  billing_cycle: 'monthly' | 'yearly';
  max_employees: number;
  features: string[];
  is_active: boolean;
  subscriptions_count: number;
}

interface Props {
  subscriptions: {
    data: Subscription[];
    links: any[];
    meta: any;
  };
  plans: Plan[];
  stats: {
    total_subscriptions: number;
    active_subscriptions: number;
    monthly_revenue: number;
    churn_rate: number;
  };
}

const props = defineProps<Props>();

const breadcrumbs = computed((): BreadcrumbItem[] => [
  {
    title: 'Super Admin',
    href: route('super-admin.dashboard')
  },
  {
    title: 'Subscriptions',
    href: route('super-admin.subscriptions.index')
  }
]);

const getStatusColor = (status: string) => {
  switch (status) {
    case 'active': return 'bg-green-100 text-green-800';
    case 'expired': return 'bg-red-100 text-red-800';
    case 'cancelled': return 'bg-gray-100 text-gray-800';
    case 'pending': return 'bg-yellow-100 text-yellow-800';
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
  <Head title="Subscriptions Management" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div class="space-y-1">
          <h1 class="text-3xl font-bold tracking-tight">Subscriptions Management</h1>
          <p class="text-muted-foreground">
            Manage subscription plans and billing for all companies
          </p>
        </div>
        <div class="flex space-x-2">
          <Button variant="outline" :href="route('super-admin.subscriptions.plans')">
            Manage Plans
          </Button>
          <Button>
            <PlusIcon class="h-4 w-4 mr-2" />
            Add Subscription
          </Button>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid gap-4 md:grid-cols-4">
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Subscriptions</CardTitle>
            <CreditCardIcon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ props.stats?.total_subscriptions || 0 }}</div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Active Subscriptions</CardTitle>
            <TrendingUpIcon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ props.stats?.active_subscriptions || 0 }}</div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Monthly Revenue</CardTitle>
            <DollarSignIcon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">${{ props.stats?.monthly_revenue || 0 }}</div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Churn Rate</CardTitle>
            <TrendingUpIcon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ props.stats?.churn_rate || 0 }}%</div>
          </CardContent>
        </Card>
      </div>

      <Tabs default-value="subscriptions" class="w-full">
        <TabsList>
          <TabsTrigger value="subscriptions">Active Subscriptions</TabsTrigger>
          <TabsTrigger value="plans">Subscription Plans</TabsTrigger>
        </TabsList>

        <!-- Subscriptions Tab -->
        <TabsContent value="subscriptions" class="space-y-4">
          <Card>
            <CardHeader>
              <CardTitle>Current Subscriptions</CardTitle>
              <CardDescription>
                All active and inactive subscriptions across companies
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div class="space-y-4">
                <div 
                  v-for="subscription in props.subscriptions.data" 
                  :key="subscription.id"
                  class="flex items-center justify-between p-4 border rounded-lg hover:bg-accent/50 transition-colors"
                >
                  <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                      <BuildingIcon class="h-6 w-6 text-primary" />
                    </div>
                    
                    <div class="space-y-1">
                      <div class="flex items-center space-x-2">
                        <h3 class="font-semibold">{{ subscription.company.name }}</h3>
                        <Badge :class="getStatusColor(subscription.status)">
                          {{ subscription.status }}
                        </Badge>
                        <Badge :class="getPlanColor(subscription.plan)">
                          {{ subscription.plan }}
                        </Badge>
                      </div>
                      
                      <div class="flex items-center space-x-4 text-sm text-muted-foreground">
                        <span class="flex items-center">
                          <DollarSignIcon class="h-3 w-3 mr-1" />
                          {{ subscription.currency }} {{ subscription.amount }}/month
                        </span>
                        <span class="flex items-center">
                          <CalendarIcon class="h-3 w-3 mr-1" />
                          Expires: {{ new Date(subscription.expires_at).toLocaleDateString() }}
                        </span>
                        <span>{{ subscription.payment_method }}</span>
                      </div>
                      
                      <div class="text-xs text-muted-foreground">
                        {{ subscription.company.email }}
                      </div>
                    </div>
                  </div>

                  <div class="flex items-center space-x-2">
                    <Button variant="ghost" size="sm">
                      View Details
                    </Button>
                    <Button variant="ghost" size="sm">
                      Edit
                    </Button>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        <!-- Plans Tab -->
        <TabsContent value="plans" class="space-y-4">
          <div class="grid gap-4 md:grid-cols-3">
            <div 
              v-for="plan in props.plans" 
              :key="plan.id"
              class="border rounded-lg p-6 hover:shadow-lg transition-shadow"
            >
              <div class="space-y-4">
                <div class="flex items-center justify-between">
                  <h3 class="text-lg font-semibold">{{ plan.name }}</h3>
                  <Badge v-if="!plan.is_active" variant="secondary">Inactive</Badge>
                </div>
                
                <p class="text-sm text-muted-foreground">{{ plan.description }}</p>
                
                <div class="text-3xl font-bold">
                  {{ plan.currency }} {{ plan.price }}
                  <span class="text-sm font-normal text-muted-foreground">
                    /{{ plan.billing_cycle }}
                  </span>
                </div>
                
                <div class="space-y-2">
                  <div class="flex items-center text-sm">
                    <UsersIcon class="h-4 w-4 mr-2" />
                    Up to {{ plan.max_employees }} employees
                  </div>
                  <div class="text-sm text-muted-foreground">
                    {{ plan.subscriptions_count }} active subscriptions
                  </div>
                </div>
                
                <ul class="space-y-1 text-sm">
                  <li v-for="feature in plan.features" :key="feature">
                    • {{ feature }}
                  </li>
                </ul>
                
                <div class="flex space-x-2">
                  <Button variant="outline" size="sm" class="flex-1">
                    Edit Plan
                  </Button>
                  <Button 
                    variant="ghost" 
                    size="sm"
                    :class="plan.is_active ? 'text-red-600' : 'text-green-600'"
                  >
                    {{ plan.is_active ? 'Deactivate' : 'Activate' }}
                  </Button>
                </div>
              </div>
            </div>
          </div>
        </TabsContent>
      </Tabs>
    </div>
  </AppLayout>
</template>
