<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, computed, onMounted, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import AppLayout from '@/layouts/AppLayout.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import CalendarDayCell from '@/components/calendar/CalendarDayCell.vue';
import CalendarEvent from '@/components/calendar/CalendarEvent.vue';
import CalendarWeekView from '@/components/calendar/CalendarWeekView.vue';
import CalendarMobileView from '@/components/calendar/CalendarMobileView.vue';
import CalendarStats from '@/components/calendar/CalendarStats.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import InputError from '@/components/InputError.vue';
import {
  Calendar as CalendarIcon,
  ChevronLeft,
  ChevronRight,
  Plus,
  Eye,
  Users,
  Grid3X3,
  List,
  Clock,
  CheckCircle2,
  XCircle,
  AlertCircle,
  User,
  Filter,
  Download,
  Settings,
  CalendarDays,
  ChevronUp,
  ChevronDown
} from 'lucide-vue-next';
import { format, addDays, subDays, addWeeks, subWeeks, addMonths, subMonths, startOfWeek, endOfWeek, startOfMonth, endOfMonth, isSameMonth, isSameDay, isToday, parseISO } from 'date-fns';
import DatePicker from '@/components/DatePicker.vue';

interface Props {
  currentDate: string;
  view: 'month' | 'week';
  calendarData: Array<{
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
  }>;
  teamData: Array<any>;
  leaveTypes: string[];
  userPermissions: {
    canViewTeam: boolean;
    canCreateLeave: boolean;
    canApproveLeave: boolean;
  };
}

const props = defineProps<Props>();

const breadcrumbs = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Calendar', href: '/calendar' }
];

// Reactive state
const currentDate = ref(parseISO(props.currentDate));
const currentView = ref<'month' | 'week'>(props.view);
const selectedEvent = ref<any>(null);
const showEventDetails = ref(false);
const showCreateForm = ref(false);
const showTeamCalendar = ref(false);
const selectedFilters = ref<string[]>(['approved', 'pending']);

// Calendar navigation
const navigateCalendar = (direction: 'prev' | 'next') => {
  let newDate: Date;
  
  if (currentView.value === 'month') {
    newDate = direction === 'next' 
      ? addMonths(currentDate.value, 1)
      : subMonths(currentDate.value, 1);
  } else {
    newDate = direction === 'next'
      ? addWeeks(currentDate.value, 1)
      : subWeeks(currentDate.value, 1);
  }
  
  currentDate.value = newDate;
  updateCalendar();
};

const goToToday = () => {
  currentDate.value = new Date();
  updateCalendar();
};

const changeView = (view: 'month' | 'week') => {
  currentView.value = view;
  updateCalendar();
};

const updateCalendar = () => {
  router.get(route('calendar.index'), {
    date: format(currentDate.value, 'yyyy-MM-dd'),
    view: currentView.value,
  }, {
    preserveState: true,
    preserveScroll: true,
  });
};

// Calendar data computed properties
const calendarTitle = computed(() => {
  if (currentView.value === 'month') {
    return format(currentDate.value, 'MMMM yyyy');
  } else {
    const start = startOfWeek(currentDate.value);
    const end = endOfWeek(currentDate.value);
    
    if (isSameMonth(start, end)) {
      return format(start, 'MMMM dd') + ' - ' + format(end, 'dd, yyyy');
    } else {
      return format(start, 'MMM dd') + ' - ' + format(end, 'MMM dd, yyyy');
    }
  }
});

const filteredEvents = computed(() => {
  return props.calendarData.filter(event => 
    selectedFilters.value.includes(event.status)
  );
});

const teamEvents = computed(() => {
  if (!showTeamCalendar.value || !props.userPermissions.canViewTeam) {
    return [];
  }
  return props.teamData.filter(event => 
    selectedFilters.value.includes(event.status)
  );
});

