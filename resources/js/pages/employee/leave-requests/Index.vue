<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type SharedData } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import AlertDialog from '@/components/ui/alert-dialog/AlertDialog.vue';
import AlertDialogAction from '@/components/ui/alert-dialog/AlertDialogAction.vue';
import AlertDialogCancel from '@/components/ui/alert-dialog/AlertDialogCancel.vue';
import AlertDialogContent from '@/components/ui/alert-dialog/AlertDialogContent.vue';
import AlertDialogDescription from '@/components/ui/alert-dialog/AlertDialogDescription.vue';
import AlertDialogFooter from '@/components/ui/alert-dialog/AlertDialogFooter.vue';
import AlertDialogHeader from '@/components/ui/alert-dialog/AlertDialogHeader.vue';
import AlertDialogTitle from '@/components/ui/alert-dialog/AlertDialogTitle.vue';
import AlertDialogTrigger from '@/components/ui/alert-dialog/AlertDialogTrigger.vue';
import { CalendarDaysIcon, PlusIcon, SearchIcon, FilterIcon, EyeIcon, EditIcon, TrashIcon, MoreHorizontalIcon, ClockIcon, CheckCircleIcon, XCircleIcon } from 'lucide-vue-next';
import LeaveCard from '@/components/LeaveCard.vue';

// Simple debounce function
function debounce<T extends (...args: any[]) => any>(func: T, delay: number): (...args: Parameters<T>) => void {
  let timeoutId: ReturnType<typeof setTimeout>;
  return (...args: Parameters<T>) => {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => func.apply(null, args), delay);
  };
}

interface LeaveRequest {
  id: number;
  type: string;
  type_display: string;
  start_date: string;
  end_date: string;
  days: number;
  status: string;
  reason: string;
  approver_name: string | null;
  submitted_at: string;
  can_edit: boolean;
  can_cancel: boolean;
}

interface Stats {
  total: number;
  pending: number;
  approved: number;
  rejected: number;
  days_this_year: number;
}

interface Props {
  leaveRequests: {
    data: LeaveRequest[];
    meta: {
      total: number;
      per_page: number;
      current_page: number;
      last_page: number;
      from: number;
      to: number;
      has_more_pages: boolean;
    };
    links: {
      first: string;
      last: string;
      prev: string | null;
      next: string | null;
    };
  };
  stats: Stats;
  filters: {
    status?: string;
    type?: string;
    year?: string;
    search?: string;
  };
  availableYears: number[];
  leaveTypes: Array<{
    value: string;
    label: string;
  }>;
}

const props = defineProps<Props>();
const page = usePage<SharedData>();

// Local state for filters
const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || 'all');
const typeFilter = ref(props.filters.type || 'all');
const yearFilter = ref(props.filters.year || 'all');

// Debounced search
const debouncedSearch = debounce((value: string) => {
  updateFilters({ search: value });
}, 300);

// Watch search input and trigger debounced search
watch(search, (newValue) => {
  debouncedSearch(newValue);
});

