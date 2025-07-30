<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { CalendarIcon, UsersIcon, FileCheckIcon, TrendingUpIcon, Building2, BarChart3, Settings } from 'lucide-vue-next';

const page = usePage<SharedData>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin Dashboard',
        href: route('admin.dashboard'),
    },
];

// Get user info and company
const user = computed(() => page.props.auth.user);
const company = computed(() => page.props.auth.company);

// Mock admin stats - these would come from the backend in a real implementation
const adminStats = computed(() => ({
    totalEmployees: 45,
    totalTeams: 8,
    pendingRequests: 12,
    approvedThisMonth: 34,
    leaveTypes: 6,
    activeUsers: 42,
}));
</script>

<template>
    <Head title="Admin Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
            <!-- Header Section -->
            <div class="flex items-center justify-between">
                <div class="space-y-2">
                    <h1 class="text-3xl font-bold tracking-tight">
                        Admin Dashboard
                    </h1>
                    <p class="text-muted-foreground">
                        Manage your company's leave management system
                    </p>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" as-child>
                        <a :href="route('admin.company.profile')">
                            <Settings class="h-4 w-4 mr-2" />
                            Company Settings
                        </a>
                    </Button>
                    <Button as-child>
                        <a :href="route('admin.reports.index')">
                            <BarChart3 class="h-4 w-4 mr-2" />
                            View Reports
                        </a>
                    </Button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <!-- Total Employees -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Employees</CardTitle>
                        <UsersIcon class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ adminStats.totalEmployees }}</div>
                        <p class="text-xs text-muted-foreground">
                            +3 from last month
                        </p>
                    </CardContent>
                </Card>

                <!-- Total Teams -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Teams</CardTitle>
                        <Building2 class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ adminStats.totalTeams }}</div>
                        <p class="text-xs text-muted-foreground">
                            Across all departments
                        </p>
                    </CardContent>
                </Card>

                <!-- Pending Requests -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Pending Requests</CardTitle>
                        <CalendarIcon class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ adminStats.pendingRequests }}</div>
                        <p class="text-xs text-muted-foreground">
                            Awaiting approval
                        </p>
                    </CardContent>
                </Card>

                <!-- Approved This Month -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Approved This Month</CardTitle>
                        <TrendingUpIcon class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ adminStats.approvedThisMonth }}</div>
                        <p class="text-xs text-muted-foreground">
                            +18% from last month
                        </p>
                    </CardContent>
                </Card>

                <!-- Leave Types -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Leave Types</CardTitle>
                        <FileCheckIcon class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ adminStats.leaveTypes }}</div>
                        <p class="text-xs text-muted-foreground">
                            Active leave types
                        </p>
                    </CardContent>
                </Card>

                <!-- Active Users -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Active Users</CardTitle>
                        <UsersIcon class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ adminStats.activeUsers }}</div>
                        <p class="text-xs text-muted-foreground">
                            Last 30 days
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Management Sections -->
            <div class="grid gap-6 md:grid-cols-2">
                <!-- Quick Management Actions -->
                <Card>
                    <CardHeader>
                        <CardTitle>Quick Management</CardTitle>
                        <CardDescription>Common administrative tasks</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-3">
                            <Button variant="outline" class="justify-start" as-child>
                                <a :href="route('admin.users.index')">
                                    <UsersIcon class="h-4 w-4 mr-2" />
                                    Manage Users
                                </a>
                            </Button>

                            <Button variant="outline" class="justify-start" as-child>
                                <a :href="route('admin.teams.index')">
                                    <Building2 class="h-4 w-4 mr-2" />
                                    Manage Teams
                                </a>
                            </Button>

                            <Button variant="outline" class="justify-start" as-child>
                                <a :href="route('admin.leave-types.index')">
                                    <FileCheckIcon class="h-4 w-4 mr-2" />
                                    Manage Leave Types
                                </a>
                            </Button>

                            <Button variant="outline" class="justify-start" as-child>
                                <a :href="route('leave-requests.index')">
                                    <CalendarIcon class="h-4 w-4 mr-2" />
                                    Review Leave Requests
                                </a>
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <!-- Recent Activity -->
                <Card>
                    <CardHeader>
                        <CardTitle>Recent Activity</CardTitle>
                        <CardDescription>Latest system activities</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div class="flex items-center space-x-4">
                                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                <div class="flex-1 space-y-1">
                                    <p class="text-sm font-medium">New user John Smith added to Engineering team</p>
                                    <p class="text-xs text-muted-foreground">2 hours ago</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-4">
                                <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                <div class="flex-1 space-y-1">
                                    <p class="text-sm font-medium">Leave type "Parental Leave" updated</p>
                                    <p class="text-xs text-muted-foreground">4 hours ago</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-4">
                                <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
                                <div class="flex-1 space-y-1">
                                    <p class="text-sm font-medium">5 leave requests pending approval</p>
                                    <p class="text-xs text-muted-foreground">6 hours ago</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-4">
                                <div class="w-2 h-2 bg-purple-500 rounded-full"></div>
                                <div class="flex-1 space-y-1">
                                    <p class="text-sm font-medium">Monthly report generated</p>
                                    <p class="text-xs text-muted-foreground">1 day ago</p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Company Overview -->
            <Card>
                <CardHeader>
                    <CardTitle>Company Overview</CardTitle>
                    <CardDescription>{{ company?.name }} - System Status</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600">98%</div>
                            <p class="text-sm text-muted-foreground">System Uptime</p>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-blue-600">{{ company?.max_employees || 50 }}</div>
                            <p class="text-sm text-muted-foreground">Employee Limit</p>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-600">{{ company?.subscription_plan || 'Basic' }}</div>
                            <p class="text-sm text-muted-foreground">Subscription Plan</p>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-orange-600">{{ company?.timezone || 'UTC' }}</div>
                            <p class="text-sm text-muted-foreground">Timezone</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
