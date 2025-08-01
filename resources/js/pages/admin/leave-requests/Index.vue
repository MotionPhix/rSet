<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { 
  CalendarIcon,
  FilterIcon,
  SearchIcon,
  CheckIcon,
  XIcon,
  ClockIcon,
  EyeIcon,
  DownloadIcon
} from 'lucide-vue-next';

interface LeaveRequest {
  id: number;
  employee_name: string;
  employee_team: string;
  leave_type: string;
  start_date: string;
  end_date: string;
  duration: number;
  status: 'pending' | 'approved' | 'rejected';
  reason: string;
  submitted_at: string;
  manager_name?: string;
}

interface Props {
  leave_requests: {
    data: LeaveRequest[];
    meta: {
      current_page: number;
      last_page: number;
      per_page: number;
      total: number;
    };
  };
  filters: {
    status?: string;
    team?: string;
    leave_type?: string;
    search?: string;
    date_from?: string;
    date_to?: string;
  };
  teams: Array<{ id: number; name: string; }>;
  leave_types: Array<{ id: number; name: string; }>;
  stats: {
    total: number;
    pending: number;
    approved: number;
    rejected: number;
  };
}

const props = defineProps<Props>();

const breadcrumbs = computed((): BreadcrumbItem[] => [
  {
    title: 'Admin',
    href: route('admin.dashboard')
  },
  {
    title: 'Leave Requests',
    href: route('admin.leave-requests.index')
  }
]);

// Filter state
const filters = ref({
  status: props.filters?.status || '',
  team: props.filters?.team || '',
  leave_type: props.filters?.leave_type || '',
  search: props.filters?.search || '',
  date_from: props.filters?.date_from || '',
  date_to: props.filters?.date_to || '',
});

const activeTab = ref('all');

// Apply filters
const applyFilters = () => {
  router.get(route('admin.leave-requests.index'), filters.value, {
    preserveState: true,
    preserveScroll: true,
  });
};

// Clear filters
const clearFilters = () => {
  filters.value = {
    status: '',
    team: '',
    leave_type: '',
    search: '',
    date_from: '',
    date_to: '',
  };
  applyFilters();
};

// Approve/Reject actions
const approveRequest = (requestId: number) => {
  router.post(route('admin.leave-requests.approve', requestId), {}, {
    preserveScroll: true,
  });
};

const rejectRequest = (requestId: number) => {
  router.post(route('admin.leave-requests.reject', requestId), {}, {
    preserveScroll: true,
  });
};

// Export requests
const exportRequests = () => {
  window.open(route('admin.reports.export', { type: 'leave_requests', ...filters.value }));
};

// Get status badge variant
const getStatusBadge = (status: string) => {
  const statusMap: { [key: string]: { variant: 'default' | 'destructive' | 'outline' | 'secondary', label: string } } = {
    'pending': { variant: 'outline', label: 'Pending' },
    'approved': { variant: 'default', label: 'Approved' },
    'rejected': { variant: 'destructive', label: 'Rejected' },
  };
  
  return statusMap[status.toLowerCase()] || { variant: 'outline', label: status };
};

// Format date
const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString();
};

// Filter requests by tab
const filteredRequests = computed(() => {
  if (activeTab.value === 'all') return props.leave_requests?.data || [];
  return (props.leave_requests?.data || []).filter(request => request.status === activeTab.value);
});

// Get tab counts
const getTabCount = (status: string) => {
  if (status === 'all') return props.stats?.total || 0;
  return props.stats?.[status as keyof typeof props.stats] || 0;
};
</script>

