<template>
  <AdminLayout>
    <Head title="Calendar" />
    
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900">Company Calendar</h1>
          <p class="text-sm text-gray-600">View and manage all company leave requests</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
          <!-- View Toggle -->
          <div class="flex items-center bg-gray-100 rounded-lg p-1">
            <Button
              :variant="currentView === 'month' ? 'default' : 'ghost'"
              size="sm"
              @click="currentView = 'month'"
              class="px-3 py-1.5 text-xs"
            >
              <Calendar class="h-3 w-3 mr-1" />
              Month
            </Button>
            <Button
              :variant="currentView === 'week' ? 'default' : 'ghost'"
              size="sm"
              @click="currentView = 'week'"
              class="px-3 py-1.5 text-xs"
            >
              <CalendarDays class="h-3 w-3 mr-1" />
              Week
            </Button>
          </div>

          <!-- Team Filter -->
          <Select v-model="selectedTeam">
            <SelectTrigger class="w-40">
              <SelectValue placeholder="All Teams" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="">All Teams</SelectItem>
              <SelectItem
                v-for="team in teams"
                :key="team.id"
                :value="team.id.toString()"
              >
                {{ team.name }} ({{ team.users_count }})
              </SelectItem>
            </SelectContent>
          </Select>

          <!-- Today Button -->
          <Button variant="outline" size="sm" @click="goToToday">
            <Clock class="h-3 w-3 mr-1" />
            Today
          </Button>
        </div>
      </div>

      <!-- Navigation -->
      <Card>
        <CardContent class="p-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
              <div class="flex items-center gap-2">
                <Button variant="outline" size="sm" @click="navigateCalendar('prev')">
                  <ChevronLeft class="h-4 w-4" />
                </Button>
                <Button variant="outline" size="sm" @click="navigateCalendar('next')">
                  <ChevronRight class="h-4 w-4" />
                </Button>
              </div>
              <h2 class="text-lg font-semibold">{{ calendarTitle }}</h2>
            </div>

            <div class="flex items-center gap-3">
              <!-- Calendar Type Toggle -->
              <Button
                :variant="showCompanyCalendar ? 'default' : 'outline'"
                size="sm"
                @click="showCompanyCalendar = !showCompanyCalendar"
                v-if="userPermissions.canViewAllCompany"
              >
                <Building class="h-3 w-3 mr-1" />
                Company View
              </Button>
              
              <Button
                :variant="showTeamCalendar ? 'default' : 'outline'"
                size="sm"
                @click="showTeamCalendar = !showTeamCalendar"
              >
                <Users class="h-3 w-3 mr-1" />
                Team View
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>

      <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Stats Sidebar -->
        <div class="lg:col-span-1 order-2 lg:order-1">
          <AdminCalendarStats
            :events="filteredEvents"
            :team-events="teamEvents"
            :company-overview="companyOverview"
            :show-company-stats="showCompanyCalendar"
            :show-team-stats="showTeamCalendar"
            :current-period="calendarTitle"
            :selected-team="selectedTeam"
          />
        </div>

        <!-- Main Calendar -->
        <div class="lg:col-span-3 order-1 lg:order-2 space-y-6">
          <!-- Filters and Options -->
          <Card>
            <CardContent class="p-4">
              <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-3">
                  <!-- Status Filters -->
                  <div class="flex items-center gap-2">
                    <Label class="text-sm font-medium">Status:</Label>
                    <div class="flex items-center gap-1">
                      <Button
                        v-for="status in statusFilters"
                        :key="status.value"
                        :variant="selectedStatuses.includes(status.value) ? 'default' : 'outline'"
                        size="sm"
                        @click="toggleStatusFilter(status.value)"
                        class="px-2 py-1 text-xs"
                      >
                        <component :is="status.icon" class="h-3 w-3 mr-1" />
                        {{ status.label }}
                      </Button>
                    </div>
                  </div>
                </div>

                <div class="flex items-center gap-2">
                  <Badge variant="secondary" class="text-xs">
                    {{ filteredEvents.length }} events
                  </Badge>
                  <Badge variant="outline" class="text-xs" v-if="selectedTeam">
                    Team: {{ teams.find(t => t.id.toString() === selectedTeam)?.name }}
                  </Badge>
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
                  :max-visible="3"
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
        <DialogContent v-if="selectedEvent" class="max-w-lg">
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

            <div>
              <Label class="text-sm font-medium">Employee</Label>
              <div class="mt-1 text-sm flex items-center gap-2">
                <User class="h-4 w-4" />
                {{ selectedEvent.user_name }}
                <Badge variant="outline" class="text-xs" v-if="selectedEvent.extendedProps.teamName">
                  {{ selectedEvent.extendedProps.teamName }}
                </Badge>
              </div>
            </div>

            <div v-if="selectedEvent.reason">
              <Label class="text-sm font-medium">Reason</Label>
              <div class="mt-1 text-sm p-2 bg-muted rounded">{{ selectedEvent.reason }}</div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <Label class="text-sm font-medium">Applied On</Label>
                <div class="mt-1 text-sm">{{ selectedEvent.extendedProps.appliedAt }}</div>
              </div>
              <div v-if="selectedEvent.extendedProps.approvedAt">
                <Label class="text-sm font-medium">Approved On</Label>
                <div class="mt-1 text-sm">{{ selectedEvent.extendedProps.approvedAt }}</div>
              </div>
            </div>

            <div v-if="selectedEvent.extendedProps.approverName">
              <Label class="text-sm font-medium">Approved By</Label>
              <div class="mt-1 text-sm">{{ selectedEvent.extendedProps.approverName }}</div>
            </div>
          </div>

          <DialogFooter class="gap-2">
            <Button variant="outline" @click="showEventDetails = false">
              Close
            </Button>
            
            <!-- Admin Actions -->
            <Button 
              v-if="selectedEvent.extendedProps.canApprove"
              @click="approveRequest(selectedEvent.id)"
              class="bg-green-600 hover:bg-green-700"
            >
              <Check class="h-4 w-4 mr-1" />
              Approve
            </Button>
            
            <Button 
              v-if="selectedEvent.extendedProps.canReject"
              variant="destructive"
              @click="rejectRequest(selectedEvent.id)"
            >
              <X class="h-4 w-4 mr-1" />
              Reject
            </Button>
            
            <Button 
              @click="router.visit(route('admin.leave-requests.show', selectedEvent.id))"
            >
              <Eye class="h-4 w-4 mr-1" />
              View Details
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Label } from '@/components/ui/label'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import CalendarDayCell from '@/components/calendar/CalendarDayCell.vue'
import CalendarWeekView from '@/components/calendar/CalendarWeekView.vue'
import CalendarMobileView from '@/components/calendar/CalendarMobileView.vue'
import AdminCalendarStats from '../../../components/admin/AdminCalendarStats.vue'
import { 
  Calendar, CalendarDays, Clock, ChevronLeft, ChevronRight, Users, Building,
  User, Eye, Check, X, CheckCircle, XCircle, Clock as ClockIcon
} from 'lucide-vue-next'
import { format, parseISO, startOfMonth, endOfMonth, startOfWeek, endOfWeek, eachDayOfInterval, addMonths, subMonths, addWeeks, subWeeks, isToday, isSameMonth, isEqual } from 'date-fns'
import axios from 'axios'

