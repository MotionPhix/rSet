<template>
  <AdminLayout title="Reports">
    <div class="space-y-6">
      <div class="grid gap-6 md:grid-cols-4">
        <Card>
          <CardHeader class="pb-2">
            <CardTitle class="text-sm font-medium">Total Leave Requests</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ stats.total_leave_requests }}</div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader class="pb-2">
            <CardTitle class="text-sm font-medium">Pending Requests</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ stats.pending_leave_requests }}</div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader class="pb-2">
            <CardTitle class="text-sm font-medium">Approved Requests</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ stats.approved_leave_requests }}</div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader class="pb-2">
            <CardTitle class="text-sm font-medium">Rejected Requests</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ stats.rejected_leave_requests }}</div>
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Generate Report</CardTitle>
          <CardDescription>Select parameters to generate a custom report</CardDescription>
        </CardHeader>
        <CardContent>
          <form @submit.prevent="generateReport" class="space-y-6">
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
              <div class="space-y-2">
                <Label for="report-type">Report Type</Label>
                <Select v-model="form.report_type">
                  <SelectTrigger id="report-type" class="w-full">
                    <SelectValue placeholder="Select report type" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem v-for="type in reportTypes" :key="type.id" :value="type.id">
                      {{ type.name }}
                    </SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div class="space-y-2">
                <Label for="start-date">Start Date</Label>
                <Popover>
                  <PopoverTrigger as-child>
                    <Button
                      variant="outline"
                      class="w-full justify-start text-left font-normal"
                      :class="{ 'text-muted-foreground': !form.start_date }"
                    >
                      <CalendarIcon class="mr-2 h-4 w-4" />
                      {{ form.start_date ? new Date(form.start_date).toLocaleDateString() : 'Select date' }}
                    </Button>
                  </PopoverTrigger>
                  <PopoverContent class="w-auto p-0">
                    <Calendar v-model="form.start_date" />
                  </PopoverContent>
                </Popover>
              </div>

              <div class="space-y-2">
                <Label for="end-date">End Date</Label>
                <Popover>
                  <PopoverTrigger as-child>
                    <Button
                      variant="outline"
                      class="w-full justify-start text-left font-normal"
                      :class="{ 'text-muted-foreground': !form.end_date }"
                    >
                      <CalendarIcon class="mr-2 h-4 w-4" />
                      {{ form.end_date ? new Date(form.end_date).toLocaleDateString() : 'Select date' }}
                    </Button>
                  </PopoverTrigger>
                  <PopoverContent class="w-auto p-0">
                    <Calendar v-model="form.end_date" :disabled-date="date => date < form.start_date" />
                  </PopoverContent>
                </Popover>
              </div>

              <div class="space-y-2">
                <Label for="status">Status</Label>
                <Select v-model="form.status">
                  <SelectTrigger id="status" class="w-full">
                    <SelectValue placeholder="Any status" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="">Any status</SelectItem>
                    <SelectItem value="pending">Pending</SelectItem>
                    <SelectItem value="approved">Approved</SelectItem>
                    <SelectItem value="rejected">Rejected</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div v-if="teams.length > 0" class="space-y-2">
                <Label for="team">Team</Label>
                <Select v-model="form.team_id">
                  <SelectTrigger id="team" class="w-full">
                    <SelectValue placeholder="All teams" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="">All teams</SelectItem>
                    <SelectItem v-for="team in teams" :key="team.id" :value="team.id">
                      {{ team.name }}
                    </SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div v-if="leaveTypes.length > 0" class="space-y-2">
                <Label for="leave-type">Leave Type</Label>
                <Select v-model="form.leave_type_id">
                  <SelectTrigger id="leave-type" class="w-full">
                    <SelectValue placeholder="All types" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="">All types</SelectItem>
                    <SelectItem v-for="type in leaveTypes" :key="type.id" :value="type.id">
                      {{ type.name }}
                    </SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div v-if="filteredEmployees.length > 0 && form.team_id" class="space-y-2">
                <Label for="employee">Employee</Label>
                <Select v-model="form.user_id">
                  <SelectTrigger id="employee" class="w-full">
                    <SelectValue placeholder="All employees" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="">All employees</SelectItem>
                    <SelectItem v-for="employee in filteredEmployees" :key="employee.id" :value="employee.id">
                      {{ employee.name }}
                    </SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </div>

            <div class="flex items-center justify-end gap-2">
              <Button type="button" variant="outline" @click="resetForm">Reset</Button>
              <Button type="submit" :disabled="isLoading">
                <Loader2Icon v-if="isLoading" class="mr-2 h-4 w-4 animate-spin" />
                Generate Report
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>

      <Card v-if="report">
        <CardHeader class="flex flex-row items-center justify-between">
          <div>
            <CardTitle>{{ getReportTitle }}</CardTitle>
            <CardDescription>
              {{ new Date(report.start_date).toLocaleDateString() }} to
              {{ new Date(report.end_date).toLocaleDateString() }}
            </CardDescription>
          </div>
          <div class="flex items-center gap-2">
            <Button variant="outline" size="sm" @click="printReport">
              <PrinterIcon class="mr-2 h-4 w-4" />
              Print
            </Button>
            <Button variant="outline" size="sm" @click="exportReport">
              <DownloadIcon class="mr-2 h-4 w-4" />
              Export
            </Button>
          </div>
        </CardHeader>
        <CardContent>
          <div v-if="report.report_type === 'leave-summary'" class="space-y-8">
            <div class="grid gap-6 md:grid-cols-4">
              <Card>
                <CardHeader class="pb-2">
                  <CardTitle class="text-sm font-medium">Total Requests</CardTitle>
                </CardHeader>
                <CardContent>
                  <div class="text-2xl font-bold">{{ report.data.total_leave_requests }}</div>
                  <p class="text-xs text-muted-foreground">{{ report.data.total_days }} days</p>
                </CardContent>
              </Card>
              <Card>
                <CardHeader class="pb-2">
                  <CardTitle class="text-sm font-medium">Approved</CardTitle>
                </CardHeader>
                <CardContent>
                  <div class="text-2xl font-bold">{{ report.data.approved_requests }}</div>
                  <p class="text-xs text-muted-foreground">{{ report.data.approved_days }} days</p>
                </CardContent>
              </Card>
              <Card>
                <CardHeader class="pb-2">
                  <CardTitle class="text-sm font-medium">Pending</CardTitle>
                </CardHeader>
                <CardContent>
                  <div class="text-2xl font-bold">{{ report.data.pending_requests }}</div>
                  <p class="text-xs text-muted-foreground">{{ report.data.pending_days }} days</p>
                </CardContent>
              </Card>
              <Card>
                <CardHeader class="pb-2">
                  <CardTitle class="text-sm font-medium">Rejected</CardTitle>
                </CardHeader>
                <CardContent>
                  <div class="text-2xl font-bold">{{ report.data.rejected_requests }}</div>
                  <p class="text-xs text-muted-foreground">{{ report.data.rejected_days }} days</p>
                </CardContent>
              </Card>
            </div>

            <div class="h-[350px] w-full">
              <ResponsiveContainer>
                <PieChart>
                  <Pie
                    data={report.chart_data}
                    cx="50%"
                    cy="50%"
                    labelLine={false}
                    outerRadius={120}
                    fill="#8884d8"
                    dataKey="value"
                    nameKey="name"
                    label
                  >
                    <Cell key="approved" fill="#4ade80" />
                    <Cell key="pending" fill="#facc15" />
                    <Cell key="rejected" fill="#f87171" />
                  </Pie>
                  <Tooltip />
                  <Legend />
                </PieChart>
              </ResponsiveContainer>
            </div>
          </div>

          <div v-else-if="['leave-by-type', 'leave-by-team'].includes(report.report_type)" class="space-y-8">
            <div class="h-[350px] w-full">
              <ResponsiveContainer>
                <BarChart data={report.chart_data}>
                  <XAxis dataKey="name" />
                  <YAxis />
                  <Tooltip />
                  <Legend />
                  <Bar dataKey="value" name="Days" fill="#8884d8" />
                </BarChart>
              </ResponsiveContainer>
            </div>

            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>{{ report.report_type === 'leave-by-type' ? 'Leave Type' : 'Team' }}</TableHead>
                  <TableHead class="text-right">Requests</TableHead>
                  <TableHead class="text-right">Days</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="(item, i) in report.data" :key="i">
                  <TableCell>{{ report.report_type === 'leave-by-type' ? item.type : item.team }}</TableCell>
                  <TableCell class="text-right">{{ item.count }}</TableCell>
                  <TableCell class="text-right">{{ item.days }}</TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>

          <div v-else-if="report.report_type === 'leave-by-employee'" class="space-y-8">
            <div class="h-[350px] w-full">
              <ResponsiveContainer>
                <BarChart data={report.chart_data}>
                  <XAxis dataKey="name" />
                  <YAxis />
                  <Tooltip />
                  <Legend />
                  <Bar dataKey="value" name="Days" fill="#8884d8" />
                </BarChart>
              </ResponsiveContainer>
            </div>

            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Employee</TableHead>
                  <TableHead>Email</TableHead>
                  <TableHead class="text-right">Requests</TableHead>
                  <TableHead class="text-right">Days</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="(item, i) in report.data" :key="i">
                  <TableCell>{{ item.employee }}</TableCell>
                  <TableCell>{{ item.email }}</TableCell>
                  <TableCell class="text-right">{{ item.count }}</TableCell>
                  <TableCell class="text-right">{{ item.days }}</TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>

          <div v-else-if="report.report_type === 'leave-calendar'" class="space-y-8">
            <div class="h-[350px] w-full">
              <ResponsiveContainer>
                <LineChart data={report.chart_data}>
                  <XAxis dataKey="name" />
                  <YAxis />
                  <Tooltip />
                  <Legend />
                  <Line type="monotone" dataKey="value" name="Employees on leave" stroke="#8884d8" />
                </LineChart>
              </ResponsiveContainer>
            </div>

            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Date</TableHead>
                  <TableHead class="text-right">Employees on Leave</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="(item, i) in report.data" :key="i">
                  <TableCell>{{ new Date(item.date).toLocaleDateString() }}</TableCell>
                  <TableCell class="text-right">{{ item.count }}</TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
        </CardContent>
      </Card>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Button } from '@/components/ui/button';
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';
import { Calendar } from '@/components/ui/calendar';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/components/ui/popover';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import { useToast } from '@/components/ui/toast';
import { axios } from '@/lib/axios';
import { defineProps } from 'vue';
import { ref, computed, watch } from 'vue';
import {
  BarChart,
  Bar,
  Cell,
  LineChart,
  Line,
  PieChart,
  Pie,
  ResponsiveContainer,
  Tooltip,
  Legend,
  XAxis,
  YAxis
} from 'recharts';
import {
  CalendarIcon,
  DownloadIcon,
  Loader2Icon,
  PrinterIcon
} from 'lucide-vue-next';
import { toast } from 'vue-sonner';
import { useLocalStorage } from '@vueuse/core';

