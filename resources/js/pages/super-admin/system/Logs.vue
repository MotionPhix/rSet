<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { 
  FileTextIcon, 
  AlertTriangleIcon,
  InfoIcon,
  XCircleIcon,
  CheckCircleIcon,
  RefreshCwIcon,
  DownloadIcon,
  SearchIcon
} from 'lucide-vue-next';

interface Props {
  logs: {
    data: Array<{
      id: string;
      timestamp: string;
      level: 'info' | 'warning' | 'error' | 'debug' | 'critical';
      message: string;
      context: string;
      user_id?: number;
      user_name?: string;
      ip_address: string;
      user_agent: string;
    }>;
    meta: {
      current_page: number;
      last_page: number;
      per_page: number;
      total: number;
    };
  };
  filters: {
    level?: string;
    search?: string;
    date_from?: string;
    date_to?: string;
  };
  log_stats: {
    total_logs: number;
    error_count: number;
    warning_count: number;
    info_count: number;
    last_error?: string;
  };
}

const props = defineProps<Props>();

const breadcrumbs = computed((): BreadcrumbItem[] => [
  {
    title: 'Super Admin',
    href: route('super-admin.dashboard')
  },
  {
    title: 'System Logs',
    href: route('super-admin.system.logs')
  }
]);

// Filter state
const filters = ref({
  level: props.filters?.level || '',
  search: props.filters?.search || '',
  date_from: props.filters?.date_from || '',
  date_to: props.filters?.date_to || '',
});

// Apply filters
const applyFilters = () => {
  router.get(route('super-admin.system.logs'), filters.value, {
    preserveState: true,
    preserveScroll: true,
  });
};

// Clear filters
const clearFilters = () => {
  filters.value = {
    level: '',
    search: '',
    date_from: '',
    date_to: '',
  };
  applyFilters();
};

// Download logs
const downloadLogs = () => {
  window.open(route('super-admin.system.logs.download', filters.value));
};

// Get level badge variant
const getLevelBadge = (level: string) => {
  const levelMap: { [key: string]: { variant: 'default' | 'destructive' | 'outline' | 'secondary', icon: any } } = {
    'info': { variant: 'default', icon: InfoIcon },
    'warning': { variant: 'outline', icon: AlertTriangleIcon },
    'error': { variant: 'destructive', icon: XCircleIcon },
    'debug': { variant: 'secondary', icon: FileTextIcon },
    'critical': { variant: 'destructive', icon: AlertTriangleIcon },
  };
  
  return levelMap[level.toLowerCase()] || { variant: 'outline', icon: InfoIcon };
};

// Format timestamp
const formatTimestamp = (timestamp: string) => {
  return new Date(timestamp).toLocaleString();
};

// Truncate text
const truncateText = (text: string, length: number = 100) => {
  return text.length > length ? text.substring(0, length) + '...' : text;
};
</script>