// Types
interface CalendarEvent {
  id: number
  title: string
  start: string
  end: string
  allDay: boolean
  status: 'approved' | 'pending' | 'rejected'
  type: string
  reason?: string
  days: number
  user_name: string
  team_name?: string
  backgroundColor: string
  borderColor: string
  textColor: string
  extendedProps: {
    status: string
    type: string
    isOwnRequest: boolean
    appliedAt: string
    approvedAt?: string
    approverName?: string
    userId: number
    userName: string
    teamName?: string
    canApprove: boolean
    canReject: boolean
  }
}

interface CompanyOverview {
  summary: {
    total_requests: number
    approved_requests: number
    pending_requests: number
    rejected_requests: number
    total_days_off: number
    employees_on_leave: number
  }
  team_breakdown: Array<{
    team_name: string
    total_requests: number
    approved_requests: number
    total_days: number
    employees_on_leave: number
  }>
  type_breakdown: Array<{
    type: string
    count: number
    total_days: number
  }>
}

// Props
const props = defineProps<{
  userPermissions: {
    canViewTeam: boolean
    canViewAllCompany: boolean
  }
  teams: Array<{
    id: number
    name: string
    users_count: number
  }>
}>()

// State
const currentDate = ref(new Date())
const currentView = ref<'month' | 'week'>('month')
const selectedTeam = ref('')
const showTeamCalendar = ref(true)
const showCompanyCalendar = ref(false)
const selectedStatuses = ref(['approved', 'pending'])
const events = ref<any[]>([])
const teamEvents = ref<any[]>([])
const companyOverview = ref<CompanyOverview | null>(null)
const showEventDetails = ref(false)
const selectedEvent = ref<any>(null)

// Status filters
const statusFilters = [
  { value: 'approved', label: 'Approved', icon: CheckCircle },
  { value: 'pending', label: 'Pending', icon: ClockIcon },
  { value: 'rejected', label: 'Rejected', icon: XCircle },
]

// Computed
const calendarTitle = computed(() => {
  if (currentView.value === 'week') {
    const weekStart = startOfWeek(currentDate.value)
    const weekEnd = endOfWeek(currentDate.value)
    return `${format(weekStart, 'MMM dd')} - ${format(weekEnd, 'MMM dd, yyyy')}`
  }
  return format(currentDate.value, 'MMMM yyyy')
})

const filteredEvents = computed(() => {
  return events.value.filter(event => {
    const matchesStatus = selectedStatuses.value.includes(event.status)
    return matchesStatus
  })
})

