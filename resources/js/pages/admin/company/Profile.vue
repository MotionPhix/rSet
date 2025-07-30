<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, usePage, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { Building2Icon, SaveIcon, UsersIcon, CalendarIcon, SettingsIcon } from 'lucide-vue-next';

const page = usePage<SharedData>();

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Admin Dashboard',
    href: route('admin.dashboard')
  },
  {
    title: 'Company Profile',
    href: route('admin.company.profile')
  }
];

// Get company info
const company = computed(() => page.props.auth.company);

// Form for updating company
const form = useForm({
  name: company.value?.name || '',
  email: company.value?.email || '',
  phone: company.value?.phone || '',
  address: company.value?.address || '',
  website: company.value?.website || '',
  timezone: company.value?.timezone || 'UTC',
  currency: company.value?.currency || 'USD',
  max_employees: company.value?.max_employees || 50
});

const timezones = [
  { value: 'UTC', label: 'UTC' },
  { value: 'America/New_York', label: 'Eastern Time (ET)' },
  { value: 'America/Chicago', label: 'Central Time (CT)' },
  { value: 'America/Denver', label: 'Mountain Time (MT)' },
  { value: 'America/Los_Angeles', label: 'Pacific Time (PT)' },
  { value: 'Europe/London', label: 'London (GMT)' },
  { value: 'Europe/Paris', label: 'Paris (CET)' },
  { value: 'Asia/Tokyo', label: 'Tokyo (JST)' },
  { value: 'Asia/Shanghai', label: 'Shanghai (CST)' },
  { value: 'Australia/Sydney', label: 'Sydney (AEST)' }
];

const currencies = [
  { value: 'USD', label: 'US Dollar (USD)' },
  { value: 'EUR', label: 'Euro (EUR)' },
  { value: 'GBP', label: 'British Pound (GBP)' },
  { value: 'CAD', label: 'Canadian Dollar (CAD)' },
  { value: 'AUD', label: 'Australian Dollar (AUD)' },
  { value: 'JPY', label: 'Japanese Yen (JPY)' },
  { value: 'CNY', label: 'Chinese Yuan (CNY)' }
];

const submit = () => {
  form.put(route('company.update'));
};

// Mock stats - would come from backend
const stats = computed(() => ({
  totalEmployees: 45,
  totalTeams: 8,
  activeLeaveRequests: 12,
  totalLeaveTypes: 6
}));
</script>

