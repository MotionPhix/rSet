<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { 
  AlertDialog, 
  AlertDialogContent, 
  AlertDialogDescription, 
  AlertDialogFooter, 
  AlertDialogHeader, 
  AlertDialogTitle, 
} from '@/components/ui/alert-dialog';
import InputError from '@/components/InputError.vue';
import DatePicker from '@/components/DatePicker.vue';
import { format } from 'date-fns';

interface Event {
  id: number;
  title: string;
  start: string;
  end: string;
  type: string;
  status: string;
  reason?: string;
  user_name: string;
  user_id: number;
  days: number;
  color: string;
  backgroundColor: string;
  borderColor: string;
  allDay: boolean;
  extendedProps: {
    status: string;
    type: string;
    reason?: string;
    appliedAt: string;
    isOwnRequest: boolean;
  };
}

interface Props {
  show: boolean;
  event: Event | null;
  leaveTypes: Array<{
    value: string;
    label: string;
  }>;
}

const props = defineProps<Props>();

const emit = defineEmits<{
  'update:show': [value: boolean];
  'updated': [];
}>();

// Form state
const editForm = ref({
  type: '',
  start_date: '',
  end_date: '',
  reason: '',
});

const formErrors = ref({
  type: '',
  start_date: '',
  end_date: '',
  reason: '',
});

const isSubmitting = ref(false);

// Populate form when event changes
watch(() => props.event, (newEvent) => {
  if (newEvent) {
    editForm.value = {
      type: newEvent.type,
      start_date: newEvent.start,
      end_date: newEvent.end,
      reason: newEvent.reason || '',
    };
  }
}, { immediate: true });

// Clear errors when dialog closes
watch(() => props.show, (isOpen) => {
  if (!isOpen) {
    formErrors.value = { type: '', start_date: '', end_date: '', reason: '' };
  }
});

// Form validation
const validateForm = () => {
  formErrors.value = { type: '', start_date: '', end_date: '', reason: '' };
  let isValid = true;

  if (!editForm.value.type) {
    formErrors.value.type = 'Please select a leave type';
    isValid = false;
  }

  if (!editForm.value.start_date) {
    formErrors.value.start_date = 'Please select a start date';
    isValid = false;
  }

  if (!editForm.value.end_date) {
    formErrors.value.end_date = 'Please select an end date';
    isValid = false;
  }

  if (editForm.value.start_date && editForm.value.end_date) {
    const startDate = new Date(editForm.value.start_date);
    const endDate = new Date(editForm.value.end_date);
    
    if (startDate && endDate && endDate < startDate) {
      formErrors.value.end_date = 'End date cannot be earlier than start date';
      isValid = false;
    }
  }

  if (!editForm.value.reason || editForm.value.reason.trim().length === 0) {
    formErrors.value.reason = 'Please provide a reason for your leave request';
    isValid = false;
  }

  return isValid;
};

// Submit form
const updateLeaveRequest = () => {
  if (!props.event || !validateForm()) {
    if (!validateForm()) {
      toast.error('Please fix the errors in the form');
    }
    return;
  }

  isSubmitting.value = true;

  router.put(route('leave-requests.update', props.event.id), editForm.value, {
    onSuccess: () => {
      toast.success('Leave request updated successfully!');
      emit('update:show', false);
      emit('updated');
    },
    onError: (errors) => {
      console.error('Leave request update errors:', errors);
      
      // Handle backend validation errors
      if (errors.start_date) {
        formErrors.value.start_date = errors.start_date;
        toast.error(errors.start_date);
      }
      
      if (errors.end_date) {
        formErrors.value.end_date = errors.end_date;
        toast.error(errors.end_date);
      }
      
      if (errors.type) {
        formErrors.value.type = errors.type;
        toast.error(errors.type);
      }
      
      if (errors.reason) {
        formErrors.value.reason = errors.reason;
        toast.error(errors.reason);
      }
      
      // Show first error message or generic message
      const firstError = Object.values(errors)[0];
      if (!firstError) {
        toast.error('Error updating leave request. Please check your inputs and try again.');
      }
    },
    onFinish: () => {
      isSubmitting.value = false;
    }
  });
};

