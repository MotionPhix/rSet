<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Separator } from '@/components/ui/separator';
import { Badge } from '@/components/ui/badge';
import { 
  SettingsIcon, 
  DatabaseIcon, 
  ShieldIcon,
  MailIcon,
  CreditCardIcon,
  AlertTriangleIcon,
  CheckCircleIcon
} from 'lucide-vue-next';

interface Props {
  settings: {
    app: {
      name: string;
      url: string;
      timezone: string;
      debug: boolean;
      maintenance_mode: boolean;
    };
    database: {
      connection: string;
      size: string;
      backup_enabled: boolean;
      last_backup: string;
    };
    mail: {
      driver: string;
      host: string;
      port: string;
      from_address: string;
      from_name: string;
    };
    payment: {
      paychangu_secret_key: string;
      paychangu_public_key: string;
      webhook_secret: string;
      test_mode: boolean;
    };
    system: {
      max_companies: number;
      max_users_per_company: number;
      max_file_upload_size: string;
      session_lifetime: number;
    };
  };
  system_status: {
    storage_usage: {
      used: string;
      total: string;
      percentage: number;
    };
    cache_status: string;
    queue_status: string;
    database_status: string;
  };
}

const props = defineProps<Props>();

const breadcrumbs = computed((): BreadcrumbItem[] => [
  {
    title: 'Super Admin',
    href: route('super-admin.dashboard')
  },
  {
    title: 'System Settings',
    href: route('super-admin.system.settings')
  }
]);

// Forms for different setting sections
const appForm = useForm({
  name: props.settings?.app?.name || '',
  url: props.settings?.app?.url || '',
  timezone: props.settings?.app?.timezone || '',
  debug: props.settings?.app?.debug || false,
  maintenance_mode: props.settings?.app?.maintenance_mode || false,
});

const mailForm = useForm({
  driver: props.settings?.mail?.driver || '',
  host: props.settings?.mail?.host || '',
  port: props.settings?.mail?.port || '',
  from_address: props.settings?.mail?.from_address || '',
  from_name: props.settings?.mail?.from_name || '',
});

const paymentForm = useForm({
  paychangu_secret_key: props.settings?.payment?.paychangu_secret_key || '',
  paychangu_public_key: props.settings?.payment?.paychangu_public_key || '',
  webhook_secret: props.settings?.payment?.webhook_secret || '',
  test_mode: props.settings?.payment?.test_mode || false,
});

const systemForm = useForm({
  max_companies: props.settings?.system?.max_companies || 0,
  max_users_per_company: props.settings?.system?.max_users_per_company || 0,
  max_file_upload_size: props.settings?.system?.max_file_upload_size || '',
  session_lifetime: props.settings?.system?.session_lifetime || 0,
});

const activeTab = ref('app');

// Form submission handlers
const updateAppSettings = () => {
  appForm.put(route('super-admin.system.settings.update', { section: 'app' }), {
    preserveScroll: true,
  });
};

const updateMailSettings = () => {
  mailForm.put(route('super-admin.system.settings.update', { section: 'mail' }), {
    preserveScroll: true,
  });
};

const updatePaymentSettings = () => {
  paymentForm.put(route('super-admin.system.settings.update', { section: 'payment' }), {
    preserveScroll: true,
  });
};

const updateSystemSettings = () => {
  systemForm.put(route('super-admin.system.settings.update', { section: 'system' }), {
    preserveScroll: true,
  });
};

// System actions
const clearCache = () => {
  router.post(route('super-admin.system.clear-cache'), {}, {
    preserveScroll: true,
  });
};

const runBackup = () => {
  router.post(route('super-admin.system.backup'), {}, {
    preserveScroll: true,
  });
};

const getStatusBadge = (status: string) => {
  const statusMap: { [key: string]: { variant: 'default' | 'destructive' | 'outline' | 'secondary', label: string } } = {
    'online': { variant: 'default', label: 'Online' },
    'offline': { variant: 'destructive', label: 'Offline' },
    'connected': { variant: 'default', label: 'Connected' },
    'disconnected': { variant: 'destructive', label: 'Disconnected' },
    'active': { variant: 'default', label: 'Active' },
    'inactive': { variant: 'secondary', label: 'Inactive' },
  };
  
  return statusMap[status.toLowerCase()] || { variant: 'outline', label: status };
};
</script>

