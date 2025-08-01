<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Select, SelectTrigger, SelectValue, SelectContent, SelectItem } from '@/components/ui/select';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';
import { Separator } from '@/components/ui/separator';
import { Label } from '@/components/ui/label';
import DatePicker from '@/components/DatePicker.vue';
import InputError from '@/components/InputError.vue';
import { computed, ref, watch } from 'vue';
import { 
  CalendarIcon, 
  ClockIcon, 
  AlertCircleIcon, 
  InfoIcon, 
  CheckCircleIcon, 
  XCircleIcon,
  FileTextIcon,
  UsersIcon,
  TrendingUpIcon
} from 'lucide-vue-next';
import { DateValue } from '@internationalized/date';
import { toast } from 'vue-sonner';

interface LeaveType {
  value: string;
  label: string;
  description?: string;
  days_allowed: number;
  min_duration: number;
  max_duration: number;
  allow_custom_duration: boolean;
  gender?: string | null;
  min_employment_months?: number | null;
  cooldown_days?: number | null;
  max_usage_per_year?: number | null;
  full_pay_days: number;
  half_pay_days: number;
  requires_approval: boolean;
  requires_documentation: boolean;
  documentation_type?: string | null;
}

interface LeaveBalance {
  type: string;
  total_days: number;
  used_days: number;
  pending_days: number;
  remaining_days: number;
  percentage_used: number;
}

interface ConflictingRequest {
  id: number;
  employee_name: string;
  start_date: string;
  end_date: string;
  status: string;
  days: number;
}

interface Props {
  leaveTypes: LeaveType[];
  leaveBalance: LeaveBalance[];
  userProfile: {
    employment_start_date: string;
    gender: string;
    team_name?: string;
  };
}

const props = defineProps<Props>();

// Form state
const form = useForm({
  type: '',
  start_date: '',
  end_date: '',
  reason: '',
  documentation: null as File | null,
});

// Local reactive state
const selectedLeaveType = ref<LeaveType | null>(null);
const calculatedDays = ref(0);
const startDateValue = ref<DateValue | null>(null);
const endDateValue = ref<DateValue | null>(null);
const conflicts = ref<ConflictingRequest[]>([]);
const validationWarnings = ref<string[]>([]);
const isCheckingConflicts = ref(false);
const errors = ref<Record<string, string>>({});

// Simple form validation
const validateForm = () => {
  errors.value = {};
  
  if (!form.type) {
    errors.value.type = 'Please select a leave type';
  }
  
  if (!form.start_date) {
    errors.value.start_date = 'Start date is required';
  }
  
  if (!form.end_date) {
    errors.value.end_date = 'End date is required';
  }
  
  if (form.start_date && form.end_date && new Date(form.start_date) > new Date(form.end_date)) {
    errors.value.end_date = 'End date must be after or equal to start date';
  }
  
  if (!form.reason || form.reason.length < 10) {
    errors.value.reason = 'Please provide a detailed reason (minimum 10 characters)';
  }
  
  if (form.reason && form.reason.length > 500) {
    errors.value.reason = 'Reason cannot exceed 500 characters';
  }
  
  if (selectedLeaveType.value?.requires_documentation && !form.documentation) {
    errors.value.documentation = 'Documentation is required for this leave type';
  }
  
  if (selectedLeaveType.value && calculatedDays.value > 0) {
    if (calculatedDays.value < selectedLeaveType.value.min_duration) {
      errors.value.duration = `Minimum duration for this leave type is ${selectedLeaveType.value.min_duration} days`;
    }
    if (calculatedDays.value > selectedLeaveType.value.max_duration) {
      errors.value.duration = `Maximum duration for this leave type is ${selectedLeaveType.value.max_duration} days`;
    }
  }
  
  return Object.keys(errors.value).length === 0;
};

// Watch for leave type changes
watch(() => form.type, (newType) => {
  selectedLeaveType.value = props.leaveTypes.find(lt => lt.value === newType) || null;
  validateEmployeeEligibility();
  checkLeaveBalance();
});

// Watch for date changes
watch([startDateValue, endDateValue], () => {
  updateFormDates();
  calculateDays();
  checkForConflicts();
});

// Update form when date picker values change
const updateFormDates = () => {
  if (startDateValue.value) {
    form.start_date = startDateValue.value.toString();
  }
  if (endDateValue.value) {
    form.end_date = endDateValue.value.toString();
  }
};