<template>
  <Head title="Leave Requests Management" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div class="space-y-1">
          <h1 class="text-3xl font-bold tracking-tight">Leave Requests</h1>
          <p class="text-muted-foreground">
            Review and manage employee leave requests
          </p>
        </div>
        <Button variant="outline" @click="exportRequests">
          <DownloadIcon class="h-4 w-4 mr-2" />
          Export
        </Button>
      </div>

      <!-- Stats Overview -->
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Requests</CardTitle>
            <CalendarIcon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ props.stats?.total || 0 }}</div>
            <p class="text-xs text-muted-foreground">
              All time requests
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Pending</CardTitle>
            <ClockIcon class="h-4 w-4 text-yellow-500" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-yellow-600">{{ props.stats?.pending || 0 }}</div>
            <p class="text-xs text-muted-foreground">
              Awaiting approval
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Approved</CardTitle>
            <CheckIcon class="h-4 w-4 text-green-500" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-green-600">{{ props.stats?.approved || 0 }}</div>
            <p class="text-xs text-muted-foreground">
              Successfully approved
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Rejected</CardTitle>
            <XIcon class="h-4 w-4 text-red-500" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-red-600">{{ props.stats?.rejected || 0 }}</div>
            <p class="text-xs text-muted-foreground">
              Declined requests
            </p>
          </CardContent>
        </Card>
      </div>

      <!-- Filters -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <FilterIcon class="h-5 w-5" />
            Filters
          </CardTitle>
          <CardDescription>Filter requests by various criteria</CardDescription>
        </CardHeader>
        <CardContent class="space-y-4">
          <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-6">
            <div class="space-y-2">
              <Label for="search">Search</Label>
              <Input 
                id="search"
                v-model="filters.search" 
                placeholder="Employee name..."
                @keyup.enter="applyFilters"
              />
            </div>

            <div class="space-y-2">
              <Label for="status">Status</Label>
              <Select v-model="filters.status">
                <SelectTrigger>
                  <SelectValue placeholder="All statuses" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="">All statuses</SelectItem>
                  <SelectItem value="pending">Pending</SelectItem>
                  <SelectItem value="approved">Approved</SelectItem>
                  <SelectItem value="rejected">Rejected</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div class="space-y-2">
              <Label for="team">Team</Label>
              <Select v-model="filters.team">
                <SelectTrigger>
                  <SelectValue placeholder="All teams" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="">All teams</SelectItem>
                  <SelectItem 
                    v-for="team in props.teams" 
                    :key="team.id" 
                    :value="team.id.toString()"
                  >
                    {{ team.name }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div class="space-y-2">
              <Label for="leave_type">Leave Type</Label>
              <Select v-model="filters.leave_type">
                <SelectTrigger>
                  <SelectValue placeholder="All types" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="">All types</SelectItem>
                  <SelectItem 
                    v-for="leaveType in props.leave_types" 
                    :key="leaveType.id" 
                    :value="leaveType.id.toString()"
                  >
                    {{ leaveType.name }}
                  </SelectItem>
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

      <!-- Requests Table with Tabs -->
      <Card>
        <CardHeader>
          <CardTitle>Leave Requests</CardTitle>
          <CardDescription>
            Showing {{ props.leave_requests?.data?.length || 0 }} of {{ props.leave_requests?.meta?.total || 0 }} requests
          </CardDescription>
        </CardHeader>
        <CardContent>
          <Tabs v-model="activeTab" class="w-full">
            <TabsList>
              <TabsTrigger value="all">
                All ({{ getTabCount('all') }})
              </TabsTrigger>
              <TabsTrigger value="pending">
                Pending ({{ getTabCount('pending') }})
              </TabsTrigger>
              <TabsTrigger value="approved">
                Approved ({{ getTabCount('approved') }})
              </TabsTrigger>
              <TabsTrigger value="rejected">
                Rejected ({{ getTabCount('rejected') }})
              </TabsTrigger>
            </TabsList>

            <TabsContent value="all" class="mt-4">
              <div class="rounded-md border">
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Employee</TableHead>
                      <TableHead>Team</TableHead>
                      <TableHead>Leave Type</TableHead>
                      <TableHead>Duration</TableHead>
                      <TableHead>Dates</TableHead>
                      <TableHead>Status</TableHead>
                      <TableHead>Submitted</TableHead>
                      <TableHead class="text-right">Actions</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    <TableRow v-if="!filteredRequests.length">
                      <TableCell colspan="8" class="text-center text-muted-foreground py-8">
                        No leave requests found
                      </TableCell>
                    </TableRow>
                    <TableRow 
                      v-for="request in filteredRequests" 
                      :key="request.id"
                      class="hover:bg-muted/50"
                    >
                      <TableCell>
                        <div>
                          <p class="font-medium">{{ request.employee_name }}</p>
                        </div>
                      </TableCell>
                      <TableCell>
                        <Badge variant="outline">{{ request.employee_team }}</Badge>
                      </TableCell>
                      <TableCell>{{ request.leave_type }}</TableCell>
                      <TableCell>{{ request.duration }} {{ request.duration === 1 ? 'day' : 'days' }}</TableCell>
                      <TableCell>
                        <div class="text-sm">
                          <p>{{ formatDate(request.start_date) }}</p>
                          <p class="text-muted-foreground">to {{ formatDate(request.end_date) }}</p>
                        </div>
                      </TableCell>
                      <TableCell>
                        <Badge :variant="getStatusBadge(request.status).variant">
                          {{ getStatusBadge(request.status).label }}
                        </Badge>
                      </TableCell>
                      <TableCell class="text-sm text-muted-foreground">
                        {{ formatDate(request.submitted_at) }}
                      </TableCell>
                      <TableCell class="text-right">
                        <div class="flex items-center justify-end space-x-2">
                          <Button variant="ghost" size="sm" as-child>
                            <Link :href="route('admin.leave-requests.show', request.id)">
                              <EyeIcon class="h-4 w-4" />
                            </Link>
                          </Button>
                          <Button 
                            v-if="request.status === 'pending'"
                            variant="ghost" 
                            size="sm" 
                            @click="approveRequest(request.id)"
                            class="text-green-600 hover:text-green-700"
                          >
                            <CheckIcon class="h-4 w-4" />
                          </Button>
                          <Button 
                            v-if="request.status === 'pending'"
                            variant="ghost" 
                            size="sm" 
                            @click="rejectRequest(request.id)"
                            class="text-red-600 hover:text-red-700"
                          >
                            <XIcon class="h-4 w-4" />
                          </Button>
                        </div>
                      </TableCell>
                    </TableRow>
                  </TableBody>
                </Table>
              </div>
            </TabsContent>

            <!-- Other tab contents would be similar, filtering by status -->
            <TabsContent value="pending" class="mt-4">
              <!-- Same table structure but filtered for pending requests -->
              <div class="rounded-md border">
                <Table>
                  <!-- Table content same as above but filtered -->
                </Table>
              </div>
            </TabsContent>

            <TabsContent value="approved" class="mt-4">
              <!-- Same table structure but filtered for approved requests -->
              <div class="rounded-md border">
                <Table>
                  <!-- Table content same as above but filtered -->
                </Table>
              </div>
            </TabsContent>

            <TabsContent value="rejected" class="mt-4">
              <!-- Same table structure but filtered for rejected requests -->
              <div class="rounded-md border">
                <Table>
                  <!-- Table content same as above but filtered -->
                </Table>
              </div>
            </TabsContent>
          </Tabs>

          <!-- Pagination -->
          <div v-if="props.leave_requests?.meta && props.leave_requests.meta.last_page > 1" class="flex items-center justify-between mt-4">
            <p class="text-sm text-muted-foreground">
              Page {{ props.leave_requests.meta.current_page }} of {{ props.leave_requests.meta.last_page }}
              ({{ props.leave_requests.meta.total }} total requests)
            </p>
            <div class="flex items-center space-x-2">
              <Button 
                variant="outline" 
                size="sm" 
                :disabled="props.leave_requests.meta.current_page === 1"
                @click="router.get(route('admin.leave-requests.index', { ...filters, page: props.leave_requests.meta.current_page - 1 }))"
              >
                Previous
              </Button>
              <Button 
                variant="outline" 
                size="sm" 
                :disabled="props.leave_requests.meta.current_page === props.leave_requests.meta.last_page"
                @click="router.get(route('admin.leave-requests.index', { ...filters, page: props.leave_requests.meta.current_page + 1 }))"
              >
                Next
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