<template>
  <Head title="System Logs" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
      <!-- Header -->
      <div class="space-y-1">
        <h1 class="text-3xl font-bold tracking-tight">System Logs</h1>
        <p class="text-muted-foreground">
          Monitor system activities and troubleshoot issues
        </p>
      </div>

      <!-- Log Statistics -->
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Logs</CardTitle>
            <FileTextIcon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ props.log_stats?.total_logs?.toLocaleString() || 0 }}</div>
            <p class="text-xs text-muted-foreground">
              All system events
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Errors</CardTitle>
            <XCircleIcon class="h-4 w-4 text-red-500" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-red-600">{{ props.log_stats?.error_count || 0 }}</div>
            <p class="text-xs text-muted-foreground">
              Critical issues
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Warnings</CardTitle>
            <AlertTriangleIcon class="h-4 w-4 text-yellow-500" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-yellow-600">{{ props.log_stats?.warning_count || 0 }}</div>
            <p class="text-xs text-muted-foreground">
              Potential issues
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Info Logs</CardTitle>
            <CheckCircleIcon class="h-4 w-4 text-green-500" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-green-600">{{ props.log_stats?.info_count || 0 }}</div>
            <p class="text-xs text-muted-foreground">
              Normal activities
            </p>
          </CardContent>
        </Card>
      </div>

      <!-- Filters -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <SearchIcon class="h-5 w-5" />
            Filters
          </CardTitle>
          <CardDescription>Filter and search through system logs</CardDescription>
        </CardHeader>
        <CardContent class="space-y-4">
          <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
            <div class="space-y-2">
              <Label for="search">Search</Label>
              <Input 
                id="search"
                v-model="filters.search" 
                placeholder="Search message content..."
                @keyup.enter="applyFilters"
              />
            </div>

            <div class="space-y-2">
              <Label for="level">Log Level</Label>
              <Select v-model="filters.level">
                <SelectTrigger>
                  <SelectValue placeholder="All levels" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="">All levels</SelectItem>
                  <SelectItem value="info">Info</SelectItem>
                  <SelectItem value="warning">Warning</SelectItem>
                  <SelectItem value="error">Error</SelectItem>
                  <SelectItem value="debug">Debug</SelectItem>
                  <SelectItem value="critical">Critical</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div class="space-y-2">
              <Label for="date_from">From Date</Label>
              <Input 
                id="date_from"
                v-model="filters.date_from" 
                type="date"
              />
            </div>

            <div class="space-y-2">
              <Label for="date_to">To Date</Label>
              <Input 
                id="date_to"
                v-model="filters.date_to" 
                type="date"
              />
            </div>

            <div class="flex items-end space-x-2">
              <Button @click="applyFilters" class="flex-1">
                <SearchIcon class="h-4 w-4 mr-2" />
                Search
              </Button>
              <Button variant="outline" @click="clearFilters">
                Clear
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Logs Table -->
      <Card>
        <CardHeader>
          <div class="flex items-center justify-between">
            <div>
              <CardTitle>System Logs</CardTitle>
              <CardDescription>
                Showing {{ props.logs?.data?.length || 0 }} of {{ props.logs?.meta?.total || 0 }} logs
              </CardDescription>
            </div>
            <div class="flex items-center space-x-2">
              <Button variant="outline" size="sm" @click="applyFilters">
                <RefreshCwIcon class="h-4 w-4 mr-2" />
                Refresh
              </Button>
              <Button variant="outline" size="sm" @click="downloadLogs">
                <DownloadIcon class="h-4 w-4 mr-2" />
                Download
              </Button>
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <div class="rounded-md border">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead class="w-[140px]">Timestamp</TableHead>
                  <TableHead class="w-[100px]">Level</TableHead>
                  <TableHead>Message</TableHead>
                  <TableHead class="w-[150px]">User</TableHead>
                  <TableHead class="w-[120px]">IP Address</TableHead>
                  <TableHead class="w-[100px]">Context</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-if="!props.logs?.data?.length">
                  <TableCell colspan="6" class="text-center text-muted-foreground py-8">
                    No logs found matching your criteria
                  </TableCell>
                </TableRow>
                <TableRow 
                  v-for="log in props.logs?.data" 
                  :key="log.id"
                  class="hover:bg-muted/50"
                >
                  <TableCell class="font-mono text-xs">
                    {{ formatTimestamp(log.timestamp) }}
                  </TableCell>
                  <TableCell>
                    <Badge :variant="getLevelBadge(log.level).variant" class="flex items-center gap-1">
                      <component :is="getLevelBadge(log.level).icon" class="h-3 w-3" />
                      {{ log.level.toUpperCase() }}
                    </Badge>
                  </TableCell>
                  <TableCell>
                    <div class="max-w-md">
                      <p class="text-sm" :title="log.message">
                        {{ truncateText(log.message, 80) }}
                      </p>
                    </div>
                  </TableCell>
                  <TableCell>
                    <div v-if="log.user_name" class="text-sm">
                      <p class="font-medium">{{ log.user_name }}</p>
                      <p class="text-muted-foreground text-xs">ID: {{ log.user_id }}</p>
                    </div>
                    <span v-else class="text-muted-foreground text-sm">System</span>
                  </TableCell>
                  <TableCell class="font-mono text-xs">
                    {{ log.ip_address }}
                  </TableCell>
                  <TableCell>
                    <Badge variant="outline" class="text-xs">
                      {{ log.context }}
                    </Badge>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>

          <!-- Pagination -->
          <div v-if="props.logs?.meta && props.logs.meta.last_page > 1" class="flex items-center justify-between mt-4">
            <p class="text-sm text-muted-foreground">
              Page {{ props.logs.meta.current_page }} of {{ props.logs.meta.last_page }}
              ({{ props.logs.meta.total }} total logs)
            </p>
            <div class="flex items-center space-x-2">
              <Button 
                variant="outline" 
                size="sm" 
                :disabled="props.logs.meta.current_page === 1"
                @click="router.get(route('super-admin.system.logs', { ...filters, page: props.logs.meta.current_page - 1 }))"
              >
                Previous
              </Button>
              <Button 
                variant="outline" 
                size="sm" 
                :disabled="props.logs.meta.current_page === props.logs.meta.last_page"
                @click="router.get(route('super-admin.system.logs', { ...filters, page: props.logs.meta.current_page + 1 }))"
              >
                Next
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Last Error Alert -->
      <Card v-if="props.log_stats?.last_error" class="border-red-200 bg-red-50">
        <CardHeader>
          <CardTitle class="text-red-800 flex items-center gap-2">
            <AlertTriangleIcon class="h-5 w-5" />
            Recent Error Alert
          </CardTitle>
        </CardHeader>
        <CardContent>
          <p class="text-red-700 text-sm">{{ props.log_stats.last_error }}</p>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
