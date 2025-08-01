<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { Progress } from '@/components/ui/progress';
import { computed } from 'vue';
import { 
  CalendarIcon, 
  FileTextIcon, 
  UserIcon,
  Pencil as EditIcon,
  ArrowLeftIcon,
  CheckCircleIcon,
  XCircleIcon,
  ClockIcon as PendingIcon,
  AlertCircleIcon,
  InfoIcon
} from 'lucide-vue-next';

interface LeaveRequest {
  id: number;
  type: string;
  type_display: string;
  start_date: string;
  start_date_raw: string;
  end_date: string;
  end_date_raw: string;
  days: number;
  status: 'pending' | 'approved' | 'rejected' | 'cancelled';
  status_display: string;
  reason: string;
  approver_name?: string;
  submitted_at: string;
  submitted_at_raw: string;
  updated_at: string;
  updated_at_raw: string;
  can_edit: boolean;
  can_cancel: boolean;
  is_past: boolean;
  is_future: boolean;
  is_current: boolean;
}

interface LeaveType {
  value: string;
  label: string;
  description?: string;
  full_pay_days: number;
  half_pay_days: number;
  requires_approval: boolean;
  requires_documentation: boolean;
}

interface LeaveBalance {
  type: string;
  total_days: number;
  used_days: number;
  pending_days: number;
  remaining_days: number;
  percentage_used: number;
}

interface Props {
  leaveRequest: {
    data: LeaveRequest;
  };
  leaveTypes?: LeaveType[];
  leaveBalance?: LeaveBalance[];
}

const props = defineProps<Props>();

// Get leave type details
const leaveTypeDetails = computed(() => {
  if (!props.leaveTypes) return null;
  return props.leaveTypes.find(lt => lt.value === props.leaveRequest.data.type);
});

// Get current balance for this leave type
const currentBalance = computed(() => {
  if (!props.leaveBalance) return null;
  return props.leaveBalance.find(b => b.type.toLowerCase() === props.leaveRequest.data.type);
});

// Get status variant for badge
const getStatusVariant = (status: string) => {
  switch (status) {
    case 'approved':
      return 'default';
    case 'rejected':
      return 'destructive';
    case 'cancelled':
      return 'secondary';
    case 'pending':
    default:
      return 'outline';
  }
};

// Get status icon
const getStatusIcon = (status: string) => {
  switch (status) {
    case 'approved':
      return CheckCircleIcon;
    case 'rejected':
      return XCircleIcon;
    case 'cancelled':
      return AlertCircleIcon;
    case 'pending':
    default:
      return PendingIcon;
  }
};

// Format date for display
const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