<template>
  <Head title="System Settings" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
      <!-- Header -->
      <div class="space-y-1">
        <h1 class="text-3xl font-bold tracking-tight">System Settings</h1>
        <p class="text-muted-foreground">
          Manage application configuration and system parameters
        </p>
      </div>

      <!-- System Status Overview -->
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Storage Usage</CardTitle>
            <DatabaseIcon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ props.system_status?.storage_usage?.percentage || 0 }}%</div>
            <p class="text-xs text-muted-foreground">
              {{ props.system_status?.storage_usage?.used || '0 GB' }} of {{ props.system_status?.storage_usage?.total || '0 GB' }}
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Cache Status</CardTitle>
            <CheckCircleIcon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <Badge :variant="getStatusBadge(props.system_status?.cache_status || 'offline').variant">
              {{ getStatusBadge(props.system_status?.cache_status || 'offline').label }}
            </Badge>
            <p class="text-xs text-muted-foreground mt-2">
              Application cache
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Queue Status</CardTitle>
            <AlertTriangleIcon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <Badge :variant="getStatusBadge(props.system_status?.queue_status || 'offline').variant">
              {{ getStatusBadge(props.system_status?.queue_status || 'offline').label }}
            </Badge>
            <p class="text-xs text-muted-foreground mt-2">
              Background jobs
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Database</CardTitle>
            <DatabaseIcon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <Badge :variant="getStatusBadge(props.system_status?.database_status || 'offline').variant">
              {{ getStatusBadge(props.system_status?.database_status || 'offline').label }}
            </Badge>
            <p class="text-xs text-muted-foreground mt-2">
              {{ props.settings?.database?.connection || 'Unknown' }}
            </p>
          </CardContent>
        </Card>
      </div>

      <Tabs v-model="activeTab" class="w-full">
        <TabsList class="grid w-full grid-cols-5">
          <TabsTrigger value="app">
            <SettingsIcon class="h-4 w-4 mr-2" />
            Application
          </TabsTrigger>
          <TabsTrigger value="mail">
            <MailIcon class="h-4 w-4 mr-2" />
            Email
          </TabsTrigger>
          <TabsTrigger value="payment">
            <CreditCardIcon class="h-4 w-4 mr-2" />
            Payment
          </TabsTrigger>
          <TabsTrigger value="system">
            <DatabaseIcon class="h-4 w-4 mr-2" />
            System
          </TabsTrigger>
          <TabsTrigger value="maintenance">
            <ShieldIcon class="h-4 w-4 mr-2" />
            Maintenance
          </TabsTrigger>
        </TabsList>

        <!-- Application Settings -->
        <TabsContent value="app" class="space-y-4">
          <Card>
            <CardHeader>
              <CardTitle>Application Configuration</CardTitle>
              <CardDescription>Basic application settings and environment configuration</CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
              <form @submit.prevent="updateAppSettings" class="space-y-4">
                <div class="grid gap-4 md:grid-cols-2">
                  <div class="space-y-2">
                    <Label for="app_name">Application Name</Label>
                    <Input 
                      id="app_name" 
                      v-model="appForm.name" 
                      :class="{ 'border-red-500': appForm.errors.name }"
                    />
                    <p v-if="appForm.errors.name" class="text-sm text-red-500">{{ appForm.errors.name }}</p>
                  </div>

                  <div class="space-y-2">
                    <Label for="app_url">Application URL</Label>
                    <Input 
                      id="app_url" 
                      v-model="appForm.url" 
                      :class="{ 'border-red-500': appForm.errors.url }"
                    />
                    <p v-if="appForm.errors.url" class="text-sm text-red-500">{{ appForm.errors.url }}</p>
                  </div>

                  <div class="space-y-2">
                    <Label for="timezone">Default Timezone</Label>
                    <Input 
                      id="timezone" 
                      v-model="appForm.timezone" 
                      :class="{ 'border-red-500': appForm.errors.timezone }"
                    />
                    <p v-if="appForm.errors.timezone" class="text-sm text-red-500">{{ appForm.errors.timezone }}</p>
                  </div>
                </div>

                <Separator />

                <div class="space-y-4">
                  <div class="flex items-center justify-between">
                    <div class="space-y-0.5">
                      <Label>Debug Mode</Label>
                      <p class="text-sm text-muted-foreground">Enable detailed error reporting</p>
                    </div>
                    <Switch v-model:checked="appForm.debug" />
                  </div>

                  <div class="flex items-center justify-between">
                    <div class="space-y-0.5">
                      <Label>Maintenance Mode</Label>
                      <p class="text-sm text-muted-foreground">Put application in maintenance mode</p>
                    </div>
                    <Switch v-model:checked="appForm.maintenance_mode" />
                  </div>
                </div>

                <Button type="submit" :disabled="appForm.processing">
                  {{ appForm.processing ? 'Updating...' : 'Update Application Settings' }}
                </Button>
              </form>
            </CardContent>
          </Card>
        </TabsContent>

        <!-- Email Settings -->
        <TabsContent value="mail" class="space-y-4">
          <Card>
            <CardHeader>
              <CardTitle>Email Configuration</CardTitle>
              <CardDescription>Configure SMTP settings for system emails</CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
              <form @submit.prevent="updateMailSettings" class="space-y-4">
                <div class="grid gap-4 md:grid-cols-2">
                  <div class="space-y-2">
                    <Label for="mail_driver">Mail Driver</Label>
                    <Input 
                      id="mail_driver" 
                      v-model="mailForm.driver" 
                      :class="{ 'border-red-500': mailForm.errors.driver }"
                    />
                    <p v-if="mailForm.errors.driver" class="text-sm text-red-500">{{ mailForm.errors.driver }}</p>
                  </div>

                  <div class="space-y-2">
                    <Label for="mail_host">SMTP Host</Label>
                    <Input 
                      id="mail_host" 
                      v-model="mailForm.host" 
                      :class="{ 'border-red-500': mailForm.errors.host }"
                    />
                    <p v-if="mailForm.errors.host" class="text-sm text-red-500">{{ mailForm.errors.host }}</p>
                  </div>

                  <div class="space-y-2">
                    <Label for="mail_port">SMTP Port</Label>
                    <Input 
                      id="mail_port" 
                      v-model="mailForm.port" 
                      :class="{ 'border-red-500': mailForm.errors.port }"
                    />
                    <p v-if="mailForm.errors.port" class="text-sm text-red-500">{{ mailForm.errors.port }}</p>
                  </div>

                  <div class="space-y-2">
                    <Label for="from_address">From Email Address</Label>
                    <Input 
                      id="from_address" 
                      v-model="mailForm.from_address" 
                      type="email"
                      :class="{ 'border-red-500': mailForm.errors.from_address }"
                    />
                    <p v-if="mailForm.errors.from_address" class="text-sm text-red-500">{{ mailForm.errors.from_address }}</p>
                  </div>

                  <div class="space-y-2">
                    <Label for="from_name">From Name</Label>
                    <Input 
                      id="from_name" 
                      v-model="mailForm.from_name" 
                      :class="{ 'border-red-500': mailForm.errors.from_name }"
                    />
                    <p v-if="mailForm.errors.from_name" class="text-sm text-red-500">{{ mailForm.errors.from_name }}</p>
                  </div>
                </div>

                <Button type="submit" :disabled="mailForm.processing">
                  {{ mailForm.processing ? 'Updating...' : 'Update Email Settings' }}
                </Button>
              </form>
            </CardContent>
          </Card>
        </TabsContent>

        <!-- Payment Settings -->
        <TabsContent value="payment" class="space-y-4">
          <Card>
            <CardHeader>
              <CardTitle>PayChangu Configuration</CardTitle>
              <CardDescription>Configure PayChangu payment gateway settings</CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
              <form @submit.prevent="updatePaymentSettings" class="space-y-4">
                <div class="space-y-4">
                  <div class="space-y-2">
                    <Label for="paychangu_public_key">PayChangu Public Key</Label>
                    <Input 
                      id="paychangu_public_key" 
                      v-model="paymentForm.paychangu_public_key" 
                      :class="{ 'border-red-500': paymentForm.errors.paychangu_public_key }"
                    />
                    <p v-if="paymentForm.errors.paychangu_public_key" class="text-sm text-red-500">{{ paymentForm.errors.paychangu_public_key }}</p>
                  </div>

                  <div class="space-y-2">
                    <Label for="paychangu_secret_key">PayChangu Secret Key</Label>
                    <Input 
                      id="paychangu_secret_key" 
                      v-model="paymentForm.paychangu_secret_key" 
                      type="password"
                      :class="{ 'border-red-500': paymentForm.errors.paychangu_secret_key }"
                    />
                    <p v-if="paymentForm.errors.paychangu_secret_key" class="text-sm text-red-500">{{ paymentForm.errors.paychangu_secret_key }}</p>
                  </div>

                  <div class="space-y-2">
                    <Label for="webhook_secret">Webhook Secret</Label>
                    <Input 
                      id="webhook_secret" 
                      v-model="paymentForm.webhook_secret" 
                      type="password"
                      :class="{ 'border-red-500': paymentForm.errors.webhook_secret }"
                    />
                    <p v-if="paymentForm.errors.webhook_secret" class="text-sm text-red-500">{{ paymentForm.errors.webhook_secret }}</p>
                  </div>

                  <div class="flex items-center justify-between">
                    <div class="space-y-0.5">
                      <Label>Test Mode</Label>
                      <p class="text-sm text-muted-foreground">Enable test/sandbox mode for PayChangu</p>
                    </div>
                    <Switch v-model:checked="paymentForm.test_mode" />
                  </div>
                </div>

                <Button type="submit" :disabled="paymentForm.processing">
                  {{ paymentForm.processing ? 'Updating...' : 'Update Payment Settings' }}
                </Button>
              </form>
            </CardContent>
          </Card>
        </TabsContent>

        <!-- System Limits -->
        <TabsContent value="system" class="space-y-4">
          <Card>
            <CardHeader>
              <CardTitle>System Limits</CardTitle>
              <CardDescription>Configure system-wide limits and constraints</CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
              <form @submit.prevent="updateSystemSettings" class="space-y-4">
                <div class="grid gap-4 md:grid-cols-2">
                  <div class="space-y-2">
                    <Label for="max_companies">Maximum Companies</Label>
                    <Input 
                      id="max_companies" 
                      v-model.number="systemForm.max_companies" 
                      type="number"
                      :class="{ 'border-red-500': systemForm.errors.max_companies }"
                    />
                    <p v-if="systemForm.errors.max_companies" class="text-sm text-red-500">{{ systemForm.errors.max_companies }}</p>
                  </div>

                  <div class="space-y-2">
                    <Label for="max_users_per_company">Max Users per Company</Label>
                    <Input 
                      id="max_users_per_company" 
                      v-model.number="systemForm.max_users_per_company" 
                      type="number"
                      :class="{ 'border-red-500': systemForm.errors.max_users_per_company }"
                    />
                    <p v-if="systemForm.errors.max_users_per_company" class="text-sm text-red-500">{{ systemForm.errors.max_users_per_company }}</p>
                  </div>

                  <div class="space-y-2">
                    <Label for="max_file_upload_size">Max File Upload Size</Label>
                    <Input 
                      id="max_file_upload_size" 
                      v-model="systemForm.max_file_upload_size" 
                      placeholder="e.g., 10MB"
                      :class="{ 'border-red-500': systemForm.errors.max_file_upload_size }"
                    />
                    <p v-if="systemForm.errors.max_file_upload_size" class="text-sm text-red-500">{{ systemForm.errors.max_file_upload_size }}</p>
                  </div>

                  <div class="space-y-2">
                    <Label for="session_lifetime">Session Lifetime (minutes)</Label>
                    <Input 
                      id="session_lifetime" 
                      v-model.number="systemForm.session_lifetime" 
                      type="number"
                      :class="{ 'border-red-500': systemForm.errors.session_lifetime }"
                    />
                    <p v-if="systemForm.errors.session_lifetime" class="text-sm text-red-500">{{ systemForm.errors.session_lifetime }}</p>
                  </div>
                </div>

                <Button type="submit" :disabled="systemForm.processing">
                  {{ systemForm.processing ? 'Updating...' : 'Update System Settings' }}
                </Button>
              </form>
            </CardContent>
          </Card>
        </TabsContent>

        <!-- Maintenance -->
        <TabsContent value="maintenance" class="space-y-4">
          <div class="grid gap-4 md:grid-cols-2">
            <Card>
              <CardHeader>
                <CardTitle>System Maintenance</CardTitle>
                <CardDescription>Perform system maintenance tasks</CardDescription>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="space-y-4">
                  <div>
                    <h4 class="font-semibold mb-2">Clear Application Cache</h4>
                    <p class="text-sm text-muted-foreground mb-4">
                      Clear all cached data to improve performance or resolve issues.
                    </p>
                    <Button variant="outline" @click="clearCache">
                      Clear Cache
                    </Button>
                  </div>

                  <Separator />

                  <div>
                    <h4 class="font-semibold mb-2">Database Backup</h4>
                    <p class="text-sm text-muted-foreground mb-4">
                      Create a backup of the database. Last backup: {{ props.settings?.database?.last_backup || 'Never' }}
                    </p>
                    <Button variant="outline" @click="runBackup">
                      Create Backup
                    </Button>
                  </div>
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Database Information</CardTitle>
                <CardDescription>Current database configuration and status</CardDescription>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="space-y-2">
                  <div class="flex justify-between">
                    <span class="text-sm font-medium">Connection:</span>
                    <span class="text-sm">{{ props.settings?.database?.connection || 'Unknown' }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-sm font-medium">Size:</span>
                    <span class="text-sm">{{ props.settings?.database?.size || 'Unknown' }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-sm font-medium">Backup Enabled:</span>
                    <Badge :variant="props.settings?.database?.backup_enabled ? 'default' : 'secondary'">
                      {{ props.settings?.database?.backup_enabled ? 'Yes' : 'No' }}
                    </Badge>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-sm font-medium">Last Backup:</span>
                    <span class="text-sm">{{ props.settings?.database?.last_backup || 'Never' }}</span>
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
