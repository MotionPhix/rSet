<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { toast } from 'vue-sonner';
import AppLayout from '@/layouts/AppLayout.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import {
  Calendar,
  Users,
  Clock,
  CheckCircle2,
  BarChart3,
  User,
  Award,
  AlertCircle
} from 'lucide-vue-next';
import { format } from 'date-fns';

interface Props {
  personalStats: {
    total_requests_this_year: number;
    approved_days_this_year: number;
    pending_requests: number;
    last_leave: string | null;
  };
  teamStats?: {
    team_name: string;
    team_members_count: number;
    pending_approvals: number;
    approved_this_month: number;
  } | null;
  leaveHistory: Array<{
    id: number;
    leave_type: string;
    start_date: string;
    end_date: string;
    days: number;
    status: string;
    applied_at: string;
  }>;
}

const props = defineProps<Props>();

const breadcrumbs = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Reports', href: '/reports' }
];

// Report data state
const personalReportData = ref(null);
const teamReportData = ref(null);
const selectedYear = ref(new Date().getFullYear());
const selectedMonth = ref(format(new Date(), 'yyyy-MM'));
const loading = ref(false);

const availableYears = Array.from({ length: 5 }, (_, i) => new Date().getFullYear() - i);
const availableMonths = Array.from({ length: 12 }, (_, i) => {
  const date = new Date(2024, i, 1);
  return {
    value: format(date, 'yyyy-MM'),
    label: format(date, 'MMMM yyyy')
  };
});

const fetchPersonalReport = async () => {
  loading.value = true;
  try {
    const response = await fetch(route('employee.reports.personal', { year: selectedYear.value }));
    if (response.ok) {
      personalReportData.value = await response.json();
    } else {
      toast.error('Failed to fetch personal report');
    }
  } catch (error) {
    toast.error('An error occurred while fetching personal report');
    console.error('Personal report error:', error);
  } finally {
    loading.value = false;
  }
};