// Month view calendar grid
const monthDays = computed(() => {
  const start = startOfMonth(currentDate.value);
  const end = endOfMonth(currentDate.value);
  const startDate = startOfWeek(start);
  const endDate = endOfWeek(end);
  
  const days = [];
  let current = startDate;
  
  while (current <= endDate) {
    const dayEvents = filteredEvents.value.filter(event => {
      const eventStart = parseISO(event.start);
      const eventEnd = parseISO(event.end);
      return current >= eventStart && current < eventEnd;
    });
    
    const teamDayEvents = teamEvents.value.filter(event => {
      const eventStart = parseISO(event.start);
      const eventEnd = parseISO(event.end);
      return current >= eventStart && current < eventEnd;
    });
    
    days.push({
      date: new Date(current),
      isCurrentMonth: isSameMonth(current, currentDate.value),
      isToday: isToday(current),
      events: dayEvents,
      teamEvents: teamDayEvents,
    });
    
    current = addDays(current, 1);
  }
  
  return days;
});

// Week view calendar grid
const weekDays = computed(() => {
  const start = startOfWeek(currentDate.value);
  const days = [];
  
  for (let i = 0; i < 7; i++) {
    const date = addDays(start, i);
    const dayEvents = filteredEvents.value.filter(event => {
      const eventStart = parseISO(event.start);
      const eventEnd = parseISO(event.end);
      return date >= eventStart && date < eventEnd;
    });
    
    const teamDayEvents = teamEvents.value.filter(event => {
      const eventStart = parseISO(event.start);
      const eventEnd = parseISO(event.end);
      return date >= eventStart && date < eventEnd;
    });
    
    days.push({
      date,
      isToday: isToday(date),
      events: dayEvents,
      teamEvents: teamDayEvents,
    });
  }
  
  return days;
});

// Event handlers
const openEventDetails = (event: any) => {
  selectedEvent.value = event;
  showEventDetails.value = true;
};

const openDayView = (date: Date) => {
  // Could open a day detail view or create event dialog
  if (props.userPermissions.canCreateLeave) {
    resetForm(); // Clear any existing form data
    newLeaveForm.value.start_date = format(date, 'yyyy-MM-dd');
    newLeaveForm.value.end_date = format(date, 'yyyy-MM-dd');
    showCreateForm.value = true;
  }
};

const openCreateDialog = () => {
  resetForm(); // Clear any existing form data
  // Set default dates to today if not already set
  if (!newLeaveForm.value.start_date) {
    const today = format(new Date(), 'yyyy-MM-dd');
    newLeaveForm.value.start_date = today;
    newLeaveForm.value.end_date = today;
  }
  showCreateForm.value = true;
};

const getStatusIcon = (status: string) => {
  switch (status) {
    case 'approved': return CheckCircle2;
    case 'pending': return Clock;
    case 'rejected': return XCircle;
    default: return AlertCircle;
  }
};

const getStatusColor = (status: string) => {
  switch (status) {
    case 'approved': return 'default';
    case 'pending': return 'secondary';
    case 'rejected': return 'destructive';
    default: return 'outline';
  }
};

const toggleFilter = (status: string) => {
  const index = selectedFilters.value.indexOf(status);
  if (index > -1) {
    selectedFilters.value.splice(index, 1);
  } else {
    selectedFilters.value.push(status);
  }
};