// Close dialog
const closeDialog = () => {
  emit('update:show', false);
};

// Computed properties
const dialogTitle = computed(() => 
  props.event ? `Edit Leave Request` : 'Edit Leave Request'
);

const isEditable = computed(() => 
  props.event?.status === 'pending' && props.event?.extendedProps?.isOwnRequest
);
</script>

<template>
  <AlertDialog :open="show" @update:open="emit('update:show', $event)">
    <AlertDialogContent v-if="event">
      <AlertDialogHeader>
        <AlertDialogTitle>{{ dialogTitle }}</AlertDialogTitle>
        <AlertDialogDescription>
          <span v-if="isEditable">
            Update your pending leave request. You can modify dates, leave type, and reason.
          </span>
          <span v-else>
            This leave request cannot be edited because it has been {{ event.status }}.
          </span>
        </AlertDialogDescription>
      </AlertDialogHeader>

      <div v-if="isEditable" class="space-y-6">
        <div class="space-y-2">
          <Label for="edit_type">Leave Type</Label>
          <Select v-model="editForm.type" required>
            <SelectTrigger class="w-full">
              <SelectValue placeholder="Select leave type" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="type in leaveTypes"
                :key="type.value"
                :value="type.value"
              >
                {{ type.label }}
              </SelectItem>
            </SelectContent>
          </Select>
          <InputError :message="formErrors.type" />
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-2">
            <Label for="edit_start_date">Start Date</Label>
            <DatePicker
              id="edit_start_date"
              v-model="editForm.start_date"
              :min="format(new Date(), 'yyyy-MM-dd')"
              required
            />
            <InputError :message="formErrors.start_date" />
          </div>
          
          <div class="space-y-2">
            <Label for="edit_end_date">End Date</Label>
            <DatePicker
              id="edit_end_date"
              v-model="editForm.end_date"
              :min="editForm.start_date || format(new Date(), 'yyyy-MM-dd')"
              required
            />
            <InputError :message="formErrors.end_date" />
          </div>
        </div>

        <div class="space-y-2">
          <Label for="edit_reason">Reason for Leave <span class="text-red-500">*</span></Label>
          <Textarea
            id="edit_reason"
            v-model="editForm.reason"
            placeholder="Please provide a reason for your leave request..."
            rows="3"
            required
          />
          <InputError :message="formErrors.reason" />
          <p class="text-xs text-muted-foreground">
            Please provide a clear reason for your leave request to help with approval.
          </p>
        </div>
      </div>

      <!-- Read-only view for non-editable requests -->
      <div v-else class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <Label class="text-sm font-medium">Leave Type</Label>
            <div class="mt-1 text-sm">{{ event.type }}</div>
          </div>
          <div>
            <Label class="text-sm font-medium">Status</Label>
            <div class="mt-1 text-sm capitalize">{{ event.status }}</div>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <Label class="text-sm font-medium">Start Date</Label>
            <div class="mt-1 text-sm">{{ event.start }}</div>
          </div>
          <div>
            <Label class="text-sm font-medium">End Date</Label>
            <div class="mt-1 text-sm">{{ event.end }}</div>
          </div>
        </div>

        <div v-if="event.reason">
          <Label class="text-sm font-medium">Reason</Label>
          <div class="mt-1 text-sm p-2 bg-muted rounded">{{ event.reason }}</div>
        </div>
      </div>

      <AlertDialogFooter>
        <Button variant="outline" @click="closeDialog" :disabled="isSubmitting">
          {{ isEditable ? 'Cancel' : 'Close' }}
        </Button>
        <Button 
          v-if="isEditable"
          @click="updateLeaveRequest" 
          :disabled="isSubmitting"
        >
          <div v-if="isSubmitting" class="flex items-center gap-2">
            <div class="animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent"></div>
            <span>Updating...</span>
          </div>
          <span v-else>Update Request</span>
        </Button>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
</template>