const fetchTeamReport = async () => {
  if (!props.teamStats) return;

  loading.value = true;
  try {
    const response = await fetch(route('employee.reports.team', { month: selectedMonth.value }));
    if (response.ok) {
      teamReportData.value = await response.json();
    } else {
      toast.error('Failed to fetch team report');
    }
  } catch (error) {
    toast.error('An error occurred while fetching team report');
    console.error('Team report error:', error);
  } finally {
    loading.value = false;
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

const getStatusIcon = (status: string) => {
  switch (status) {
    case 'approved': return CheckCircle2;
    case 'pending': return Clock;
    case 'rejected': return AlertCircle;
    default: return AlertCircle;
  }
};

// Calculate leave balance progress
const leaveBalanceProgress = computed(() => {
  if (!personalReportData.value?.leave_balance) return 0;
  const balance = personalReportData.value.leave_balance;
  return Math.max(0, Math.min(100, (balance.used_days / balance.annual_entitlement) * 100));
});

const remainingLeaveColor = computed(() => {
  const progress = leaveBalanceProgress.value;
  if (progress >= 90) return 'text-red-600';
  if (progress >= 70) return 'text-yellow-600';
  return 'text-green-600';
});
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="My Reports" />

    <div class="space-y-6">
      <!-- Header -->
      <HeadingSmall
        title="My Reports"
        description="View your personal leave statistics and team insights"
      />

      <!-- Personal Stats Overview -->
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Requests This Year</CardTitle>
            <Calendar class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ props.personalStats.total_requests_this_year }}</div>
            <p class="text-xs text-muted-foreground">Total applications</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Days Taken</CardTitle>
            <CheckCircle2 class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ props.personalStats.approved_days_this_year }}</div>
            <p class="text-xs text-muted-foreground">Approved days this year</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Pending Requests</CardTitle>
            <Clock class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ props.personalStats.pending_requests }}</div>
            <p class="text-xs text-muted-foreground">Awaiting approval</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Last Leave</CardTitle>
            <User class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-lg font-bold">
              {{ props.personalStats.last_leave ? format(new Date(props.personalStats.last_leave), 'MMM dd') : 'None' }}
            </div>
            <p class="text-xs text-muted-foreground">Most recent leave</p>
          </CardContent>
        </Card>
      </div>

      <Tabs default-value="personal" class="space-y-4">
        <TabsList>
          <TabsTrigger value="personal">Personal Reports</TabsTrigger>
          <TabsTrigger
            value="team"
            :disabled="!props.teamStats"
            class="relative"
          >
            Team Reports
            <Badge v-if="props.teamStats?.pending_approvals" variant="destructive" class="ml-2 h-5 w-5 rounded-full p-0 text-xs">
              {{ props.teamStats.pending_approvals }}
            </Badge>
          </TabsTrigger>
        </TabsList>

        <!-- Personal Reports Tab -->
        <TabsContent value="personal" class="space-y-4">
          <div class="grid gap-6 lg:grid-cols-3">
            <!-- Report Controls -->
            <div>
              <Card>
                <CardHeader>
                  <CardTitle class="flex items-center gap-2">
                    <BarChart3 class="h-5 w-5" />
                    Report Options
                  </CardTitle>
                  <CardDescription>Customize your personal report</CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                  <div class="space-y-2">
                    <label class="text-sm font-medium">Year</label>
                    <Select v-model="selectedYear" @update:model-value="fetchPersonalReport">
                      <SelectTrigger>
                        <SelectValue />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem
                          v-for="year in availableYears"
                          :key="year"
                          :value="year.toString()"
                        >
                          {{ year }}
                        </SelectItem>
                      </SelectContent>
                    </Select>
                  </div>

                  <Button @click="fetchPersonalReport" :disabled="loading" class="w-full">
                    <BarChart3 class="h-4 w-4 mr-2" />
                    {{ loading ? 'Loading...' : 'Generate Report' }}
                  </Button>
                </CardContent>
              </Card>
            </div>

            <!-- Leave Balance -->
            <div class="lg:col-span-2">
              <Card>
                <CardHeader>
                  <CardTitle class="flex items-center gap-2">
                    <Award class="h-5 w-5" />
                    Leave Balance {{ selectedYear }}
                  </CardTitle>
                  <CardDescription>Your current leave entitlement and usage</CardDescription>
                </CardHeader>
                <CardContent v-if="personalReportData?.leave_balance" class="space-y-4">
                  <div class="grid gap-4 md:grid-cols-3">
                    <div class="space-y-2">
                      <div class="text-sm text-muted-foreground">Annual Entitlement</div>
                      <div class="text-2xl font-bold">{{ personalReportData.leave_balance.annual_entitlement }}</div>
                    </div>
                    <div class="space-y-2">
                      <div class="text-sm text-muted-foreground">Days Used</div>
                      <div class="text-2xl font-bold">{{ personalReportData.leave_balance.used_days }}</div>
                    </div>
                    <div class="space-y-2">
                      <div class="text-sm text-muted-foreground">Remaining</div>
                      <div class="text-2xl font-bold" :class="remainingLeaveColor">
                        {{ personalReportData.leave_balance.remaining_days }}
                      </div>
                    </div>
                  </div>

                  <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                      <span>Usage Progress</span>
                      <span>{{ Math.round(leaveBalanceProgress) }}%</span>
                    </div>
                    <Progress :value="leaveBalanceProgress" class="h-2" />
                  </div>

                  <div v-if="personalReportData.leave_balance.pending_days > 0" class="p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                    <div class="flex items-center gap-2 text-yellow-800 dark:text-yellow-200">
                      <AlertCircle class="h-4 w-4" />
                      <span class="text-sm font-medium">
                        {{ personalReportData.leave_balance.pending_days }} days pending approval
                      </span>
                    </div>
                  </div>
                </CardContent>
                <CardContent v-else>
                  <div class="text-center py-8 text-muted-foreground">
                    <Award class="h-12 w-12 mx-auto mb-4 opacity-50" />
                    <p>Generate a report to view your leave balance</p>
                  </div>
                </CardContent>
              </Card>
            </div>
          </div>

          <!-- Yearly Summary -->
          <Card v-if="personalReportData?.yearly_summary">
            <CardHeader>
              <CardTitle>{{ selectedYear }} Summary</CardTitle>
              <CardDescription>Your leave activity breakdown for the year</CardDescription>
            </CardHeader>
            <CardContent>
              <div class="grid gap-4 md:grid-cols-4">
                <div class="space-y-2">
                  <div class="text-sm text-muted-foreground">Total Requests</div>
                  <div class="text-2xl font-bold">{{ personalReportData.yearly_summary.total_requests }}</div>
                </div>
                <div class="space-y-2">
                  <div class="text-sm text-muted-foreground">Approved</div>
                  <div class="text-2xl font-bold text-green-600">{{ personalReportData.yearly_summary.approved_requests }}</div>
                </div>
                <div class="space-y-2">
                  <div class="text-sm text-muted-foreground">Pending</div>
                  <div class="text-2xl font-bold text-yellow-600">{{ personalReportData.yearly_summary.pending_requests }}</div>
                </div>
                <div class="space-y-2">
                  <div class="text-sm text-muted-foreground">Rejected</div>
                  <div class="text-2xl font-bold text-red-600">{{ personalReportData.yearly_summary.rejected_requests }}</div>
                </div>
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        <!-- Team Reports Tab -->
        <TabsContent value="team" class="space-y-4" v-if="props.teamStats">
          <div class="grid gap-6 lg:grid-cols-3">
            <!-- Team Controls -->
            <div>
              <Card>
                <CardHeader>
                  <CardTitle class="flex items-center gap-2">
                    <Users class="h-5 w-5" />
                    Team Options
                  </CardTitle>
                  <CardDescription>{{ props.teamStats.team_name }} insights</CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                  <div class="space-y-2">
                    <label class="text-sm font-medium">Month</label>
                    <Select v-model="selectedMonth" @update:model-value="fetchTeamReport">
                      <SelectTrigger>
                        <SelectValue />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem
                          v-for="month in availableMonths"
                          :key="month.value"
                          :value="month.value"
                        >
                          {{ month.label }}
                        </SelectItem>
                      </SelectContent>
                    </Select>
                  </div>

                  <Button @click="fetchTeamReport" :disabled="loading" class="w-full">
                    <Users class="h-4 w-4 mr-2" />
                    {{ loading ? 'Loading...' : 'Generate Team Report' }}
                  </Button>
                </CardContent>
              </Card>
            </div>

            <!-- Team Stats -->
            <div class="lg:col-span-2">
              <Card>
                <CardHeader>
                  <CardTitle>{{ props.teamStats.team_name }} Overview</CardTitle>
                  <CardDescription>Current team status and metrics</CardDescription>
                </CardHeader>
                <CardContent>
                  <div class="grid gap-4 md:grid-cols-3">
                    <div class="space-y-2">
                      <div class="text-sm text-muted-foreground">Team Members</div>
                      <div class="text-2xl font-bold">{{ props.teamStats.team_members_count }}</div>
                    </div>
                    <div class="space-y-2">
                      <div class="text-sm text-muted-foreground">Pending Approvals</div>
                      <div class="text-2xl font-bold text-yellow-600">{{ props.teamStats.pending_approvals }}</div>
                    </div>
                    <div class="space-y-2">
                      <div class="text-sm text-muted-foreground">Approved This Month</div>
                      <div class="text-2xl font-bold text-green-600">{{ props.teamStats.approved_this_month }}</div>
                    </div>
                  </div>
                </CardContent>
              </Card>
            </div>
          </div>
        </TabsContent>
      </Tabs>

      <!-- Recent Leave History -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Calendar class="h-5 w-5" />
            Recent Leave History
          </CardTitle>
          <CardDescription>Your latest leave requests and their status</CardDescription>
        </CardHeader>
        <CardContent>
          <div class="space-y-3">
            <div
              v-for="request in props.leaveHistory"
              :key="request.id"
              class="flex items-center justify-between p-4 rounded-lg border hover:bg-accent/50 transition-colors"
            >
              <div class="space-y-1">
                <div class="font-medium">{{ request.leave_type }}</div>
                <div class="text-sm text-muted-foreground">
                  {{ format(new Date(request.start_date), 'MMM dd, yyyy') }} -
                  {{ format(new Date(request.end_date), 'MMM dd, yyyy') }}
                </div>
                <div class="text-xs text-muted-foreground">
                  {{ request.days }} days • Applied {{ request.applied_at }}
                </div>
              </div>
              <Badge :variant="getStatusColor(request.status)">
                <component :is="getStatusIcon(request.status)" class="h-3 w-3 mr-1" />
                {{ request.status }}
              </Badge>
            </div>

            <div v-if="props.leaveHistory.length === 0" class="text-center py-8 text-muted-foreground">
              <Calendar class="h-12 w-12 mx-auto mb-4 opacity-50" />
              <p>No leave requests found</p>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