// Props
const props = defineProps<{
  stats: {
    total_leave_requests: number;
    pending_leave_requests: number;
    approved_leave_requests: number;
    rejected_leave_requests: number;
  };
  reportTypes: { id: string; name: string }[];
}>();

// State
const form = ref({
  report_type: '',
  start_date: new Date(),
  end_date: new Date(),
  team_id: '',
  user_id: '',
  leave_type_id: '',
  status: '',
});

const isLoading = ref(false);
const report = ref(null);

const teams = ref([]);
const employees = ref([]);
const leaveTypes = ref([]);

// Fetch required data
async function fetchFilters() {
  try {
    const [teamsResponse, employeesResponse, leaveTypesResponse] = await Promise.all([
      axios.get(route('admin.teams.list')),
      axios.get(route('admin.users.list')),
      axios.get(route('admin.leave-types.list')),
    ]);

    teams.value = teamsResponse.data;
    employees.value = employeesResponse.data;
    leaveTypes.value = leaveTypesResponse.data;
  } catch (error) {
    toast.error('Failed to load filter options');
    console.error('Error loading filters:', error);
  }
}

// Call fetch filters on component mounted
fetchFilters();

// Computed properties
const filteredEmployees = computed(() => {
  if (!form.value.team_id) return [];
  return employees.value.filter(employee => employee.team_id === form.value.team_id);
});

