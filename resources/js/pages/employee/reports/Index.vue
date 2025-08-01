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
const personalReportData = ref<{
  yearly_summary?: any;
  monthly_breakdown?: any;
  leave_balance?: any;
  detailed_requests?: Array<{
    id: number;
    type: string;
    start_date: string;
    end_date: string;
    days: number;
    status: string;
    reason?: string;
    applied_date: string;
    approved_date?: string;
    approver_name?: string;
    week_of_year: number;
    quarter: number;
    is_weekend_adjacent: boolean;
    is_long_weekend: boolean;
  }>;
  leave_type_breakdown?: Array<{
    type: string;
    total_requests: number;
    approved_requests: number;
    rejected_requests: number;
    pending_requests: number;
    total_days_used: number;
    average_duration: number;
    approval_rate: number;
    most_recent_use?: string;
  }>;
  attendance_patterns?: {
    quarterly_distribution: Record<string, number>;
    monthly_pattern: Record<number, number>;
    preferred_start_days: Record<string, number>;
    peak_month: number;
    least_active_month: number;
    total_leave_blocks: number;
    average_leave_duration: number;
  };
  forfeited_analysis?: {
    annual_entitlement: number;
    used_days: number;
    remaining_days: number;
    forfeited_days_this_year: number;
    forfeited_days_last_year: number;
    carry_over_limit: number;
    utilization_rate: number;
    projected_forfeit: number;
    recommendations: string[];
    optimal_usage_timeline: {
      months_remaining?: number;
      days_per_month_suggested?: number;
      message: string;
    };
  };
} | null>(null);
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

    <div class="space-y-6 p-6 max-w-4xl">
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
                      <SelectTrigger class="w-full is-large">
                        <SelectValue placeholder="Generate Report By Year" />
                      </SelectTrigger>

                      <SelectContent>
                        <SelectItem
                          v-for="year in availableYears"
                          :key="year"
                          :value="year.toString()">
                          {{ year }}
                        </SelectItem>
                      </SelectContent>
                    </Select>
                  </div>

                  <Button 
                    @click="fetchPersonalReport" 
                    :disabled="loading" class="w-full is-large">
                    <BarChart3 class="h-4 w-4" />
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

          <!-- Forfeited Days Analysis -->
          <Card v-if="personalReportData?.forfeited_analysis">
            <CardHeader>
              <CardTitle class="flex items-center gap-2">
                <AlertCircle class="h-5 w-5" />
                Leave Utilization & Forfeit Analysis
              </CardTitle>
              <CardDescription>Understand your leave usage patterns and avoid losing days</CardDescription>
            </CardHeader>
            <CardContent class="space-y-6">
              <div class="grid gap-4 md:grid-cols-4">
                <div class="space-y-2">
                  <div class="text-sm text-muted-foreground">Utilization Rate</div>
                  <div class="text-2xl font-bold">{{ personalReportData.forfeited_analysis.utilization_rate }}%</div>
                </div>
                <div class="space-y-2">
                  <div class="text-sm text-muted-foreground">Remaining Days</div>
                  <div class="text-2xl font-bold" :class="remainingLeaveColor">{{ personalReportData.forfeited_analysis.remaining_days }}</div>
                </div>
                <div class="space-y-2">
                  <div class="text-sm text-muted-foreground">Projected Forfeit</div>
                  <div class="text-2xl font-bold text-red-600">{{ personalReportData.forfeited_analysis.projected_forfeit }}</div>
                </div>
                <div class="space-y-2">
                  <div class="text-sm text-muted-foreground">Last Year Forfeited</div>
                  <div class="text-2xl font-bold text-red-600">{{ personalReportData.forfeited_analysis.forfeited_days_last_year }}</div>
                </div>
              </div>

              <div v-if="personalReportData.forfeited_analysis.recommendations?.length" class="space-y-3">
                <h4 class="font-medium text-sm">💡 Recommendations</h4>
                <div class="space-y-2">
                  <div
                    v-for="(recommendation, index) in personalReportData.forfeited_analysis.recommendations"
                    :key="index"
                    class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800"
                  >
                    <p class="text-sm text-blue-800 dark:text-blue-200">{{ recommendation }}</p>
                  </div>
                </div>
              </div>

              <div v-if="personalReportData.forfeited_analysis.optimal_usage_timeline" class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                <h4 class="font-medium text-sm text-green-800 dark:text-green-200 mb-2">📅 Optimal Usage Plan</h4>
                <p class="text-sm text-green-700 dark:text-green-300">{{ personalReportData.forfeited_analysis.optimal_usage_timeline.message }}</p>
              </div>
            </CardContent>
          </Card>

          <!-- Leave Type Breakdown -->
          <Card v-if="personalReportData?.leave_type_breakdown?.length">
            <CardHeader>
              <CardTitle>Leave Type Analysis</CardTitle>
              <CardDescription>Detailed breakdown by leave type</CardDescription>
            </CardHeader>
            <CardContent>
              <div class="space-y-4">
                <div
                  v-for="typeData in personalReportData.leave_type_breakdown"
                  :key="typeData.type"
                  class="p-4 rounded-lg border hover:bg-accent/50 transition-colors"
                >
                  <div class="flex justify-between items-start mb-3">
                    <div>
                      <h4 class="font-medium capitalize">{{ typeData.type }}</h4>
                      <p class="text-sm text-muted-foreground">{{ typeData.total_days_used }} days used • {{ typeData.approval_rate }}% approval rate</p>
                    </div>
                    <Badge variant="outline">{{ typeData.total_requests }} requests</Badge>
                  </div>
                  
                  <div class="grid gap-3 md:grid-cols-4 text-sm">
                    <div>
                      <span class="text-muted-foreground">Approved:</span>
                      <span class="ml-1 font-medium text-green-600">{{ typeData.approved_requests }}</span>
                    </div>
                    <div>
                      <span class="text-muted-foreground">Rejected:</span>
                      <span class="ml-1 font-medium text-red-600">{{ typeData.rejected_requests }}</span>
                    </div>
                    <div>
                      <span class="text-muted-foreground">Avg Duration:</span>
                      <span class="ml-1 font-medium">{{ typeData.average_duration }} days</span>
                    </div>
                    <div v-if="typeData.most_recent_use">
                      <span class="text-muted-foreground">Last Used:</span>
                      <span class="ml-1 font-medium">{{ format(new Date(typeData.most_recent_use), 'MMM dd') }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Attendance Patterns -->
          <Card v-if="personalReportData?.attendance_patterns">
            <CardHeader>
              <CardTitle>Leave Patterns & Insights</CardTitle>
              <CardDescription>Understanding your leave-taking behavior</CardDescription>
            </CardHeader>
            <CardContent class="space-y-6">
              <div class="grid gap-4 md:grid-cols-3">
                <div>
                  <h4 class="font-medium text-sm mb-2">📊 Leave Statistics</h4>
                  <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                      <span class="text-muted-foreground">Total leave blocks:</span>
                      <span class="font-medium">{{ personalReportData.attendance_patterns.total_leave_blocks }}</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-muted-foreground">Average duration:</span>
                      <span class="font-medium">{{ personalReportData.attendance_patterns.average_leave_duration }} days</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-muted-foreground">Peak month:</span>
                      <span class="font-medium">{{ new Date(2024, personalReportData.attendance_patterns.peak_month - 1).toLocaleString('default', { month: 'long' }) }}</span>
                    </div>
                  </div>
                </div>

                <div>
                  <h4 class="font-medium text-sm mb-2">🗓️ Quarterly Distribution</h4>
                  <div class="space-y-2 text-sm">
                    <div v-for="(days, quarter) in personalReportData.attendance_patterns.quarterly_distribution" :key="quarter" class="flex justify-between">
                      <span class="text-muted-foreground">{{ quarter }}:</span>
                      <span class="font-medium">{{ days }} days</span>
                    </div>
                  </div>
                </div>

                <div>
                  <h4 class="font-medium text-sm mb-2">📅 Preferred Start Days</h4>
                  <div class="space-y-1 text-sm">
                    <div 
                      v-for="(count, day) in personalReportData.attendance_patterns.preferred_start_days" 
                      :key="day"
                      class="flex justify-between"
                      v-show="count > 0"
                    >
                      <span class="text-muted-foreground">{{ day }}:</span>
                      <span class="font-medium">{{ count }}x</span>
                    </div>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Detailed Leave History -->
          <Card v-if="personalReportData?.detailed_requests?.length">
            <CardHeader>
              <CardTitle>Detailed Leave History {{ selectedYear }}</CardTitle>
              <CardDescription>Complete record of all leave requests for the selected year</CardDescription>
            </CardHeader>
            <CardContent>
              <div class="space-y-3 max-h-96 overflow-y-auto">
                <div
                  v-for="request in personalReportData.detailed_requests"
                  :key="request.id"
                  class="p-4 rounded-lg border hover:bg-accent/50 transition-colors"
                >
                  <div class="flex justify-between items-start mb-2">
                    <div class="space-y-1">
                      <div class="flex items-center gap-2">
                        <h4 class="font-medium capitalize">{{ request.type }}</h4>
                        <Badge :variant="getStatusColor(request.status)">
                          <component :is="getStatusIcon(request.status)" class="h-3 w-3 mr-1" />
                          {{ request.status }}
                        </Badge>
                        <Badge v-if="request.is_long_weekend" variant="secondary" class="text-xs">Long Weekend</Badge>
                      </div>
                      <div class="text-sm text-muted-foreground">
                        {{ format(new Date(request.start_date), 'MMM dd, yyyy') }} - {{ format(new Date(request.end_date), 'MMM dd, yyyy') }}
                      </div>
                      <div class="text-sm text-muted-foreground">
                        {{ request.days }} {{ request.days === 1 ? 'day' : 'days' }} • Applied: {{ format(new Date(request.applied_date), 'MMM dd') }}
                        <span v-if="request.approved_date"> • Approved: {{ format(new Date(request.approved_date), 'MMM dd') }}</span>
                      </div>
                    </div>
                    <div class="text-right text-sm text-muted-foreground">
                      <div>Q{{ request.quarter }} • Week {{ request.week_of_year }}</div>
                      <div v-if="request.approver_name" class="text-xs">by {{ request.approver_name }}</div>
                    </div>
                  </div>
                  
                  <div v-if="request.reason" class="mt-3 p-2 bg-muted/50 rounded text-sm">
                    <strong>Reason:</strong> {{ request.reason }}
                  </div>
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