// Calculate working days between dates
const calculateDays = () => {
  if (!startDateValue.value || !endDateValue.value) {
    calculatedDays.value = 0;
    return;
  }
  
  const start = new Date(startDateValue.value.toString());
  const end = new Date(endDateValue.value.toString());
  
  if (start > end) {
    calculatedDays.value = 0;
    return;
  }
  
  // Simple calculation - could be enhanced to exclude weekends/holidays
  const diffTime = Math.abs(end.getTime() - start.getTime());
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
  calculatedDays.value = diffDays;
};

// Validate employee eligibility for selected leave type
const validateEmployeeEligibility = () => {
  validationWarnings.value = [];
  
  if (!selectedLeaveType.value) return;
  
  const warnings: string[] = [];
  
  // Gender restrictions
  if (selectedLeaveType.value.gender && 
      selectedLeaveType.value.gender !== 'all' && 
      selectedLeaveType.value.gender !== props.userProfile.gender?.toLowerCase()) {
    warnings.push(`This leave type is only available for ${selectedLeaveType.value.gender} employees`);
  }
  
  // Employment duration
  if (selectedLeaveType.value.min_employment_months) {
    const employmentStart = new Date(props.userProfile.employment_start_date);
    const monthsEmployed = Math.floor((Date.now() - employmentStart.getTime()) / (1000 * 60 * 60 * 24 * 30));
    
    if (monthsEmployed < selectedLeaveType.value.min_employment_months) {
      warnings.push(`You need ${selectedLeaveType.value.min_employment_months} months of employment. You have ${monthsEmployed} months.`);
    }
  }
  
  validationWarnings.value = warnings;
};

// Check leave balance for selected type
const checkLeaveBalance = () => {
  if (!selectedLeaveType.value) return;
  
  const balance = props.leaveBalance.find(b => b.type.toLowerCase() === selectedLeaveType.value?.value);
  if (balance && balance.remaining_days <= 0) {
    validationWarnings.value.push('You have no remaining days for this leave type');
  }
};

// Check for conflicting requests (mock implementation)
const checkForConflicts = async () => {
  if (!startDateValue.value || !endDateValue.value || !selectedLeaveType.value) return;
  
  isCheckingConflicts.value = true;
  
  // Mock conflict check - in real implementation, this would be an API call
  setTimeout(() => {
    // Simulate some conflicts
    conflicts.value = [];
    isCheckingConflicts.value = false;
  }, 500);
};

// Get current balance for selected leave type
const currentBalance = computed(() => {
  if (!selectedLeaveType.value) return null;
  return props.leaveBalance.find(b => b.type.toLowerCase() === selectedLeaveType.value?.value);
});

// Calculate potential days after this request
const projectedBalance = computed(() => {
  if (!currentBalance.value || !calculatedDays.value) return null;
  
  return {
    ...currentBalance.value,
    remaining_days: currentBalance.value.remaining_days - calculatedDays.value,
    used_days: currentBalance.value.used_days + calculatedDays.value,
    percentage_used: Math.min(100, ((currentBalance.value.used_days + calculatedDays.value) / currentBalance.value.total_days) * 100)
  };
});

// Check if request is valid
const isValidRequest = computed(() => {
  return selectedLeaveType.value &&
         calculatedDays.value > 0 &&
         calculatedDays.value >= selectedLeaveType.value.min_duration &&
         calculatedDays.value <= selectedLeaveType.value.max_duration &&
         validationWarnings.value.length === 0 &&
         (!currentBalance.value || currentBalance.value.remaining_days >= calculatedDays.value);
});

// Get leave type details
const getLeaveTypeDetails = (leaveType: LeaveType) => {
  const details = [];
  
  if (leaveType.min_duration === leaveType.max_duration) {
    details.push(`${leaveType.min_duration} days`);
  } else {
    details.push(`${leaveType.min_duration}-${leaveType.max_duration} days`);
  }
  
  if (leaveType.requires_approval) {
    details.push('Requires approval');
  }
  
  if (leaveType.requires_documentation) {
    details.push('Documentation required');
  }
  
  return details.join(' • ');
};

// Get pay structure description
const getPayDescription = (fullPay: number, halfPay: number) => {
  if (fullPay === 0 && halfPay === 0) return 'Unpaid';
  if (halfPay > 0) {
    return `${fullPay} days full pay + ${halfPay} days half pay`;
  }
  return `${fullPay} days full pay`;
};