<template>
  <Head title="Company Profile" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
      <!-- Header Section -->
      <div class="flex items-center justify-between max-w-4xl">
        <div class="space-y-2">
          <h1 class="text-3xl font-bold tracking-tight">
            Company Profile
          </h1>
          <p class="text-muted-foreground">
            Manage your company information and settings
          </p>
        </div>
        <div class="flex gap-2">
          <Button variant="outline" as-child>
            <a :href="route('admin.company.employees')">
              <UsersIcon class="h-4 w-4 mr-2" />
              Manage Employees
            </a>
          </Button>
        </div>
      </div>

      <!-- Company Stats -->
      <div class="grid gap-4 md:grid-cols-4 max-w-4xl">
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Employees</CardTitle>
            <UsersIcon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ stats.totalEmployees }}</div>
            <p class="text-xs text-muted-foreground">
              of {{ company?.max_employees }} max
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Teams</CardTitle>
            <Building2Icon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ stats.totalTeams }}</div>
            <p class="text-xs text-muted-foreground">
              Active teams
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Active Requests</CardTitle>
            <CalendarIcon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ stats.activeLeaveRequests }}</div>
            <p class="text-xs text-muted-foreground">
              Pending approval
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Leave Types</CardTitle>
            <SettingsIcon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ stats.totalLeaveTypes }}</div>
            <p class="text-xs text-muted-foreground">
              Configured types
            </p>
          </CardContent>
        </Card>
      </div>

      <!-- Company Information Form -->
      <section class="max-w-4xl">
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <Building2Icon class="h-5 w-5" />
              Company Information
            </CardTitle>
            <CardDescription>
              Update your company details and preferences
            </CardDescription>
          </CardHeader>
          <CardContent>
            <form @submit.prevent="submit" class="space-y-6">
              <!-- Basic Information -->
              <div class="space-y-4">
                <h3 class="text-lg font-semibold">Basic Information</h3>

                <div class="grid gap-4 md:grid-cols-2">
                  <div class="space-y-2">
                    <Label for="name">Company Name *</Label>
                    <Input
                      id="name"
                      v-model="form.name"
                      type="text"
                      required
                      :class="{ 'border-destructive': form.errors.name }"
                    />
                    <p v-if="form.errors.name" class="text-sm text-destructive">
                      {{ form.errors.name }}
                    </p>
                  </div>

                  <div class="space-y-2">
                    <Label for="email">Company Email</Label>
                    <Input
                      id="email"
                      v-model="form.email"
                      type="email"
                      :class="{ 'border-destructive': form.errors.email }"
                    />
                    <p v-if="form.errors.email" class="text-sm text-destructive">
                      {{ form.errors.email }}
                    </p>
                  </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                  <div class="space-y-2">
                    <Label for="phone">Phone Number</Label>
                    <Input
                      id="phone"
                      v-model="form.phone"
                      type="tel"
                      :class="{ 'border-destructive': form.errors.phone }"
                    />
                    <p v-if="form.errors.phone" class="text-sm text-destructive">
                      {{ form.errors.phone }}
                    </p>
                  </div>

                  <div class="space-y-2">
                    <Label for="website">Website</Label>
                    <Input
                      id="website"
                      v-model="form.website"
                      type="url"
                      :class="{ 'border-destructive': form.errors.website }"
                    />
                    <p v-if="form.errors.website" class="text-sm text-destructive">
                      {{ form.errors.website }}
                    </p>
                  </div>
                </div>

                <div class="space-y-2">
                  <Label for="address">Address</Label>
                  <Textarea
                    id="address"
                    v-model="form.address"
                    rows="3"
                    :class="{ 'border-destructive': form.errors.address }"
                  />
                  <p v-if="form.errors.address" class="text-sm text-destructive">
                    {{ form.errors.address }}
                  </p>
                </div>
              </div>

              <Separator />

              <!-- Preferences -->
              <div class="space-y-4">
                <h3 class="text-lg font-semibold">Preferences</h3>

                <div class="grid gap-4 md:grid-cols-3">
                  <div class="space-y-2">
                    <Label for="timezone">Timezone</Label>
                    <Select v-model="form.timezone">
                      <SelectTrigger class="w-full">
                        <SelectValue placeholder="Select timezone" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem
                          v-for="timezone in timezones"
                          :key="timezone.value"
                          :value="timezone.value"
                        >
                          {{ timezone.label }}
                        </SelectItem>
                      </SelectContent>
                    </Select>
                    <p v-if="form.errors.timezone" class="text-sm text-destructive">
                      {{ form.errors.timezone }}
                    </p>
                  </div>

                  <div class="space-y-2">
                    <Label for="currency">Currency</Label>
                    <Select v-model="form.currency">
                      <SelectTrigger class="w-full">
                        <SelectValue placeholder="Select currency" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem
                          v-for="currency in currencies"
                          :key="currency.value"
                          :value="currency.value"
                        >
                          {{ currency.label }}
                        </SelectItem>
                      </SelectContent>
                    </Select>
                    <p v-if="form.errors.currency" class="text-sm text-destructive">
                      {{ form.errors.currency }}
                    </p>
                  </div>

                  <div class="space-y-2">
                    <Label for="max_employees">Max Employees</Label>
                    <Input
                      id="max_employees"
                      v-model="form.max_employees"
                      type="number"
                      min="1"
                      max="1000"
                      :class="{ 'border-destructive': form.errors.max_employees }"
                    />
                    <p v-if="form.errors.max_employees" class="text-sm text-destructive">
                      {{ form.errors.max_employees }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Submit Button -->
              <div class="flex justify-end">
                <Button
                  type="submit"
                  :disabled="form.processing"
                >
                  <SaveIcon class="h-4 w-4 mr-2" />
                  {{ form.processing ? 'Saving...' : 'Save Changes' }}
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>
      </section>

      <!-- Subscription Information -->
      <section class="max-w-4xl">
        <Card>
          <CardHeader>
            <CardTitle>Subscription Information</CardTitle>
            <CardDescription>
              Current subscription details and limits
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div class="grid gap-4 md:grid-cols-3">
              <div class="text-center p-4 border rounded-lg">
                <div class="text-2xl font-bold text-blue-600">{{ company?.subscription_plan || 'Basic' }}</div>
                <p class="text-sm text-muted-foreground">Current Plan</p>
              </div>
              <div class="text-center p-4 border rounded-lg">
                <div class="text-2xl font-bold text-green-600">{{ company?.max_employees || 50 }}</div>
                <p class="text-sm text-muted-foreground">Employee Limit</p>
              </div>
              <div class="text-center p-4 border rounded-lg">
                <div class="text-2xl font-bold text-purple-600">
                  {{ company?.subscription_expires_at ? 'Active' : 'No Expiry' }}
                </div>
                <p class="text-sm text-muted-foreground">Status</p>
              </div>
            </div>
          </CardContent>
        </Card>
      </section>
    </div>
  </AppLayout>
</template>
