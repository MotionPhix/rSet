<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { toast } from 'vue-sonner';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import {
  Building2,
  Mail,
  Phone,
  Globe,
  MapPin,
  Users,
  CreditCard,
  Calendar,
  Shield
} from 'lucide-vue-next';
import { type SharedData } from '@/types';

const page = usePage<SharedData>();
const company = computed(() => page.props.auth.company);
const userAbilities = computed(() => page.props.auth.abilities || {});

const form = useForm({
  name: company.value?.name || '',
  email: company.value?.email || '',
  phone: company.value?.phone || '',
  website: company.value?.website || '',
  address: company.value?.address || '',
  timezone: company.value?.timezone || 'UTC',
  currency: company.value?.currency || 'USD',
  max_employees: company.value?.max_employees || 50,
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
  { value: 'Australia/Sydney', label: 'Sydney (AEST)' },
];

const currencies = [
  { value: 'USD', label: 'US Dollar (USD)' },
  { value: 'EUR', label: 'Euro (EUR)' },
  { value: 'GBP', label: 'British Pound (GBP)' },
  { value: 'JPY', label: 'Japanese Yen (JPY)' },
  { value: 'CAD', label: 'Canadian Dollar (CAD)' },
  { value: 'AUD', label: 'Australian Dollar (AUD)' },
];

const submit = () => {
  form.patch(route('admin.settings.company.update'), {
    preserveScroll: true,
    onSuccess: () => {
      toast.success('Company profile updated successfully');
    },
    onError: () => {
      toast.error('Failed to update company profile');
    },
  });
};

const getSubscriptionBadgeVariant = (plan: string) => {
  switch (plan) {
    case 'enterprise': return 'default';
    case 'premium': return 'secondary';
    case 'basic': return 'outline';
    default: return 'destructive';
  }
};

const isSubscriptionActive = computed(() => {
  if (!company.value?.subscription_expires_at) return false;
  return new Date(company.value.subscription_expires_at) > new Date();
});
</script>

<template>
  <Head title="Company Settings" />

  <SettingsLayout>
    <div class="space-y-6 p-6">
      <!-- Header -->
      <HeadingSmall
        title="Company Profile"
        description="Manage your company information and settings"
      />

      <!-- Company Overview -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Building2 class="h-5 w-5" />
            Company Overview
          </CardTitle>
          <CardDescription>
            Current company status and subscription details
          </CardDescription>
        </CardHeader>
        <CardContent class="space-y-4">
          <div class="grid gap-4 md:grid-cols-3">
            <div class="space-y-2">
              <div class="text-sm text-muted-foreground">Status</div>
              <Badge :variant="company?.is_active ? 'default' : 'destructive'">
                {{ company?.is_active ? 'Active' : 'Inactive' }}
              </Badge>
            </div>
            <div class="space-y-2">
              <div class="text-sm text-muted-foreground">Subscription Plan</div>
              <Badge :variant="getSubscriptionBadgeVariant(company?.subscription_plan || '')">
                {{ (company?.subscription_plan || 'free').charAt(0).toUpperCase() + (company?.subscription_plan || 'free').slice(1) }}
              </Badge>
            </div>
            <div class="space-y-2">
              <div class="text-sm text-muted-foreground">Employee Limit</div>
              <div class="text-lg font-semibold">{{ company?.max_employees || 0 }}</div>
            </div>
          </div>

          <Separator />

          <div class="grid gap-4 md:grid-cols-2">
            <div class="space-y-2">
              <div class="text-sm text-muted-foreground">Subscription Expires</div>
              <div class="flex items-center gap-2">
                <Calendar class="h-4 w-4" />
                <span class="text-sm">
                  {{ company?.subscription_expires_at ?
                    new Date(company.subscription_expires_at).toLocaleDateString() :
                    'No expiration'
                  }}
                </span>
                <Badge v-if="!isSubscriptionActive" variant="destructive" class="text-xs">
                  Expired
                </Badge>
              </div>
            </div>
            <div class="space-y-2">
              <div class="text-sm text-muted-foreground">Company ID</div>
              <div class="text-sm font-mono">{{ company?.id }}</div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Company Information Form -->
      <form @submit.prevent="submit">
        <Card>
          <CardHeader>
            <CardTitle>Company Information</CardTitle>
            <CardDescription>
              Update your company's basic information and contact details
            </CardDescription>
          </CardHeader>
          <CardContent class="space-y-6">
            <!-- Basic Information -->
            <div class="grid gap-4 md:grid-cols-2">
              <div class="space-y-2">
                <Label for="name">Company Name *</Label>
                <div class="relative">
                  <Building2 class="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
                  <Input
                    id="name"
                    v-model="form.name"
                    class="pl-10"
                    placeholder="Enter company name"
                    :disabled="!userAbilities.editCompanyProfile"
                    required
                  />
                </div>
                <div v-if="form.errors.name" class="text-sm text-destructive">
                  {{ form.errors.name }}
                </div>
              </div>

              <div class="space-y-2">
                <Label for="email">Company Email *</Label>
                <div class="relative">
                  <Mail class="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
                  <Input
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="pl-10"
                    placeholder="company@example.com"
                    :disabled="!userAbilities.editCompanyProfile"
                    required
                  />
                </div>
                <div v-if="form.errors.email" class="text-sm text-destructive">
                  {{ form.errors.email }}
                </div>
              </div>
            </div>

            <!-- Contact Information -->
            <div class="grid gap-4 md:grid-cols-2">
              <div class="space-y-2">
                <Label for="phone">Phone Number</Label>
                <div class="relative">
                  <Phone class="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
                  <Input
                    id="phone"
                    v-model="form.phone"
                    type="tel"
                    class="pl-10"
                    placeholder="+1 (555) 123-4567"
                    :disabled="!userAbilities.editCompanyProfile"
                  />
                </div>
                <div v-if="form.errors.phone" class="text-sm text-destructive">
                  {{ form.errors.phone }}
                </div>
              </div>

              <div class="space-y-2">
                <Label for="website">Website</Label>
                <div class="relative">
                  <Globe class="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
                  <Input
                    id="website"
                    v-model="form.website"
                    type="url"
                    class="pl-10"
                    placeholder="https://company.com"
                    :disabled="!userAbilities.editCompanyProfile"
                  />
                </div>
                <div v-if="form.errors.website" class="text-sm text-destructive">
                  {{ form.errors.website }}
                </div>
              </div>
            </div>

            <!-- Address -->
            <div class="space-y-2">
              <Label for="address">Company Address</Label>
              <div class="relative">
                <MapPin class="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
                <Textarea
                  id="address"
                  v-model="form.address"
                  class="pl-10"
                  placeholder="Enter company address"
                  :disabled="!userAbilities.editCompanyProfile"
                  rows="3"
                />
              </div>
              <div v-if="form.errors.address" class="text-sm text-destructive">
                {{ form.errors.address }}
              </div>
            </div>

            <!-- Settings -->
            <Separator />
            <div class="space-y-4">
              <h3 class="text-lg font-medium">Regional Settings</h3>

              <div class="grid gap-4 md:grid-cols-3">
                <div class="space-y-2">
                  <Label for="timezone">Timezone</Label>
                  <Select v-model="form.timezone" :disabled="!userAbilities.editCompanyProfile">
                    <SelectTrigger>
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
                  <div v-if="form.errors.timezone" class="text-sm text-destructive">
                    {{ form.errors.timezone }}
                  </div>
                </div>

                <div class="space-y-2">
                  <Label for="currency">Currency</Label>
                  <Select v-model="form.currency" :disabled="!userAbilities.editCompanyProfile">
                    <SelectTrigger>
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
                  <div v-if="form.errors.currency" class="text-sm text-destructive">
                    {{ form.errors.currency }}
                  </div>
                </div>

                <div class="space-y-2">
                  <Label for="max_employees">Employee Limit</Label>
                  <div class="relative">
                    <Users class="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
                    <Input
                      id="max_employees"
                      v-model="form.max_employees"
                      type="number"
                      class="pl-10"
                      min="1"
                      max="10000"
                      :disabled="!userAbilities.editCompanyProfile"
                    />
                  </div>
                  <div v-if="form.errors.max_employees" class="text-sm text-destructive">
                    {{ form.errors.max_employees }}
                  </div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Action Buttons -->
        <div class="flex justify-end gap-2 mt-6">
          <Button
            type="submit"
            :disabled="form.processing || !userAbilities.editCompanyProfile"
          >
            {{ form.processing ? 'Saving...' : 'Save Changes' }}
          </Button>
        </div>
      </form>

      <!-- Subscription Management -->
      <Card v-if="userAbilities.manageCompanySubscription">
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <CreditCard class="h-5 w-5" />
            Subscription Management
          </CardTitle>
          <CardDescription>
            Manage your subscription plan and billing
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="space-y-4">
            <div class="flex items-center justify-between p-4 border rounded-lg">
              <div class="space-y-1">
                <div class="font-medium">Current Plan: {{ (company?.subscription_plan || 'free').charAt(0).toUpperCase() + (company?.subscription_plan || 'free').slice(1) }}</div>
                <div class="text-sm text-muted-foreground">
                  {{ isSubscriptionActive ? 'Active subscription' : 'Subscription expired or inactive' }}
                </div>
              </div>
              <Button variant="outline">
                Manage Subscription
              </Button>
            </div>

            <div class="text-sm text-muted-foreground">
              <Shield class="inline h-4 w-4 mr-1" />
              Contact support for subscription changes or billing issues
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </SettingsLayout>
</template>
