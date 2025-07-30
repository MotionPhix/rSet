<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { useVModel } from '@vueuse/core';
import { toast } from 'vue-sonner';
import AppLayout from '@/layouts/AppLayout.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Calendar } from '@/components/ui/calendar';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import {
  BarChart3,
  Download,
  Calendar as CalendarIcon,
  Users,
  Clock,
  CheckCircle2,
  XCircle,
  AlertCircle,
  FileText,
  TrendingUp,
  Filter
} from 'lucide-vue-next';
import { format } from 'date-fns';

interface Props {
  stats: {
    total_employees: number;
    pending_requests: number;
    approved_this_month: number;
    total_teams: number;
  };
  recentLeaveRequests: Array<{
    id: number;
    user_name: string;
    leave_type: string;
    start_date: string;
    end_date: string;
    status: string;
    days: number;
  }>;
}

const props = defineProps<Props>();

const breadcrumbs = [
  { title: 'Admin', href: '/admin/dashboard' },
  { title: 'Reports', href: '/admin/reports' }
];

// Report generation state
const reportLoading = ref(false);
const exportLoading = ref(false);
const reportData = ref(null);

// Form data for report generation
const reportForm = ref({
  type: 'leave_summary',
  start_date: new Date(new Date().getFullYear(), 0, 1), // Start of current year
  end_date: new Date(),
  team_id: '',
  user_id: ''
});

// Date picker states
const startDateOpen = ref(false);
const endDateOpen = ref(false);

const reportTypes = [
  { value: 'leave_summary', label: 'Leave Summary Report' },
  { value: 'team_performance', label: 'Team Performance Report' },
  { value: 'monthly_overview', label: 'Monthly Overview Report' },
  { value: 'yearly_overview', label: 'Yearly Overview Report' }
];

const exportFormats = [
  { value: 'csv', label: 'CSV' },
  { value: 'pdf', label: 'PDF' },
  { value: 'excel', label: 'Excel' }
];

const generateReport = async () => {
  reportLoading.value = true;
  try {
    const response = await fetch(route('admin.reports.generate'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify({
        type: reportForm.value.type,
        start_date: format(reportForm.value.start_date, 'yyyy-MM-dd'),
        end_date: format(reportForm.value.end_date, 'yyyy-MM-dd'),
        team_id: reportForm.value.team_id || null,
        user_id: reportForm.value.user_id || null
      })
    });

    if (response.ok) {
      reportData.value = await response.json();
      toast.success('Report generated successfully');
    } else {
      toast.error('Failed to generate report');
    }
  } catch (error) {
    toast.error('An error occurred while generating the report');
    console.error('Report generation error:', error);
  } finally {
    reportLoading.value = false;
  }
};