// Filter functions
const updateFilters = (newFilters: Partial<typeof props.filters>) => {
  router.get(route('leave-requests.index'), {
    ...props.filters,
    ...newFilters,
  }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const clearFilters = () => {
  search.value = '';
  statusFilter.value = 'all';
  typeFilter.value = 'all';
  yearFilter.value = 'all';
  router.get(route('leave-requests.index'), {}, {
    preserveState: true,
    preserveScroll: true,
  });
};

// Helper functions
const getStatusBadgeVariant = (status: string) => {
  switch (status) {
    case 'approved': return 'default';
    case 'pending': return 'secondary';
    case 'rejected': return 'destructive';
    default: return 'outline';
  }
};

const getStatusIcon = (status: string) => {
  switch (status) {
    case 'approved': return CheckCircleIcon;
    case 'pending': return ClockIcon;
    case 'rejected': return XCircleIcon;
    default: return ClockIcon;
  }
};

const getTypeDisplayName = (request: LeaveRequest) => {
  return request.type_display;
};

const handleStatusFilter = (value: any) => {
  updateFilters({ status: value === 'all' ? undefined : value });
};

const handleTypeFilter = (value: any) => {
  updateFilters({ type: value === 'all' ? undefined : value });
};

const handleYearFilter = (value: any) => {
  updateFilters({ year: value === 'all' ? undefined : value });
};

const deleteRequest = (id: number) => {
  router.delete(route('leave-requests.destroy', id), {
    onSuccess: () => {
      // Success message will be handled by the flash message system
    }
  });
};

// Computed properties
const hasActiveFilters = computed(() => {
  return !!(props.filters.search || props.filters.status || props.filters.type || props.filters.year);
});

const statusCounts = computed(() => [
  { status: 'All', count: props.stats.total, variant: 'outline' },
  { status: 'pending', count: props.stats.pending, variant: 'secondary' },
  { status: 'approved', count: props.stats.approved, variant: 'default' },
  { status: 'rejected', count: props.stats.rejected, variant: 'destructive' },
]);
</script>

<template>
  <Head title="Leave Requests" />

  <AppLayout
    :breadcrumbs="[
      { title: 'Dashboard', href: route('dashboard') },
      { title: 'Leave Requests', href: '#' }
    ]">

    <div class="space-y-6 p-6 max-w-4xl">
      <!-- Header Section -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold tracking-tight">Leave Requests</h1>
          <p class="text-muted-foreground">Manage your leave requests and track their status</p>
        </div>
        <Button as-child>
          <Link :href="route('leave-requests.create')">
            <PlusIcon class="h-4 w-4 mr-2" />
            New Request
          </Link>
        </Button>
      </div>

      <!-- Stats Overview -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Requests</CardTitle>
            <CalendarDaysIcon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ props.stats.total }}</div>
            <p class="text-xs text-muted-foreground">All time</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Pending</CardTitle>
            <ClockIcon class="h-4 w-4 text-yellow-500" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-yellow-600">{{ props.stats.pending }}</div>
            <p class="text-xs text-muted-foreground">Awaiting approval</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Approved</CardTitle>
            <CheckCircleIcon class="h-4 w-4 text-green-500" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-green-600">{{ props.stats.approved }}</div>
            <p class="text-xs text-muted-foreground">Successfully approved</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Rejected</CardTitle>
            <XCircleIcon class="h-4 w-4 text-red-500" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-red-600">{{ props.stats.rejected }}</div>
            <p class="text-xs text-muted-foreground">Not approved</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Days This Year</CardTitle>
            <CalendarDaysIcon class="h-4 w-4 text-blue-500" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-blue-600">{{ props.stats.days_this_year }}</div>
            <p class="text-xs text-muted-foreground">Leave taken</p>
          </CardContent>
        </Card>
      </div>

      <!-- Filters Section -->
      <Card>
        <CardHeader>
          <CardTitle class="text-lg">Filters</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Search -->
            <div class="space-y-2">
              <label class="text-sm font-medium">Search</label>
              <div class="relative">
                <SearchIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                <Input
                  v-model="search"
                  placeholder="Search requests..."
                  class="pl-10"
                />
              </div>
            </div>

            <!-- Status Filter -->
            <div class="space-y-2">
              <label class="text-sm font-medium">Status</label>
              <Select 
                v-model="statusFilter" 
                @update:model-value="handleStatusFilter">
                <SelectTrigger class="w-full is-large">
                  <SelectValue placeholder="All statuses" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All statuses</SelectItem>
                  <SelectItem value="pending">Pending</SelectItem>
                  <SelectItem value="approved">Approved</SelectItem>
                  <SelectItem value="rejected">Rejected</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <!-- Type Filter -->
            <div class="space-y-2">
              <label class="text-sm font-medium">Type</label>
              <Select 
                v-model="typeFilter" 
                @update:model-value="handleTypeFilter"
              >
                <SelectTrigger class="w-full is-large">
                  <SelectValue placeholder="All types" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All types</SelectItem>
                  <SelectItem v-for="type in props.leaveTypes" :key="type.value" :value="type.value">
                    {{ type.label }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>

            <!-- Year Filter -->
            <div class="space-y-2">
              <label class="text-sm font-medium">Year</label>
              <Select 
                v-model="yearFilter" 
                @update:model-value="handleYearFilter"
              >
                <SelectTrigger class="w-full is-large">
                  <SelectValue placeholder="All years" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All years</SelectItem>
                  <SelectItem v-for="year in props.availableYears" :key="year" :value="year.toString()">
                    {{ year }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>

          <div class="flex items-center justify-between mt-4">
            <div class="flex items-center gap-2">
              <Button 
                v-if="hasActiveFilters" 
                variant="outline" 
                size="sm" 
                @click="clearFilters">
                Clear Filters
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Leave Requests Table -->
      <Card>
        <CardHeader>
          <div class="flex items-center justify-between">
            <div>
              <CardTitle>Leave Requests</CardTitle>
              <CardDescription>
                Showing {{ props.leaveRequests.meta.from ?? 0 }} to {{ props.leaveRequests.meta.to ?? 0 }} 
                of {{ props.leaveRequests.meta.total }} requests
              </CardDescription>
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <div v-if="props.leaveRequests.data.length === 0" class="text-center py-8">
            <CalendarDaysIcon class="h-12 w-12 text-muted-foreground mx-auto mb-4" />
            <h3 class="text-lg font-medium mb-2">No leave requests found</h3>
            <p class="text-muted-foreground mb-4">
              {{ hasActiveFilters ? 'No requests match your filters.' : 'You haven\'t submitted any leave requests yet.' }}
            </p>
            <Button as-child>
              <Link :href="route('leave-requests.create')">
                <PlusIcon class="h-4 w-4 mr-2" />
                Create First Request
              </Link>
            </Button>
          </div>

          <div v-else class="space-y-4">
            <!-- Mobile Card View -->
            <div class="block md:hidden space-y-4">
              <div v-for="request in props.leaveRequests.data" :key="request.id" 
                   class="border rounded-lg p-3 space-y-4 bg-card relative overflow-hidden">
                <!-- Header with Type and Status -->
                <div class="flex items-start justify-between">
                  <div class="flex-1">
                    <h3 class="font-semibold text-lg mb-1">{{ request.type_display }}</h3>
                    <p v-if="request.reason" class="text-sm text-muted-foreground line-clamp-2">
                      {{ request.reason }}
                    </p>
                  </div>
                  <Badge 
                  :variant="getStatusBadgeVariant(request.status)" 
                  class="flex items-center gap-1 ml-3 absolute -top-1 -right-2 pt-1 pr-4 rounded-r-none">
                    <component :is="getStatusIcon(request.status)" class="h-3 w-3" />
                    {{ request.status.charAt(0).toUpperCase() + request.status.slice(1) }}
                  </Badge>
                </div>
                
                <!-- Details Grid -->
                <div class="grid grid-cols-2 gap-4 text-sm pt-3 border-t border-border/50">
                  <div>
                    <p class="text-xs font-medium text-muted-foreground uppercase tracking-wide mb-1">Period</p>
                    <div>
                      <div class="font-medium">{{ request.start_date }}</div>
                      <div class="text-muted-foreground">{{ request.end_date }}</div>
                    </div>
                  </div>
                  <div>
                    <p class="text-xs font-medium text-muted-foreground uppercase tracking-wide mb-1">Duration</p>
                    <div>
                      <span class="font-semibold">{{ request.days }}</span>
                      <span class="text-muted-foreground ml-1">{{ request.days === 1 ? 'day' : 'days' }}</span>
                    </div>
                  </div>
                  <div>
                    <p class="text-xs font-medium text-muted-foreground uppercase tracking-wide mb-1">Submitted</p>
                    <p class="font-medium">{{ request.submitted_at }}</p>
                  </div>
                  <div>
                    <p class="text-xs font-medium text-muted-foreground uppercase tracking-wide mb-1">
                      {{ request.status === 'approved' ? 'Approved by' : request.status === 'rejected' ? 'Reviewed by' : 'Assigned to' }}
                    </p>
                    <p class="font-medium">{{ request.approver_name || 'Pending' }}</p>
                  </div>
                </div>
                
                <!-- Actions -->
                <div class="flex items-center gap-2 pt-3 border-t border-border/50">
                  <Button variant="outline" size="sm" as-child class="flex-1">
                    <Link :href="route('leave-requests.show', request.id)">
                      <EyeIcon class="h-4 w-4 mr-2" />
                      View
                    </Link>
                  </Button>
                  
                  <Button v-if="request.can_edit" variant="outline" size="sm" as-child class="flex-1">
                    <Link :href="route('leave-requests.edit', request.id)">
                      <EditIcon class="h-4 w-4 mr-2" />
                      Edit
                    </Link>
                  </Button>
                  
                  <DropdownMenu v-if="request.can_cancel">
                    <DropdownMenuTrigger as-child>
                      <Button variant="link" size="icon">
                        <MoreHorizontalIcon class="h-4 w-4" />
                      </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                      <AlertDialog>
                        <AlertDialogTrigger as-child>
                          <DropdownMenuItem @click.prevent class="text-red-600">
                            <TrashIcon class="h-4 w-4 mr-2" />
                            Cancel Request
                          </DropdownMenuItem>
                        </AlertDialogTrigger>
                        <AlertDialogContent>
                          <AlertDialogHeader>
                            <AlertDialogTitle>Cancel Leave Request</AlertDialogTitle>
                            <AlertDialogDescription>
                              Are you sure you want to cancel this leave request for <strong>{{ request.type_display }}</strong>? This action cannot be undone.
                            </AlertDialogDescription>
                          </AlertDialogHeader>
                          <AlertDialogFooter>
                            <AlertDialogCancel>No, keep it</AlertDialogCancel>
                            <AlertDialogAction @click="deleteRequest(request.id)" class="bg-red-600 hover:bg-red-700">
                              Yes, cancel request
                            </AlertDialogAction>
                          </AlertDialogFooter>
                        </AlertDialogContent>
                      </AlertDialog>
                    </DropdownMenuContent>
                  </DropdownMenu>
                </div>
              </div>
            </div>

            <!-- Desktop Card View -->
            <div class="hidden md:block space-y-3">
              <div 
                v-for="request in props.leaveRequests.data" :key="request.id" 
                class="group border rounded-lg p-6 hover:shadow-md transition-all duration-200 bg-card">
     
                <div class="flex items-start justify-between">
                  <!-- Left Section: Main Info -->
                  <div class="flex-1 space-y-3">
                    <div class="flex items-start gap-4">
                      <!-- Type and Status -->
                      <div class="flex-1">
                        <div class="flex items-center gap-3 mb-1">
                          <h3 class="font-semibold text-lg">{{ request.type_display }}</h3>
                          <Badge :variant="getStatusBadgeVariant(request.status)" class="flex items-center gap-1">
                            <component :is="getStatusIcon(request.status)" class="h-3 w-3" />
                            {{ request.status.charAt(0).toUpperCase() + request.status.slice(1) }}
                          </Badge>
                        </div>
                        
                        <!-- Reason (if available) -->
                        <p v-if="request.reason" class="text-sm text-muted-foreground line-clamp-2 max-w-md">
                          {{ request.reason }}
                        </p>
                      </div>
                    </div>

                    <!-- Request Details Grid -->
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 pt-3 border-t border-border/50">
                      <!-- Dates -->
                      <div class="space-y-1">
                        <p class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Period</p>
                        <div class="text-sm">
                          <div class="font-medium">{{ request.start_date }}</div>
                          <div class="text-muted-foreground">{{ request.end_date }}</div>
                        </div>
                      </div>

                      <!-- Duration -->
                      <div class="space-y-1">
                        <p class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Duration</p>
                        <div class="text-sm">
                          <span class="font-semibold text-lg">{{ request.days }}</span>
                          <span class="text-muted-foreground ml-1">{{ request.days === 1 ? 'day' : 'days' }}</span>
                        </div>
                      </div>

                      <!-- Submitted -->
                      <div class="space-y-1">
                        <p class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Submitted</p>
                        <p class="text-sm font-medium">{{ request.submitted_at }}</p>
                      </div>

                      <!-- Approver -->
                      <div class="space-y-1">
                        <p class="text-xs font-medium text-muted-foreground uppercase tracking-wide">
                          {{ request.status === 'approved' ? 'Approved by' : request.status === 'rejected' ? 'Reviewed by' : 'Assigned to' }}
                        </p>
                        <p class="text-sm font-medium">{{ request.approver_name || 'Pending assignment' }}</p>
                      </div>
                    </div>
                  </div>

                  <!-- Right Section: Actions -->
                  <div class="flex items-start gap-2 ml-6">
                    <Button variant="outline" size="sm" as-child class="opacity-60 group-hover:opacity-100 transition-opacity">
                      <Link :href="route('leave-requests.show', request.id)">
                        <EyeIcon class="h-4 w-4 mr-2" />
                        View
                      </Link>
                    </Button>
                    
                    <Button v-if="request.can_edit" variant="outline" size="sm" as-child class="opacity-60 group-hover:opacity-100 transition-opacity">
                      <Link :href="route('leave-requests.edit', request.id)">
                        <EditIcon class="h-4 w-4 mr-2" />
                        Edit
                      </Link>
                    </Button>
                    
                    <AlertDialog v-if="request.can_cancel">
                      <AlertDialogTrigger as-child>
                        <Button variant="outline" size="sm" class="text-red-600 hover:text-red-700 hover:border-red-300 opacity-60 group-hover:opacity-100 transition-opacity">
                          <TrashIcon class="h-4 w-4 mr-2" />
                          Cancel
                        </Button>
                      </AlertDialogTrigger>
                      <AlertDialogContent>
                        <AlertDialogHeader>
                          <AlertDialogTitle>Cancel Leave Request</AlertDialogTitle>
                          <AlertDialogDescription>
                            Are you sure you want to cancel this leave request for <strong>{{ request.type_display }}</strong> 
                            from {{ request.start_date }} to {{ request.end_date }}? This action cannot be undone.
                          </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                          <AlertDialogCancel>No, keep it</AlertDialogCancel>
                          <AlertDialogAction @click="deleteRequest(request.id)" class="bg-red-600 hover:bg-red-700">
                            Yes, cancel request
                          </AlertDialogAction>
                        </AlertDialogFooter>
                      </AlertDialogContent>
                    </AlertDialog>
                  </div>
                </div>
              </div>
            </div>

            <!-- Pagination -->
            <div v-if="props.leaveRequests.meta.last_page > 1" class="flex items-center justify-between pt-4">
              <div class="text-sm text-muted-foreground">
                Showing {{ props.leaveRequests.from }} to {{ props.leaveRequests.to }} 
                of {{ props.leaveRequests.total }} results
              </div>
              
              <div class="flex items-center gap-2">
                <Button 
                  variant="outline"
                  size="sm"
                  :disabled="!props.leaveRequests.links.prev"
                  @click="props.leaveRequests.links.prev && router.get(props.leaveRequests.links.prev)"
                >
                  Previous
                </Button>
                <Button 
                  variant="outline"
                  size="sm"
                  :disabled="!props.leaveRequests.links.next"
                  @click="props.leaveRequests.links.next && router.get(props.leaveRequests.links.next)"
                >
                  Next
                </Button>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
