<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { 
  CalendarIcon,
  ClockIcon,
  UserIcon,
  BuildingIcon,
  FileTextIcon,
  CheckIcon,
  XIcon,
  ArrowLeftIcon,
  MessageSquareIcon
} from 'lucide-vue-next';

interface LeaveRequest {
  id: number;
  employee: {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    position: string;
  };
  team: {
    id: number;
    name: string;
    manager: string;
  };
  leave_type: {
    id: number;
    name: string;
    description: string;
    color: string;
  };
  start_date: string;
  end_date: string;
  duration: number;
  status: 'pending' | 'approved' | 'rejected';
  reason: string;
  manager_notes?: string;
  submitted_at: string;
  processed_at?: string;
  processed_by?: {
    id: number;
    name: string;
  };
  attachments?: Array<{
    id: number;
    name: string;
    url: string;
    size: string;
  }>;
}

interface Props {
  leave_request: LeaveRequest;
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
  },
  {
    title: `Request #${props.leave_request.id}`,
    href: route('admin.leave-requests.show', props.leave_request.id)
  }
]);

// Forms for approval/rejection
const approvalForm = useForm({
  notes: ''
});

const rejectionForm = useForm({
  notes: ''
});

const showApprovalDialog = ref(false);
const showRejectionDialog = ref(false);

// Actions
const approveRequest = () => {
  approvalForm.post(route('admin.leave-requests.approve', props.leave_request.id), {
    onSuccess: () => {
      showApprovalDialog.value = false;
      approvalForm.reset();
    }
  });
};

const rejectRequest = () => {
  rejectionForm.post(route('admin.leave-requests.reject', props.leave_request.id), {
    onSuccess: () => {
      showRejectionDialog.value = false;
      rejectionForm.reset();
    }
  });
};

// Get status badge variant
const getStatusBadge = (status: string) => {
  const statusMap: { [key: string]: { variant: 'default' | 'destructive' | 'outline' | 'secondary', label: string } } = {
    'pending': { variant: 'outline', label: 'Pending Review' },
    'approved': { variant: 'default', label: 'Approved' },
    'rejected': { variant: 'destructive', label: 'Rejected' },
  };
  
  return statusMap[status.toLowerCase()] || { variant: 'outline', label: status };
};

// Format date
const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

const formatDateTime = (dateString: string) => {
  return new Date(dateString).toLocaleString();
};

// Calculate working days
const getWorkingDays = computed(() => {
  return props.leave_request.duration;
});
</script>