const monthDays = computed(() => {
  if (currentView.value !== 'month') return []
  
  const start = startOfWeek(startOfMonth(currentDate.value))
  const end = endOfWeek(endOfMonth(currentDate.value))
  
  return eachDayOfInterval({ start, end }).map(date => {
    const dayEvents = filteredEvents.value.filter(event => {
      const eventStart = parseISO(event.start)
      const eventEnd = parseISO(event.end)
      return date >= eventStart && date < eventEnd
    })
    
    const dayTeamEvents = teamEvents.value.filter(event => {
      const eventStart = parseISO(event.start)
      const eventEnd = parseISO(event.end)
      return date >= eventStart && date < eventEnd
    })
    
    return {
      date,
      events: dayEvents,
      teamEvents: dayTeamEvents,
      isToday: isToday(date),
      isCurrentMonth: isSameMonth(date, currentDate.value),
    }
  })
})

// Methods
const navigateCalendar = (direction: 'prev' | 'next') => {
  if (currentView.value === 'month') {
    currentDate.value = direction === 'prev' 
      ? subMonths(currentDate.value, 1)
      : addMonths(currentDate.value, 1)
  } else {
    currentDate.value = direction === 'prev'
      ? subWeeks(currentDate.value, 1)
      : addWeeks(currentDate.value, 1)
  }
}

const goToToday = () => {
  currentDate.value = new Date()
}

const toggleStatusFilter = (status: string) => {
  const index = selectedStatuses.value.indexOf(status)
  if (index > -1) {
    selectedStatuses.value.splice(index, 1)
  } else {
    selectedStatuses.value.push(status)
  }
}

const openEventDetails = (event: any) => {
  selectedEvent.value = event
  showEventDetails.value = true
}

const openDayView = (date: Date) => {
  currentDate.value = new Date(date)
  currentView.value = 'week'
}

const getStatusIcon = (status: string) => {
  switch (status) {
    case 'approved': return CheckCircle
    case 'rejected': return XCircle
    case 'pending': return ClockIcon
    default: return ClockIcon
  }
}

const getStatusColor = (status: string) => {
  switch (status) {
    case 'approved': return 'default'
    case 'rejected': return 'destructive'
    case 'pending': return 'secondary'
    default: return 'secondary'
  }
}

const approveRequest = async (requestId: number) => {
  try {
    await axios.patch(route('admin.leave-requests.approve', requestId))
    showEventDetails.value = false
    await fetchEvents()
    // Show success notification
  } catch (error) {
    console.error('Error approving request:', error)
  }
}

const rejectRequest = async (requestId: number) => {
  try {
    await axios.patch(route('admin.leave-requests.reject', requestId))
    showEventDetails.value = false
    await fetchEvents()
    // Show success notification
  } catch (error) {
    console.error('Error rejecting request:', error)
  }
}

const fetchEvents = async () => {
  try {
    const start = currentView.value === 'month' 
      ? startOfMonth(currentDate.value)
      : startOfWeek(currentDate.value)
    const end = currentView.value === 'month'
      ? endOfMonth(currentDate.value)
      : endOfWeek(currentDate.value)

    const params = {
      start: format(start, 'yyyy-MM-dd'),
      end: format(end, 'yyyy-MM-dd'),
      ...(selectedTeam.value && { team_id: selectedTeam.value })
    }

    const response = await axios.get(route('admin.calendar.events'), { params })
    events.value = response.data
  } catch (error) {
    console.error('Error fetching events:', error)
  }
}

const fetchTeamEvents = async () => {
  if (!showTeamCalendar.value) return
  
  try {
    const start = currentView.value === 'month' 
      ? startOfMonth(currentDate.value)
      : startOfWeek(currentDate.value)
    const end = currentView.value === 'month'
      ? endOfMonth(currentDate.value)
      : endOfWeek(currentDate.value)

    const params = {
      start: format(start, 'yyyy-MM-dd'),
      end: format(end, 'yyyy-MM-dd'),
      ...(selectedTeam.value && { team_id: selectedTeam.value })
    }

    const response = await axios.get(route('admin.calendar.team-events'), { params })
    teamEvents.value = response.data
  } catch (error) {
    console.error('Error fetching team events:', error)
  }
}

const fetchCompanyOverview = async () => {
  if (!props.userPermissions.canViewAllCompany || !showCompanyCalendar.value) return
  
  try {
    const start = currentView.value === 'month' 
      ? startOfMonth(currentDate.value)
      : startOfWeek(currentDate.value)
    const end = currentView.value === 'month'
      ? endOfMonth(currentDate.value)
      : endOfWeek(currentDate.value)

    const params = {
      start: format(start, 'yyyy-MM-dd'),
      end: format(end, 'yyyy-MM-dd'),
    }

    const response = await axios.get(route('admin.calendar.company-overview'), { params })
    companyOverview.value = response.data
  } catch (error) {
    console.error('Error fetching company overview:', error)
  }
}

// Watchers
watch([currentDate, currentView, selectedTeam], () => {
  fetchEvents()
  fetchTeamEvents()
  fetchCompanyOverview()
})

watch([showTeamCalendar], () => {
  fetchTeamEvents()
})

watch([showCompanyCalendar], () => {
  fetchCompanyOverview()
})

// Initialize
onMounted(() => {
  fetchEvents()
  fetchTeamEvents()
  if (props.userPermissions.canViewAllCompany) {
    fetchCompanyOverview()
  }
})
</script>

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