// Submit form
const submit = () => {
  if (!validateForm()) {
    toast.error('Please fix the validation errors before submitting');
    return;
  }
  
  form.post(route('leave-requests.store'), {
    preserveScroll: true,

    onSuccess: () => {
      toast.success('Leave request submitted successfully!');
    },

    onError: (serverErrors) => {
      // Handle server validation errors
      errors.value = { ...errors.value, ...serverErrors };
      toast.error('Please check the form for errors');
    }
  });
};

// File upload handler
const handleFileUpload = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files.length > 0) {
    form.documentation = target.files[0];
  }
};
</script>

<template>
  <Head title="Request Leave" />

  <AppLayout
    :breadcrumbs="[
      { title: 'Employee', href: route('dashboard') },
      { title: 'Leave Requests', href: route('leave-requests.index') },
      { title: 'Create Request', href: '#' }
    ]">
    <div class="max-w-4xl p-6 space-y-8">
      <!-- Header Section -->
      <div class="space-y-2">
        <h1 class="text-3xl font-bold tracking-tight">Request Leave</h1>
        <p class="text-lg text-muted-foreground">
          Submit a comprehensive leave request with all necessary details
        </p>
      </div>

      <!-- Main Content Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Form - Left Side (2/3) -->
        <div class="lg:col-span-2 space-y-6">
          <form @submit.prevent="submit" class="space-y-6">
            <!-- Leave Type Selection -->
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <CalendarIcon class="h-5 w-5" />
                  Leave Type Selection
                </CardTitle>
                <CardDescription>
                  Choose the type of leave you want to request
                </CardDescription>
              </CardHeader>
              <CardContent>
                <div class="space-y-6">
                  <div class="space-y-2">
                    <Label class="text-base font-semibold">Leave Type *</Label>
                    <Select v-model="form.type">
                      <SelectTrigger class="w-full">
                        <SelectValue placeholder="Select the type of leave you need" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem v-for="leaveType in props.leaveTypes" 
                                    :key="leaveType.value" 
                                    :value="leaveType.value">
                          <div class="flex flex-col items-start py-2">
                            <div class="font-medium">{{ leaveType.label }}</div>
                            <div class="text-sm text-muted-foreground">
                              {{ getLeaveTypeDetails(leaveType) }}
                            </div>
                            <div class="text-xs text-muted-foreground mt-1">
                              {{ getPayDescription(leaveType.full_pay_days, leaveType.half_pay_days) }}
                            </div>
                          </div>
                        </SelectItem>
                      </SelectContent>
                    </Select>
                    <InputError :message="errors.type" />
                  </div>

                  <!-- Selected Leave Type Details -->
                  <div v-if="selectedLeaveType" class="p-4 border rounded-lg bg-muted/50">
                    <h4 class="font-semibold text-sm mb-3">{{ selectedLeaveType.label }} Details</h4>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                      <div>
                        <span class="text-muted-foreground">Duration Range:</span>
                        <span class="ml-2 font-medium">
                          {{ selectedLeaveType.min_duration }}-{{ selectedLeaveType.max_duration }} days
                        </span>
                      </div>
                      <div>
                        <span class="text-muted-foreground">Annual Allowance:</span>
                        <span class="ml-2 font-medium">{{ selectedLeaveType.days_allowed }} days</span>
                      </div>
                      <div class="flex items-center gap-2">
                        <span class="text-muted-foreground">Approval:</span>
                        <Badge :variant="selectedLeaveType.requires_approval ? 'default' : 'secondary'">
                          {{ selectedLeaveType.requires_approval ? 'Required' : 'Not Required' }}
                        </Badge>
                      </div>
                      <div class="flex items-center gap-2">
                        <span class="text-muted-foreground">Documentation:</span>
                        <Badge :variant="selectedLeaveType.requires_documentation ? 'default' : 'secondary'">
                          {{ selectedLeaveType.requires_documentation ? 'Required' : 'Optional' }}
                        </Badge>
                      </div>
                    </div>
                    <div v-if="selectedLeaveType.description" class="mt-3 text-sm text-muted-foreground">
                      {{ selectedLeaveType.description }}
                    </div>
                  </div>

                  <!-- Validation Warnings -->
                  <div v-if="validationWarnings.length > 0" class="space-y-2">
                    <div v-for="warning in validationWarnings" :key="warning" 
                          class="flex items-start gap-2 p-3 border border-amber-200 bg-amber-50 rounded-lg">
                      <AlertCircleIcon class="h-4 w-4 text-amber-600 mt-0.5 flex-shrink-0" />
                      <span class="text-sm text-amber-800">{{ warning }}</span>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Date Selection -->
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <ClockIcon class="h-5 w-5" />
                  Leave Dates
                </CardTitle>
                <CardDescription>
                  Select your leave start and end dates
                </CardDescription>
              </CardHeader>
              <CardContent>
                <div class="space-y-6">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                      <Label class="text-base font-semibold">Start Date *</Label>
                      <DatePicker v-model="startDateValue" placeholder="Select start date" />
                      <InputError :message="errors.start_date" />
                    </div>

                    <div class="space-y-2">
                      <Label class="text-base font-semibold">End Date *</Label>
                      <DatePicker v-model="endDateValue" placeholder="Select end date" />
                      <InputError :message="errors.end_date" />
                    </div>
                  </div>

                  <!-- Days Calculation -->
                  <div v-if="calculatedDays > 0" class="p-4 border rounded-lg bg-blue-50 border-blue-200">
                    <div class="flex items-center justify-between">
                      <div class="flex items-center gap-2">
                        <InfoIcon class="h-4 w-4 text-blue-600" />
                        <span class="font-medium text-blue-900">Total Leave Duration</span>
                      </div>
                      <div class="text-right">
                        <div class="text-2xl font-bold text-blue-900">{{ calculatedDays }}</div>
                        <div class="text-sm text-blue-700">{{ calculatedDays === 1 ? 'day' : 'days' }}</div>
                      </div>
                    </div>
                    
                    <div v-if="selectedLeaveType" class="mt-3 flex items-center justify-between text-sm">
                      <span class="text-blue-700">Duration limits for {{ selectedLeaveType.label }}:</span>
                      <span class="font-medium text-blue-900">
                        {{ selectedLeaveType.min_duration }}-{{ selectedLeaveType.max_duration }} days
                      </span>
                    </div>
                    
                    <!-- Duration validation error -->
                    <InputError :message="errors.duration" class="mt-2" />
                  </div>

                  <!-- Conflict Warning -->
                  <div v-if="conflicts.length > 0" class="space-y-2">
                    <div class="flex items-center gap-2 text-amber-800">
                      <UsersIcon class="h-4 w-4" />
                      <span class="font-medium">Team Conflicts Detected</span>
                    </div>
                    <div v-for="conflict in conflicts" :key="conflict.id" 
                        class="p-3 border border-amber-200 bg-amber-50 rounded-lg">
                      <div class="flex items-center justify-between">
                        <span class="font-medium">{{ conflict.employee_name }}</span>
                        <Badge variant="outline">{{ conflict.status }}</Badge>
                      </div>
                      <div class="text-sm text-muted-foreground mt-1">
                        {{ conflict.start_date }} - {{ conflict.end_date }} ({{ conflict.days }} days)
                      </div>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Reason & Documentation -->
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <FileTextIcon class="h-5 w-5" />
                  Additional Details
                </CardTitle>
                <CardDescription>
                  Provide reason and any required documentation
                </CardDescription>
              </CardHeader>
              <CardContent>
                <div class="space-y-6">
                  <div class="space-y-2">
                    <Label class="text-base font-semibold">Reason for Leave *</Label>
                    <Textarea v-model="form.reason" 
                              placeholder="Please provide a detailed reason for your leave request. This helps managers understand the purpose and urgency of your request."
                              class="min-h-[120px] resize-none" />
                    <p class="text-sm text-muted-foreground">
                      Minimum 10 characters required. Be specific about the purpose of your leave.
                    </p>
                    <InputError :message="errors.reason" />
                  </div>

                  <!-- Documentation Upload -->
                  <div v-if="selectedLeaveType?.requires_documentation" class="space-y-2">
                    <Label class="text-base font-semibold">
                      Required Documentation *
                      <span v-if="selectedLeaveType.documentation_type" class="text-sm font-normal text-muted-foreground">
                        ({{ selectedLeaveType.documentation_type.replace('_', ' ') }})
                      </span>
                    </Label>
                    <Input type="file" 
                          accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" 
                          @change="handleFileUpload"
                          class="file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-primary-foreground hover:file:bg-primary/90" />
                    <p class="text-sm text-muted-foreground">
                      Upload supporting documents (PDF, Word, or Image files, max 5MB)
                    </p>
                    <InputError :message="errors.documentation" />
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Form Actions -->
            <div class="flex gap-4 pt-4">
              <Button type="submit" 
                      :disabled="form.processing || !isValidRequest"
                      class="flex-1 h-12 text-base font-semibold">
                <CheckCircleIcon v-if="!form.processing" class="h-4 w-4 mr-2" />
                {{ form.processing ? 'Submitting Request...' : 'Submit Leave Request' }}
              </Button>
              <Button type="button" variant="outline" as-child class="h-12 px-8">
                <Link :href="route('leave-requests.index')">
                  <XCircleIcon class="h-4 w-4 mr-2" />
                  Cancel
                </Link>
              </Button>
            </div>
          </form>
        </div>

        <!-- Sidebar - Right Side (1/3) -->
        <div class="lg:col-span-1 space-y-6">
          <!-- Leave Balance Summary -->
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center gap-2 text-lg">
                <TrendingUpIcon class="h-5 w-5" />
                Your Leave Balance
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div class="space-y-4">
                <div v-for="balance in props.leaveBalance" :key="balance.type" class="space-y-2">
                  <div class="flex items-center justify-between">
                    <span class="font-medium capitalize">{{ balance.type }} Leave</span>
                    <span class="text-sm text-muted-foreground">
                      {{ balance.remaining_days }}/{{ balance.total_days }} days
                    </span>
                  </div>
                  <Progress 
                    v-model="balance.percentage_used" 
                    :class="balance.percentage_used > 80 ? 'text-red-500' : 
                    balance.percentage_used > 60 ? 'text-amber-500' : 'text-green-500'" />
                  <div class="text-xs text-muted-foreground">
                    {{ balance.used_days }} used, {{ balance.pending_days }} pending
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Current Request Impact -->
          <Card v-if="currentBalance && calculatedDays > 0">
            <CardHeader>
              <CardTitle class="text-lg">Request Impact</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="space-y-4">
                <div class="flex items-center justify-between">
                  <span class="text-sm">Current Remaining:</span>
                  <span class="font-medium">{{ currentBalance.remaining_days }} days</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-sm">This Request:</span>
                  <span class="font-medium">{{ calculatedDays }} days</span>
                </div>
                <Separator />
                <div class="flex items-center justify-between">
                  <span class="text-sm font-medium">After Approval:</span>
                  <span class="font-bold" :class="projectedBalance && projectedBalance.remaining_days < 0 ? 'text-red-600' : 'text-green-600'">
                    {{ projectedBalance?.remaining_days || 0 }} days
                  </span>
                </div>
                
                <div v-if="projectedBalance" class="mt-4">
                  <Progress 
                    v-model="projectedBalance.percentage_used" 
                    :class="projectedBalance.percentage_used > 100 ? 'text-red-500' : 
                    projectedBalance.percentage_used > 80 ? 'text-amber-500' : 'text-green-500'" 
                  />
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Quick Tips -->
          <Card>
            <CardHeader>
              <CardTitle class="text-lg">💡 Quick Tips</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="space-y-3 text-sm">
                <div class="flex items-start gap-2">
                  <CheckCircleIcon class="h-4 w-4 text-green-600 mt-0.5 flex-shrink-0" />
                  <span>Submit requests at least 2 weeks in advance for better approval chances</span>
                </div>
                <div class="flex items-start gap-2">
                  <CheckCircleIcon class="h-4 w-4 text-green-600 mt-0.5 flex-shrink-0" />
                  <span>Check with your team before requesting leave during busy periods</span>
                </div>
                <div class="flex items-start gap-2">
                  <CheckCircleIcon class="h-4 w-4 text-green-600 mt-0.5 flex-shrink-0" />
                  <span>Keep documentation ready for medical or emergency leave</span>
                </div>
                <div class="flex items-start gap-2">
                  <InfoIcon class="h-4 w-4 text-blue-600 mt-0.5 flex-shrink-0" />
                  <span>You can edit pending requests until they are approved</span>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Emergency Contact -->
          <Card v-if="selectedLeaveType?.requires_approval">
            <CardHeader>
              <CardTitle class="text-lg">Need Help?</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="space-y-2 text-sm">
                <p class="text-muted-foreground">
                  For urgent leave requests or questions about policies:
                </p>
                <div class="space-y-1">
                  <div>📧 <strong>HR Team:</strong> hr@company.com</div>
                  <div>📞 <strong>Manager:</strong> {{ props.userProfile.team_name || 'Contact your direct manager' }}</div>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