// Format datetime for display
const formatDateTime = (dateString: string) => {
  return new Date(dateString).toLocaleString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

// Calculate business days between dates
const calculateBusinessDays = (start: string, end: string) => {
  const startDate = new Date(start);
  const endDate = new Date(end);
  
  const diffTime = Math.abs(endDate.getTime() - startDate.getTime());
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
  
  return diffDays;
};

// Get pay structure description
const getPayDescription = (fullPay: number, halfPay: number) => {
  if (fullPay === 0 && halfPay === 0) return 'Unpaid';
  if (halfPay > 0) {
    return `${fullPay} days full pay + ${halfPay} days half pay`;
  }
  return `${fullPay} days full pay`;
};

// Download documentation
const downloadDocumentation = () => {
  // Not available in current data structure
  console.log('Documentation download not implemented');
};
</script>

<template>
  <Head :title="`Leave Request #${leaveRequest.data.id}`" />

  <AppLayout
    :breadcrumbs="[
      { title: 'Employee', href: route('dashboard') },
      { title: 'Leave Requests', href: route('leave-requests.index') },
      { title: `Request #${leaveRequest.data.id}`, href: '#' }
    ]">
    
    <div class="max-w-4xl p-6 space-y-8">
      <!-- Header Section -->
      <div class="flex items-start justify-between">
        <div class="space-y-2">
          <div class="flex items-center gap-3">
            <h1 class="text-3xl font-bold tracking-tight">Leave Request #{{ leaveRequest.data.id }}</h1>
            <Badge :variant="getStatusVariant(leaveRequest.data.status)" class="text-sm">
              <component :is="getStatusIcon(leaveRequest.data.status)" class="h-3 w-3 mr-1" />
              {{ leaveRequest.data.status_display }}
            </Badge>
          </div>
          <p class="text-lg text-muted-foreground">
            {{ leaveRequest.data.type_display }} leave request details
          </p>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-2">
          <Button variant="outline" size="sm" as-child>
            <Link :href="route('leave-requests.index')">
              <ArrowLeftIcon class="h-4 w-4 mr-2" />
              Back to List
            </Link>
          </Button>
          
          <Button v-if="leaveRequest.data.can_edit && leaveRequest.data.status === 'pending'" variant="outline" size="sm" as-child>
            <Link :href="route('leave-requests.edit', leaveRequest.data.id)">
              <EditIcon class="h-4 w-4 mr-2" />
              Edit
            </Link>
          </Button>
        </div>
      </div>

      <!-- Main Content Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Details - Left Side (2/3) -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Leave Request Overview -->
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center gap-2">
                <CalendarIcon class="h-5 w-5" />
                Request Overview
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                  <div>
                    <label class="text-sm font-medium text-muted-foreground">Leave Type</label>
                    <p class="text-lg font-semibold">{{ leaveRequest.data.type_display }}</p>
                  </div>
                  
                  <div>
                    <label class="text-sm font-medium text-muted-foreground">Start Date</label>
                    <p class="text-lg">{{ leaveRequest.data.start_date }}</p>
                  </div>
                  
                  <div>
                    <label class="text-sm font-medium text-muted-foreground">End Date</label>
                    <p class="text-lg">{{ leaveRequest.data.end_date }}</p>
                  </div>
                </div>

                <div class="space-y-4">
                  <div>
                    <label class="text-sm font-medium text-muted-foreground">Duration</label>
                    <p class="text-lg font-semibold">{{ leaveRequest.data.days }} {{ leaveRequest.data.days === 1 ? 'day' : 'days' }}</p>
                  </div>
                  
                  <div>
                    <label class="text-sm font-medium text-muted-foreground">Status</label>
                    <div class="mt-1">
                      <Badge :variant="getStatusVariant(leaveRequest.data.status)">
                        <component :is="getStatusIcon(leaveRequest.data.status)" class="h-3 w-3 mr-1" />
                        {{ leaveRequest.data.status_display }}
                      </Badge>
                    </div>
                  </div>
                  
                  <div>
                    <label class="text-sm font-medium text-muted-foreground">Submitted</label>
                    <p class="text-sm">{{ leaveRequest.data.submitted_at }}</p>
                  </div>
                </div>
              </div>

              <!-- Leave Type Details -->
              <div v-if="leaveTypeDetails" class="mt-6 p-4 border rounded-lg bg-muted/50">
                <h4 class="font-semibold text-sm mb-3">{{ leaveTypeDetails.label }} Policy</h4>
                <div class="grid grid-cols-2 gap-4 text-sm">
                  <div>
                    <span class="text-muted-foreground">Pay Structure:</span>
                    <span class="ml-2 font-medium">
                      {{ getPayDescription(leaveTypeDetails.full_pay_days, leaveTypeDetails.half_pay_days) }}
                    </span>
                  </div>
                  <div>
                    <span class="text-muted-foreground">Approval Required:</span>
                    <span class="ml-2 font-medium">
                      {{ leaveTypeDetails.requires_approval ? 'Yes' : 'No' }}
                    </span>
                  </div>
                </div>
                <div v-if="leaveTypeDetails.description" class="mt-3 text-sm text-muted-foreground">
                  {{ leaveTypeDetails.description }}
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Reason -->
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center gap-2">
                <FileTextIcon class="h-5 w-5" />
                Reason for Leave
              </CardTitle>
            </CardHeader>
            <CardContent>
              <p class="text-sm leading-relaxed whitespace-pre-wrap">{{leaveRequest.data.reason }}</p>
            </CardContent>
          </Card>

          <!-- Approval/Rejection Details -->
          <Card v-if="leaveRequest.data.status !== 'pending'">
            <CardHeader>
              <CardTitle class="flex items-center gap-2">
                <UserIcon class="h-5 w-5" />
                {{ leaveRequest.data.status === 'approved' ? 'Approval' : 'Rejection' }} Details
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div class="space-y-4">
                <div v-if="leaveRequest.data.approver_name">
                  <label class="text-sm font-medium text-muted-foreground">
                    {{ leaveRequest.data.status === 'approved' ? 'Approved by' : 'Reviewed by' }}
                  </label>
                  <p class="text-lg font-semibold">{{ leaveRequest.data.approver_name }}</p>
                </div>
                
                <div>
                  <label class="text-sm font-medium text-muted-foreground">
                    {{ leaveRequest.data.status === 'approved' ? 'Approved on' : 'Reviewed on' }}
                  </label>
                  <p class="text-sm">{{ leaveRequest.data.updated_at }}</p>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Sidebar - Right Side (1/3) -->
        <div class="lg:col-span-1 space-y-6">
          <!-- Quick Actions -->
          <Card v-if="leaveRequest.data.status === 'pending'">
            <CardHeader>
              <CardTitle class="text-lg">Quick Actions</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="space-y-3">
                <Button v-if="leaveRequest.data.can_edit" variant="outline" size="sm" class="w-full" as-child>
                  <Link :href="route('leave-requests.edit', leaveRequest.data.id)">
                    <EditIcon class="h-4 w-4 mr-2" />
                    Edit Request
                  </Link>
                </Button>
                
                <Button v-if="leaveRequest.data.can_cancel" variant="outline" size="sm" class="w-full text-red-600 hover:text-red-700">
                  <XCircleIcon class="h-4 w-4 mr-2" />
                  Cancel Request
                </Button>
              </div>
            </CardContent>
          </Card>

          <!-- Leave Balance Impact -->
          <Card v-if="currentBalance">
            <CardHeader>
              <CardTitle class="text-lg">Leave Balance Impact</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="space-y-4">
                <div class="space-y-2">
                  <div class="flex items-center justify-between">
                    <span class="font-medium capitalize">{{ currentBalance.type }} Leave</span>
                    <span class="text-sm text-muted-foreground">
                      {{ currentBalance.remaining_days }}/{{ currentBalance.total_days }} days
                    </span>
                  </div>
                  <Progress :value="currentBalance.percentage_used" 
                           :class="currentBalance.percentage_used > 80 ? 'text-red-500' : 
                                  currentBalance.percentage_used > 60 ? 'text-amber-500' : 'text-green-500'" />
                  <div class="text-xs text-muted-foreground">
                    {{ currentBalance.used_days }} used, {{ currentBalance.pending_days }} pending
                  </div>
                </div>

                <Separator />

                <div class="space-y-2">
                  <div class="flex items-center justify-between">
                    <span class="text-sm">This Request:</span>
                    <span class="font-medium">{{ leaveRequest.data.days }} days</span>
                  </div>
                  
                  <div v-if="leaveRequest.data.status === 'approved'" class="flex items-center justify-between">
                    <span class="text-sm font-medium">After This Request:</span>
                    <span class="font-bold text-green-600">
                      {{ Math.max(0, currentBalance.remaining_days - leaveRequest.data.days) }} days
                    </span>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Request Timeline -->
          <Card>
            <CardHeader>
              <CardTitle class="text-lg">Request Timeline</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="space-y-4">
                <div class="flex items-start gap-3">
                  <div class="h-2 w-2 rounded-full bg-blue-600 mt-2"></div>
                  <div>
                    <p class="font-medium text-sm">Request Submitted</p>
                    <p class="text-xs text-muted-foreground">{{ leaveRequest.data.submitted_at }}</p>
                  </div>
                </div>
                
                <div v-if="leaveRequest.data.status !== 'pending'" class="flex items-start gap-3">
                  <div class="h-2 w-2 rounded-full mt-2" 
                       :class="{
                         'bg-green-600': leaveRequest.data.status === 'approved',
                         'bg-red-600': leaveRequest.data.status === 'rejected',
                         'bg-gray-600': leaveRequest.data.status === 'cancelled'
                       }"></div>
                  <div>
                    <p class="font-medium text-sm">{{ leaveRequest.data.status_display }}</p>
                    <p class="text-xs text-muted-foreground">{{ leaveRequest.data.updated_at }}</p>
                  </div>
                </div>
                
                <div v-else class="flex items-start gap-3">
                  <div class="h-2 w-2 rounded-full bg-amber-600 mt-2"></div>
                  <div>
                    <p class="font-medium text-sm">Pending Review</p>
                    <p class="text-xs text-muted-foreground">Waiting for manager approval</p>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Help & Support -->
          <Card>
            <CardHeader>
              <CardTitle class="text-lg">Need Help?</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="space-y-3 text-sm">
                <div class="flex items-start gap-2">
                  <InfoIcon class="h-4 w-4 text-blue-600 mt-0.5 flex-shrink-0" />
                  <span>Contact your manager for urgent requests or questions about this leave request.</span>
                </div>
                
                <div class="space-y-1 pt-2">
                  <div>📧 <strong>HR Team:</strong> hr@company.com</div>
                  <div>📞 <strong>Support:</strong> +1 (555) 123-4567</div>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
