<template>
  <div class="space-y-4">
    <!-- Summary Stats -->
    <Card>
      <CardHeader class="pb-3">
        <CardTitle class="text-base font-medium">{{ currentPeriod }} Overview</CardTitle>
      </CardHeader>
      <CardContent class="space-y-4">
        <!-- Company Overview (if admin has permission) -->
        <div v-if="showCompanyStats && companyOverview" class="space-y-3">
          <div class="grid grid-cols-2 gap-3">
            <div class="p-3 bg-blue-50 rounded-lg">
              <div class="text-2xl font-bold text-blue-600">{{ companyOverview.summary.total_requests }}</div>
              <div class="text-xs text-blue-600">Total Requests</div>
            </div>
            <div class="p-3 bg-green-50 rounded-lg">
              <div class="text-2xl font-bold text-green-600">{{ companyOverview.summary.approved_requests }}</div>
              <div class="text-xs text-green-600">Approved</div>
            </div>
          </div>
          
          <div class="grid grid-cols-2 gap-3">
            <div class="p-3 bg-orange-50 rounded-lg">
              <div class="text-2xl font-bold text-orange-600">{{ companyOverview.summary.pending_requests }}</div>
              <div class="text-xs text-orange-600">Pending</div>
            </div>
            <div class="p-3 bg-purple-50 rounded-lg">
              <div class="text-2xl font-bold text-purple-600">{{ companyOverview.summary.total_days_off }}</div>
              <div class="text-xs text-purple-600">Total Days Off</div>
            </div>
          </div>

          <div class="p-3 bg-gray-50 rounded-lg">
            <div class="text-2xl font-bold text-gray-600">{{ companyOverview.summary.employees_on_leave }}</div>
            <div class="text-xs text-gray-600">Employees Currently on Leave</div>
          </div>
        </div>

        <!-- Regular Event Stats -->
        <div v-else class="space-y-3">
          <div class="grid grid-cols-2 gap-3">
            <div class="p-3 bg-blue-50 rounded-lg">
              <div class="text-2xl font-bold text-blue-600">{{ totalEvents }}</div>
              <div class="text-xs text-blue-600">Total Events</div>
            </div>
            <div class="p-3 bg-green-50 rounded-lg">
              <div class="text-2xl font-bold text-green-600">{{ approvedEvents }}</div>
              <div class="text-xs text-green-600">Approved</div>
            </div>
          </div>
          
          <div class="grid grid-cols-2 gap-3">
            <div class="p-3 bg-orange-50 rounded-lg">
              <div class="text-2xl font-bold text-orange-600">{{ pendingEvents }}</div>
              <div class="text-xs text-orange-600">Pending</div>
            </div>
            <div class="p-3 bg-purple-50 rounded-lg">
              <div class="text-2xl font-bold text-purple-600">{{ totalDaysOff }}</div>
              <div class="text-xs text-purple-600">Days Off</div>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Team Breakdown (if available) -->
    <Card v-if="showCompanyStats && companyOverview?.team_breakdown?.length">
      <CardHeader class="pb-3">
        <CardTitle class="text-base font-medium">Team Breakdown</CardTitle>
      </CardHeader>
      <CardContent>
        <div class="space-y-3">
          <div 
            v-for="team in companyOverview.team_breakdown"
            :key="team.team_name"
            class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
          >
            <div class="flex items-center gap-2">
              <Users class="h-4 w-4 text-gray-500" />
              <span class="text-sm font-medium">{{ team.team_name }}</span>
            </div>
            <div class="text-right">
              <div class="text-sm font-bold">{{ team.total_days }} days</div>
              <div class="text-xs text-gray-500">{{ team.employees_on_leave }} employees</div>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Leave Type Breakdown -->
    <Card v-if="showCompanyStats && companyOverview?.type_breakdown?.length">
      <CardHeader class="pb-3">
        <CardTitle class="text-base font-medium">Leave Types</CardTitle>
      </CardHeader>
      <CardContent>
        <div class="space-y-2">
          <div 
            v-for="type in companyOverview.type_breakdown"
            :key="type.type"
            class="flex items-center justify-between"
          >
            <div class="flex items-center gap-2">
              <div :class="getTypeColor(type.type)" class="w-3 h-3 rounded-full"></div>
              <span class="text-sm">{{ formatLeaveType(type.type) }}</span>
            </div>
            <div class="text-right">
              <div class="text-sm font-medium">{{ type.count }}</div>
              <div class="text-xs text-gray-500">{{ type.total_days }} days</div>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Team Events (if viewing team calendar) -->
    <Card v-if="showTeamStats && teamEvents.length > 0">
      <CardHeader class="pb-3">
        <CardTitle class="text-base font-medium">
          Team Calendar
          <Badge v-if="selectedTeam" variant="outline" class="ml-2 text-xs">
            Filtered
          </Badge>
        </CardTitle>
      </CardHeader>
      <CardContent>
        <div class="space-y-3">
          <div class="grid grid-cols-2 gap-3">
            <div class="p-3 bg-teal-50 rounded-lg">
              <div class="text-2xl font-bold text-teal-600">{{ teamEvents.length }}</div>
              <div class="text-xs text-teal-600">Team Events</div>
            </div>
            <div class="p-3 bg-indigo-50 rounded-lg">
              <div class="text-2xl font-bold text-indigo-600">{{ teamMembers }}</div>
              <div class="text-xs text-indigo-600">Team Members</div>
            </div>
          </div>

          <!-- Recent Team Events -->
          <div class="space-y-2">
            <h4 class="text-sm font-medium text-gray-700">Recent Events</h4>
            <div class="space-y-2 max-h-40 overflow-y-auto">
              <div 
                v-for="event in recentTeamEvents"
                :key="event.id"
                class="flex items-center justify-between p-2 bg-white border rounded text-xs"
              >
                <div class="flex items-center gap-2">
                  <div :class="getStatusColor(event.status)" class="w-2 h-2 rounded-full"></div>
                  <span class="font-medium">{{ event.user_name }}</span>
                </div>
                <div class="text-right">
                  <div>{{ formatLeaveType(event.type) }}</div>
                  <div class="text-gray-500">{{ event.days }} days</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Quick Actions -->
    <Card>
      <CardHeader class="pb-3">
        <CardTitle class="text-base font-medium">Quick Actions</CardTitle>
      </CardHeader>
      <CardContent>
        <div class="space-y-2">
          <Button 
            variant="outline" 
            size="sm" 
            class="w-full justify-start text-xs"
            @click="$emit('export-calendar')"
          >
            <Download class="h-3 w-3 mr-2" />
            Export Calendar
          </Button>
          
          <Button 
            variant="outline" 
            size="sm" 
            class="w-full justify-start text-xs"
            @click="$emit('view-reports')"
          >
            <BarChart3 class="h-3 w-3 mr-2" />
            View Reports
          </Button>
          
          <Button 
            variant="outline" 
            size="sm" 
            class="w-full justify-start text-xs"
            @click="$emit('manage-requests')"
          >
            <Settings class="h-3 w-3 mr-2" />
            Manage Requests
          </Button>
        </div>
      </CardContent>
    </Card>

    <!-- Legend -->
    <Card>
      <CardHeader class="pb-3">
        <CardTitle class="text-base font-medium">Status Legend</CardTitle>
      </CardHeader>
      <CardContent>
        <div class="space-y-2">
          <div class="flex items-center gap-2">
            <CheckCircle class="h-4 w-4 text-green-600" />
            <span class="text-sm">Approved</span>
          </div>
          <div class="flex items-center gap-2">
            <Clock class="h-4 w-4 text-orange-600" />
            <span class="text-sm">Pending</span>
          </div>
          <div class="flex items-center gap-2">
            <XCircle class="h-4 w-4 text-red-600" />
            <span class="text-sm">Rejected</span>
          </div>
        </div>
      </CardContent>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { 
  Users, CheckCircle, Clock, XCircle, Download, BarChart3, Settings 
} from 'lucide-vue-next'