// New leave request form
const newLeaveForm = ref({
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

// Form validation
const validateForm = () => {
  formErrors.value = { type: '', start_date: '', end_date: '', reason: '' };
  let isValid = true;

  // Validate leave type
  if (!newLeaveForm.value.type) {
    formErrors.value.type = 'Please select a leave type';
    isValid = false;
  }

  // Validate start date
  if (!newLeaveForm.value.start_date) {
    formErrors.value.start_date = 'Please select a start date';
    isValid = false;
  }

  // Validate end date
  if (!newLeaveForm.value.end_date) {
    formErrors.value.end_date = 'Please select an end date';
    isValid = false;
  }

  // Validate end date is not before start date
  if (newLeaveForm.value.start_date && newLeaveForm.value.end_date) {
    const startDate = parseISO(newLeaveForm.value.start_date);
    const endDate = parseISO(newLeaveForm.value.end_date);
    
    if (endDate < startDate) {
      formErrors.value.end_date = 'End date cannot be earlier than start date';
      isValid = false;
    }
  }

  // Validate reason (now required)
  if (!newLeaveForm.value.reason || newLeaveForm.value.reason.trim().length === 0) {
    formErrors.value.reason = 'Please provide a reason for your leave request';
    isValid = false;
  }

  return isValid;
};

const createLeaveRequest = () => {
  if (!validateForm()) {
    toast.error('Please fix the errors in the form');
    return;
  }

  isSubmitting.value = true;

  router.post(route('leave-requests.store'), newLeaveForm.value, {
    onSuccess: () => {
      toast.success('Leave request created successfully!');
      showCreateForm.value = false;
      resetForm();
      // Refresh the calendar to show the new request
      updateCalendar();
    },
    onError: (errors) => {
      console.error('Leave request errors:', errors);
      
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
        toast.error('Error creating leave request. Please check your inputs and try again.');
      }
    },
    onFinish: () => {
      isSubmitting.value = false;
    }
  });
};

const resetForm = () => {
  newLeaveForm.value = { type: '', start_date: '', end_date: '', reason: '' };
  formErrors.value = { type: '', start_date: '', end_date: '', reason: '' };
};

// Watch for start date changes to clear end date validation errors
watch(() => newLeaveForm.value.start_date, (newStartDate) => {
  // Clear start date error when valid date is selected
  if (newStartDate && formErrors.value.start_date) {
    formErrors.value.start_date = '';
  }
  
  if (formErrors.value.end_date && newStartDate && newLeaveForm.value.end_date) {
    // Re-validate end date when start date changes
    const startDate = parseISO(newStartDate);
    const endDate = parseISO(newLeaveForm.value.end_date);
    
    if (endDate >= startDate) {
      formErrors.value.end_date = '';
    }
  }
});

// Watch for end date changes to validate against start date
watch(() => newLeaveForm.value.end_date, (newEndDate) => {
  if (newEndDate && newLeaveForm.value.start_date) {
    const startDate = parseISO(newLeaveForm.value.start_date);
    const endDate = parseISO(newEndDate);
    
    if (endDate < startDate) {
      formErrors.value.end_date = 'End date cannot be earlier than start date';
    } else {
      formErrors.value.end_date = '';
    }
  }
});

// Watch for other field changes to clear their errors
watch(() => newLeaveForm.value.type, (newType) => {
  if (newType && formErrors.value.type) {
    formErrors.value.type = '';
  }
});

watch(() => newLeaveForm.value.reason, (newReason) => {
  if (newReason && newReason.trim().length > 0 && formErrors.value.reason) {
    formErrors.value.reason = '';
  }
});

// Watch for dialog open/close to reset form
watch(showCreateForm, (isOpen) => {
  if (!isOpen) {
    resetForm();
  }
});

onMounted(() => {
  // Any initialization logic
});
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Calendar" />

    <div class="space-y-6 p-6">
      <!-- Header with controls -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <HeadingSmall
          title="Leave Calendar"
          :description="`${calendarTitle} - ${currentView === 'month' ? 'Monthly' : 'Weekly'} View`"
        />
        
        <div class="flex flex-wrap items-center gap-2">
          <!-- View Toggle -->
          <div class="flex items-center border rounded-lg p-1">
            <Button
              variant="ghost"
              size="sm"
              :class="currentView === 'month' ? 'bg-muted' : ''"
              @click="changeView('month')"
            >
              <Grid3X3 class="h-4 w-4 mr-1" />
              Month
            </Button>
            <Button
              variant="ghost"
              size="sm"
              :class="currentView === 'week' ? 'bg-muted' : ''"
              @click="changeView('week')"
            >
              <List class="h-4 w-4 mr-1" />
              Week
            </Button>
          </div>

          <!-- Calendar Navigation -->
          <div class="flex items-center gap-1">
            <Button variant="outline" size="sm" @click="navigateCalendar('prev')">
              <ChevronLeft class="h-4 w-4" />
            </Button>
            <Button variant="outline" size="sm" @click="goToToday">
              Today
            </Button>
            <Button variant="outline" size="sm" @click="navigateCalendar('next')">
              <ChevronRight class="h-4 w-4" />
            </Button>
          </div>

          <!-- Actions -->
          <Dialog v-model:open="showCreateForm" v-if="props.userPermissions.canCreateLeave">
            <Button size="sm" @click="openCreateDialog">
              <Plus class="h-4 w-4 mr-1" />
              New Leave
            </Button>
            <DialogContent>
              <DialogHeader>
                <DialogTitle>Create Leave Request</DialogTitle>
                <DialogDescription>
                  Submit a new leave request for approval.
                </DialogDescription>
              </DialogHeader>
              <div class="space-y-6">
                <div class="space-y-2">
                  <Label for="type">Leave Type</Label>
                  <Select v-model="newLeaveForm.type" required>
                    <SelectTrigger class="w-full is-large">
                      <SelectValue placeholder="Select leave type" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem
                        v-for="type in props.leaveTypes"
                        :key="type"
                        :value="type"
                      >
                        {{ type }}
                      </SelectItem>
                    </SelectContent>
                  </Select>
                  <InputError :message="formErrors.type" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                  <div class="space-y-2">
                    <Label for="start_date">Start Date</Label>
                    <Input
                      id="start_date"
                      type="date"
                      v-model="newLeaveForm.start_date"
                      :min="format(new Date(), 'yyyy-MM-dd')"
                      required
                    />
                    <InputError :message="formErrors.start_date" />
                  </div>
                  
                  <div class="space-y-2">
                    <Label for="end_date">End Date</Label>
                    <Input
                      id="end_date"
                      type="date"
                      v-model="newLeaveForm.end_date"
                      :min="newLeaveForm.start_date || format(new Date(), 'yyyy-MM-dd')"
                      required
                    />
                    <InputError :message="formErrors.end_date" />
                  </div>
                </div>
                <div class="space-y-2">
                  <Label for="reason">Reason for Leave <span class="text-red-500">*</span></Label>
                  <Textarea
                    id="reason"
                    v-model="newLeaveForm.reason"
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
              <DialogFooter>
                <Button variant="outline" @click="showCreateForm = false" :disabled="isSubmitting">
                  Cancel
                </Button>
                <Button @click="createLeaveRequest" :disabled="isSubmitting">
                  <div v-if="isSubmitting" class="flex items-center gap-2">
                    <div class="animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent"></div>
                    <span>Submitting...</span>
                  </div>
                  <span v-else>Submit Request</span>
                </Button>
              </DialogFooter>
            </DialogContent>
          </Dialog>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Stats Sidebar -->
        <div class="lg:col-span-1 order-2 lg:order-1">
          <CalendarStats
            :events="filteredEvents"
            :team-events="teamEvents"
            :show-team-stats="props.userPermissions.canViewTeam && showTeamCalendar"
            :current-period="calendarTitle"
          />
        </div>

        <!-- Main Calendar -->
        <div class="lg:col-span-3 order-1 lg:order-2 space-y-6">
          <!-- Filters and Options -->
          <Card>
            <CardContent class="p-4">
              <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                  <div class="flex items-center gap-2">
                    <Filter class="h-4 w-4 text-muted-foreground" />
                    <span class="text-sm font-medium">Status Filters:</span>
                  </div>
                  
                  <div class="flex gap-2">
                    <Button
                      variant="outline"
                      size="sm"
                      :class="selectedFilters.includes('approved') ? 'bg-green-100 border-green-300 text-green-700' : ''"
                      @click="toggleFilter('approved')"
                    >
                      <CheckCircle2 class="h-3 w-3 mr-1" />
                      Approved
                    </Button>
                    <Button
                      variant="outline"
                      size="sm"
                      :class="selectedFilters.includes('pending') ? 'bg-yellow-100 border-yellow-300 text-yellow-700' : ''"
                      @click="toggleFilter('pending')"
                    >
                      <Clock class="h-3 w-3 mr-1" />
                      Pending
                    </Button>
                    <Button
                      variant="outline"
                      size="sm"
                      :class="selectedFilters.includes('rejected') ? 'bg-red-100 border-red-300 text-red-700' : ''"
                      @click="toggleFilter('rejected')"
                    >
                      <XCircle class="h-3 w-3 mr-1" />
                      Rejected
                    </Button>
                  </div>
                </div>

                <div class="flex items-center gap-2" v-if="props.userPermissions.canViewTeam">
                  <Button
                    variant="outline"
                    size="sm"
                    :class="showTeamCalendar ? 'bg-blue-100 border-blue-300 text-blue-700' : ''"
                    @click="showTeamCalendar = !showTeamCalendar"
                  >
                    <Users class="h-3 w-3 mr-1" />
                    Team View
                  </Button>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Calendar Grid -->
      <div class="block lg:hidden">
        <!-- Mobile View -->
        <CalendarMobileView
          :events="filteredEvents"
          :team-events="teamEvents"
          :show-team-events="showTeamCalendar"
          @event-click="openEventDetails"
        />
      </div>

      <Card class="hidden lg:block">
        <CardContent class="p-0">
          <!-- Month View -->
          <div v-if="currentView === 'month'" class="grid grid-cols-7 border-b">
            <!-- Week header -->
            <div
              v-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']"
              :key="day"
              class="p-3 text-center font-medium text-sm border-r last:border-r-0 bg-muted/50"
            >
              {{ day }}
            </div>
            
            <!-- Month days -->
            <CalendarDayCell
              v-for="day in monthDays"
              :key="day.date.getTime()"
              :date="day.date"
              :events="day.events"
              :team-events="day.teamEvents"
              :is-today="day.isToday"
              :is-current-month="day.isCurrentMonth"
              :show-team-events="showTeamCalendar"
              :max-visible="2"
              @event-click="openEventDetails"
              @day-click="openDayView"
            />
          </div>

          <!-- Week View -->
          <CalendarWeekView
            v-else
            :current-date="currentDate"
            :events="filteredEvents"
            :team-events="teamEvents"
            :show-team-events="showTeamCalendar"
            @event-click="openEventDetails"
            @date-click="openDayView"
            @prev-week="navigateCalendar('prev')"
            @next-week="navigateCalendar('next')"
          />
        </CardContent>
      </Card>
        </div>
      </div>

      <!-- Event Details Dialog -->
      <Dialog v-model:open="showEventDetails">
        <DialogContent v-if="selectedEvent">
          <DialogHeader>
            <DialogTitle class="flex items-center gap-2">
              <component :is="getStatusIcon(selectedEvent.status)" class="h-5 w-5" />
              {{ selectedEvent.title }}
            </DialogTitle>
            <DialogDescription>
              Leave request details and information
            </DialogDescription>
          </DialogHeader>
          
          <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <Label class="text-sm font-medium">Status</Label>
                <div class="mt-1">
                  <Badge :variant="getStatusColor(selectedEvent.status)">
                    <component :is="getStatusIcon(selectedEvent.status)" class="h-3 w-3 mr-1" />
                    {{ selectedEvent.status }}
                  </Badge>
                </div>
              </div>
              <div>
                <Label class="text-sm font-medium">Leave Type</Label>
                <div class="mt-1 text-sm">{{ selectedEvent.type }}</div>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <Label class="text-sm font-medium">Start Date</Label>
                <div class="mt-1 text-sm">{{ format(parseISO(selectedEvent.start), 'MMM dd, yyyy') }}</div>
              </div>
              <div>
                <Label class="text-sm font-medium">End Date</Label>
                <div class="mt-1 text-sm">{{ format(parseISO(selectedEvent.end), 'MMM dd, yyyy') }}</div>
              </div>
            </div>

            <div>
              <Label class="text-sm font-medium">Duration</Label>
              <div class="mt-1 text-sm">{{ selectedEvent.days }} {{ selectedEvent.days === 1 ? 'day' : 'days' }}</div>
            </div>

            <div v-if="selectedEvent.reason">
              <Label class="text-sm font-medium">Reason</Label>
              <div class="mt-1 text-sm p-2 bg-muted rounded">{{ selectedEvent.reason }}</div>
            </div>

            <div>
              <Label class="text-sm font-medium">Applied On</Label>
              <div class="mt-1 text-sm">{{ selectedEvent.extendedProps.appliedAt }}</div>
            </div>

            <div v-if="!selectedEvent.extendedProps.isOwnRequest">
              <Label class="text-sm font-medium">Employee</Label>
              <div class="mt-1 text-sm flex items-center gap-1">
                <User class="h-4 w-4" />
                {{ selectedEvent.user_name }}
              </div>
            </div>
          </div>

          <DialogFooter>
            <Button variant="outline" @click="showEventDetails = false">
              Close
            </Button>
            <Button 
              v-if="selectedEvent.extendedProps.isOwnRequest"
              @click="router.visit(route('leave-requests.show', selectedEvent.id))"
            >
              <Eye class="h-4 w-4 mr-1" />
              View Details
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  </AppLayout>
</template>

<style scoped>
/* Additional custom styles if needed */
.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
}

.day-cell {
  aspect-ratio: 1;
  min-height: 120px;
}

@media (max-width: 768px) {
  .day-cell {
    min-height: 80px;
  }
}
</style>