const exportReport = async (format: string) => {
  exportLoading.value = true;
  try {
    const response = await fetch(route('admin.reports.export'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify({
        type: format,
        report_type: reportForm.value.type,
        start_date: format(reportForm.value.start_date, 'yyyy-MM-dd'),
        end_date: format(reportForm.value.end_date, 'yyyy-MM-dd')
      })
    });

    if (response.ok) {
      toast.success(`Report export started. You'll receive a download link shortly.`);
    } else {
      toast.error('Failed to export report');
    }
  } catch (error) {
    toast.error('An error occurred while exporting the report');
    console.error('Export error:', error);
  } finally {
    exportLoading.value = false;
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
    case 'rejected': return XCircle;
    default: return AlertCircle;
  }
};
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Reports & Analytics" />

    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <HeadingSmall
          title="Reports & Analytics"
          description="Generate comprehensive reports and analyze leave patterns"
        />
        <div class="flex items-center gap-2">
          <Button variant="outline" size="sm">
            <Filter class="h-4 w-4 mr-2" />
            Filters
          </Button>
        </div>
      </div>

      <!-- Stats Overview -->
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Employees</CardTitle>
            <Users class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ props.stats.total_employees }}</div>
            <p class="text-xs text-muted-foreground">Active company members</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Pending Requests</CardTitle>
            <Clock class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ props.stats.pending_requests }}</div>
            <p class="text-xs text-muted-foreground">Awaiting approval</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Approved This Month</CardTitle>
            <CheckCircle2 class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ props.stats.approved_this_month }}</div>
            <p class="text-xs text-muted-foreground">Current month approvals</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Teams</CardTitle>
            <Users class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ props.stats.total_teams }}</div>
            <p class="text-xs text-muted-foreground">Organized teams</p>
          </CardContent>
        </Card>
      </div>

      <div class="grid gap-6 lg:grid-cols-3">
        <!-- Report Generation -->
        <div class="lg:col-span-2">
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center gap-2">
                <BarChart3 class="h-5 w-5" />
                Generate Report
              </CardTitle>
              <CardDescription>
                Create detailed reports for analysis and export
              </CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
              <!-- Report Type Selection -->
              <div class="space-y-2">
                <Label>Report Type</Label>
                <Select v-model="reportForm.type">
                  <SelectTrigger>
                    <SelectValue placeholder="Select report type" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem
                      v-for="type in reportTypes"
                      :key="type.value"
                      :value="type.value"
                    >
                      {{ type.label }}
                    </SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <!-- Date Range Selection -->
              <div class="grid gap-4 md:grid-cols-2">
                <div class="space-y-2">
                  <Label>Start Date</Label>
                  <Popover v-model:open="startDateOpen">
                    <PopoverTrigger as-child>
                      <Button variant="outline" class="w-full justify-start text-left font-normal">
                        <CalendarIcon class="mr-2 h-4 w-4" />
                        {{ format(reportForm.start_date, 'PPP') }}
                      </Button>
                    </PopoverTrigger>
                    <PopoverContent class="w-auto p-0" align="start">
                      <Calendar v-model="reportForm.start_date" @update:model-value="startDateOpen = false" />
                    </PopoverContent>
                  </Popover>
                </div>

                <div class="space-y-2">
                  <Label>End Date</Label>
                  <Popover v-model:open="endDateOpen">
                    <PopoverTrigger as-child>
                      <Button variant="outline" class="w-full justify-start text-left font-normal">
                        <CalendarIcon class="mr-2 h-4 w-4" />
                        {{ format(reportForm.end_date, 'PPP') }}
                      </Button>
                    </PopoverTrigger>
                    <PopoverContent class="w-auto p-0" align="start">
                      <Calendar v-model="reportForm.end_date" @update:model-value="endDateOpen = false" />
                    </PopoverContent>
                  </Popover>
                </div>
              </div>

              <div class="flex flex-col sm:flex-row gap-2">
                <Button
                  @click="generateReport"
                  :disabled="reportLoading"
                  class="flex-1"
                >
                  <BarChart3 class="h-4 w-4 mr-2" />
                  {{ reportLoading ? 'Generating...' : 'Generate Report' }}
                </Button>

                <div class="flex gap-2">
                  <Button
                    v-for="format in exportFormats"
                    :key="format.value"
                    variant="outline"
                    size="sm"
                    @click="exportReport(format.value)"
                    :disabled="exportLoading"
                  >
                    <Download class="h-4 w-4 mr-1" />
                    {{ format.label }}
                  </Button>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Report Results -->
          <Card v-if="reportData" class="mt-6">
            <CardHeader>
              <CardTitle>Report Results</CardTitle>
              <CardDescription>Generated report data and insights</CardDescription>
            </CardHeader>
            <CardContent>
              <div class="space-y-4">
                <!-- Report data will be displayed here -->
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                  <div class="space-y-2">
                    <div class="text-sm text-muted-foreground">Total Requests</div>
                    <div class="text-2xl font-bold">{{ reportData.total_requests || 0 }}</div>
                  </div>
                  <div class="space-y-2">
                    <div class="text-sm text-muted-foreground">Approved</div>
                    <div class="text-2xl font-bold text-green-600">{{ reportData.approved_requests || 0 }}</div>
                  </div>
                  <div class="space-y-2">
                    <div class="text-sm text-muted-foreground">Pending</div>
                    <div class="text-2xl font-bold text-yellow-600">{{ reportData.pending_requests || 0 }}</div>
                  </div>
                  <div class="space-y-2">
                    <div class="text-sm text-muted-foreground">Total Days</div>
                    <div class="text-2xl font-bold">{{ reportData.total_days || 0 }}</div>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Recent Leave Requests -->
        <div>
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center gap-2">
                <FileText class="h-5 w-5" />
                Recent Requests
              </CardTitle>
              <CardDescription>Latest leave requests overview</CardDescription>
            </CardHeader>
            <CardContent>
              <div class="space-y-3">
                <div
                  v-for="request in props.recentLeaveRequests"
                  :key="request.id"
                  class="flex items-center justify-between p-3 rounded-lg border"
                >
                  <div class="space-y-1">
                    <div class="font-medium text-sm">{{ request.user_name }}</div>
                    <div class="text-xs text-muted-foreground">
                      {{ request.leave_type }} • {{ request.days }} days
                    </div>
                    <div class="text-xs text-muted-foreground">
                      {{ format(new Date(request.start_date), 'MMM dd') }} -
                      {{ format(new Date(request.end_date), 'MMM dd') }}
                    </div>
                  </div>
                  <Badge :variant="getStatusColor(request.status)">
                    <component :is="getStatusIcon(request.status)" class="h-3 w-3 mr-1" />
                    {{ request.status }}
                  </Badge>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