<template>
  <Head :title="`Leave Request #${props.leave_request.id}`" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <Button variant="ghost" size="sm" @click="router.visit(route('admin.leave-requests.index'))">
            <ArrowLeftIcon class="h-4 w-4 mr-2" />
            Back to Requests
          </Button>
          <div class="space-y-1">
            <h1 class="text-3xl font-bold tracking-tight">Leave Request #{{ props.leave_request.id }}</h1>
            <div class="flex items-center space-x-2">
              <Badge :variant="getStatusBadge(props.leave_request.status).variant">
                {{ getStatusBadge(props.leave_request.status).label }}
              </Badge>
              <span class="text-muted-foreground">•</span>
              <span class="text-muted-foreground">Submitted {{ formatDateTime(props.leave_request.submitted_at) }}</span>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div v-if="props.leave_request.status === 'pending'" class="flex items-center space-x-2">
          <Button 
            variant="outline" 
            @click="showRejectionDialog = true"
            class="text-red-600 border-red-200 hover:bg-red-50"
          >
            <XIcon class="h-4 w-4 mr-2" />
            Reject
          </Button>
          <Button @click="showApprovalDialog = true">
            <CheckIcon class="h-4 w-4 mr-2" />
            Approve
          </Button>
        </div>
      </div>

      <div class="grid gap-6 md:grid-cols-3">
        <!-- Main Content -->
        <div class="md:col-span-2 space-y-6">
          <!-- Request Details -->
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center gap-2">
                <CalendarIcon class="h-5 w-5" />
                Leave Details
              </CardTitle>
            </CardHeader>
            <CardContent class="space-y-6">
              <!-- Leave Type and Duration -->
              <div class="grid gap-4 md:grid-cols-2">
                <div>
                  <Label class="text-sm font-medium text-muted-foreground">Leave Type</Label>
                  <div class="mt-1">
                    <Badge 
                      variant="outline" 
                      class="text-sm"
                      :style="{ 
                        borderColor: props.leave_request.leave_type.color,
                        backgroundColor: props.leave_request.leave_type.color + '10'
                      }"
                    >
                      {{ props.leave_request.leave_type.name }}
                    </Badge>
                  </div>
                  <p class="text-sm text-muted-foreground mt-1">
                    {{ props.leave_request.leave_type.description }}
                  </p>
                </div>

                <div>
                  <Label class="text-sm font-medium text-muted-foreground">Duration</Label>
                  <div class="mt-1">
                    <div class="flex items-center space-x-2">
                      <ClockIcon class="h-4 w-4 text-muted-foreground" />
                      <span class="font-medium">{{ getWorkingDays }} {{ getWorkingDays === 1 ? 'day' : 'days' }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <Separator />

              <!-- Dates -->
              <div class="grid gap-4 md:grid-cols-2">
                <div>
                  <Label class="text-sm font-medium text-muted-foreground">Start Date</Label>
                  <div class="mt-1">
                    <p class="font-medium">{{ formatDate(props.leave_request.start_date) }}</p>
                  </div>
                </div>

                <div>
                  <Label class="text-sm font-medium text-muted-foreground">End Date</Label>
                  <div class="mt-1">
                    <p class="font-medium">{{ formatDate(props.leave_request.end_date) }}</p>
                  </div>
                </div>
              </div>

              <Separator />

              <!-- Reason -->
              <div>
                <Label class="text-sm font-medium text-muted-foreground">Reason for Leave</Label>
                <div class="mt-2">
                  <div class="bg-muted/50 rounded-md p-4">
                    <p class="text-sm">{{ props.leave_request.reason }}</p>
                  </div>
                </div>
              </div>

              <!-- Manager Notes (if processed) -->
              <div v-if="props.leave_request.manager_notes && props.leave_request.status !== 'pending'">
                <Label class="text-sm font-medium text-muted-foreground">Manager Notes</Label>
                <div class="mt-2">
                  <div class="bg-muted/50 rounded-md p-4">
                    <p class="text-sm">{{ props.leave_request.manager_notes }}</p>
                    <div v-if="props.leave_request.processed_by" class="mt-2 text-xs text-muted-foreground">
                      — {{ props.leave_request.processed_by.name }} on {{ formatDateTime(props.leave_request.processed_at!) }}
                    </div>
                  </div>
                </div>
              </div>

              <!-- Attachments -->
              <div v-if="props.leave_request.attachments?.length">
                <Label class="text-sm font-medium text-muted-foreground">Attachments</Label>
                <div class="mt-2 space-y-2">
                  <div 
                    v-for="attachment in props.leave_request.attachments"
                    :key="attachment.id"
                    class="flex items-center justify-between p-3 border rounded-md"
                  >
                    <div class="flex items-center space-x-3">
                      <FileTextIcon class="h-4 w-4 text-muted-foreground" />
                      <div>
                        <p class="text-sm font-medium">{{ attachment.name }}</p>
                        <p class="text-xs text-muted-foreground">{{ attachment.size }}</p>
                      </div>
                    </div>
                    <Button variant="ghost" size="sm" as-child>
                      <a :href="attachment.url" target="_blank">View</a>
                    </Button>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Employee Information -->
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center gap-2">
                <UserIcon class="h-5 w-5" />
                Employee
              </CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
              <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center">
                  <UserIcon class="h-5 w-5 text-primary" />
                </div>
                <div>
                  <p class="font-medium">{{ props.leave_request.employee.name }}</p>
                  <p class="text-sm text-muted-foreground">{{ props.leave_request.employee.position }}</p>
                </div>
              </div>

              <Separator />

              <div class="space-y-2">
                <div class="flex justify-between text-sm">
                  <span class="text-muted-foreground">Email:</span>
                  <span>{{ props.leave_request.employee.email }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-muted-foreground">Employee ID:</span>
                  <span>#{{ props.leave_request.employee.id }}</span>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Team Information -->
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center gap-2">
                <BuildingIcon class="h-5 w-5" />
                Team
              </CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
              <div>
                <p class="font-medium">{{ props.leave_request.team.name }}</p>
                <p class="text-sm text-muted-foreground">Team</p>
              </div>

              <Separator />

              <div class="space-y-2">
                <div class="flex justify-between text-sm">
                  <span class="text-muted-foreground">Manager:</span>
                  <span>{{ props.leave_request.team.manager }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-muted-foreground">Team ID:</span>
                  <span>#{{ props.leave_request.team.id }}</span>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Request Timeline -->
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center gap-2">
                <ClockIcon class="h-5 w-5" />
                Timeline
              </CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
              <div class="space-y-3">
                <div class="flex items-start space-x-3">
                  <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                  <div>
                    <p class="text-sm font-medium">Request Submitted</p>
                    <p class="text-xs text-muted-foreground">{{ formatDateTime(props.leave_request.submitted_at) }}</p>
                  </div>
                </div>

                <div v-if="props.leave_request.processed_at" class="flex items-start space-x-3">
                  <div 
                    class="w-2 h-2 rounded-full mt-2"
                    :class="{
                      'bg-green-500': props.leave_request.status === 'approved',
                      'bg-red-500': props.leave_request.status === 'rejected'
                    }"
                  ></div>
                  <div>
                    <p class="text-sm font-medium">
                      {{ props.leave_request.status === 'approved' ? 'Approved' : 'Rejected' }}
                    </p>
                    <p class="text-xs text-muted-foreground">
                      {{ formatDateTime(props.leave_request.processed_at) }}
                    </p>
                    <p class="text-xs text-muted-foreground">
                      by {{ props.leave_request.processed_by?.name }}
                    </p>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>

      <!-- Approval Dialog -->
      <div v-if="showApprovalDialog" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <Card class="w-full max-w-md mx-4">
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <CheckIcon class="h-5 w-5 text-green-600" />
              Approve Leave Request
            </CardTitle>
            <CardDescription>
              Approve this leave request for {{ props.leave_request.employee.name }}
            </CardDescription>
          </CardHeader>
          <CardContent class="space-y-4">
            <form @submit.prevent="approveRequest" class="space-y-4">
              <div class="space-y-2">
                <Label for="approval_notes">Notes (Optional)</Label>
                <Textarea 
                  id="approval_notes"
                  v-model="approvalForm.notes" 
                  placeholder="Add any notes about this approval..."
                  rows="3"
                />
              </div>

              <div class="flex justify-end space-x-2">
                <Button variant="outline" @click="showApprovalDialog = false">
                  Cancel
                </Button>
                <Button type="submit" :disabled="approvalForm.processing">
                  {{ approvalForm.processing ? 'Approving...' : 'Approve Request' }}
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>

      <!-- Rejection Dialog -->
      <div v-if="showRejectionDialog" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <Card class="w-full max-w-md mx-4">
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <XIcon class="h-5 w-5 text-red-600" />
              Reject Leave Request
            </CardTitle>
            <CardDescription>
              Reject this leave request for {{ props.leave_request.employee.name }}
            </CardDescription>
          </CardHeader>
          <CardContent class="space-y-4">
            <form @submit.prevent="rejectRequest" class="space-y-4">
              <div class="space-y-2">
                <Label for="rejection_notes">Reason for Rejection *</Label>
                <Textarea 
                  id="rejection_notes"
                  v-model="rejectionForm.notes" 
                  placeholder="Please provide a reason for rejecting this request..."
                  rows="3"
                  required
                  :class="{ 'border-red-500': rejectionForm.errors.notes }"
                />
                <p v-if="rejectionForm.errors.notes" class="text-sm text-red-500">{{ rejectionForm.errors.notes }}</p>
              </div>

              <div class="flex justify-end space-x-2">
                <Button variant="outline" @click="showRejectionDialog = false">
                  Cancel
                </Button>
                <Button 
                  type="submit" 
                  variant="destructive" 
                  :disabled="rejectionForm.processing"
                >
                  {{ rejectionForm.processing ? 'Rejecting...' : 'Reject Request' }}
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