const getReportTitle = computed(() => {
  const reportType = props.reportTypes.find(type => type.id === report.value?.report_type);
  return reportType ? reportType.name : 'Report';
});

// Methods
async function generateReport() {
  if (!form.value.report_type || !form.value.start_date || !form.value.end_date) {
    toast.error('Please select report type and date range');
    return;
  }

  isLoading.value = true;

  try {
    const response = await axios.post(route('admin.reports.generate'), {
      report_type: form.value.report_type,
      start_date: form.value.start_date,
      end_date: form.value.end_date,
      team_id: form.value.team_id || null,
      user_id: form.value.user_id || null,
      leave_type_id: form.value.leave_type_id || null,
      status: form.value.status || null,
    });

    report.value = response.data;
    toast.success('Report generated successfully');
  } catch (error) {
    toast.error('Failed to generate report');
    console.error('Error generating report:', error);
  } finally {
    isLoading.value = false;
  }
}

function resetForm() {
  form.value = {
    report_type: '',
    start_date: new Date(),
    end_date: new Date(),
    team_id: '',
    user_id: '',
    leave_type_id: '',
    status: '',
  };
  report.value = null;
}

function printReport() {
  window.print();
}

async function exportReport() {
  if (!report.value) return;

  isLoading.value = true;

  try {
    await axios.post(route('admin.reports.export'), {
      report_type: form.value.report_type,
      start_date: form.value.start_date,
      end_date: form.value.end_date,
      team_id: form.value.team_id || null,
      user_id: form.value.user_id || null,
      leave_type_id: form.value.leave_type_id || null,
      status: form.value.status || null,
    }, { responseType: 'blob' });

    toast.success('Report exported successfully');
  } catch (error) {
    toast.error('Failed to export report');
    console.error('Error exporting report:', error);
  } finally {
    isLoading.value = false;
  }
}

// Reset user_id when team_id changes
watch(() => form.value.team_id, () => {
  form.value.user_id = '';
});
</script>

<style>
@media print {
  .no-print {
    display: none;
  }
}
</style>