// Props
const props = defineProps<{
  events: Array<any>
  teamEvents: Array<any>
  companyOverview: any
  showCompanyStats: boolean
  showTeamStats: boolean
  currentPeriod: string
  selectedTeam?: string
}>()

// Emits
defineEmits<{
  'export-calendar': []
  'view-reports': []
  'manage-requests': []
}>()

// Computed
const totalEvents = computed(() => props.events.length)

const approvedEvents = computed(() => 
  props.events.filter(event => event.status === 'approved').length
)

const pendingEvents = computed(() => 
  props.events.filter(event => event.status === 'pending').length
)

const totalDaysOff = computed(() => 
  props.events
    .filter(event => event.status === 'approved')
    .reduce((total, event) => total + (event.days || 0), 0)
)

const teamMembers = computed(() => {
  const uniqueUsers = new Set(props.teamEvents.map(event => event.user_name))
  return uniqueUsers.size
})

const recentTeamEvents = computed(() => 
  props.teamEvents
    .slice()
    .sort((a, b) => new Date(b.start).getTime() - new Date(a.start).getTime())
    .slice(0, 5)
)

// Methods
const getTypeColor = (type: string) => {
  const colors = {
    annual: 'bg-blue-400',
    sick: 'bg-red-400',
    personal: 'bg-purple-400',
    maternity: 'bg-pink-400',
    paternity: 'bg-green-400',
    bereavement: 'bg-gray-400',
  }
  return colors[type as keyof typeof colors] || 'bg-gray-400'
}

const getStatusColor = (status: string) => {
  const colors = {
    approved: 'bg-green-400',
    pending: 'bg-orange-400',
    rejected: 'bg-red-400',
  }
  return colors[status as keyof typeof colors] || 'bg-gray-400'
}

const formatLeaveType = (type: string) => {
  return type.charAt(0).toUpperCase() + type.slice(1)
}
</script>
