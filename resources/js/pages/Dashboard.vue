<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { CalendarIcon, UsersIcon, FileCheckIcon, TrendingUpIcon } from 'lucide-vue-next';

const page = usePage<SharedData>();

// Get user info and company
const user = computed(() => page.props.auth.user);
const company = computed(() => page.props.auth.company);
const userRoles = computed(() => user.value?.roles?.map(role => role.name) || []);
const isAdmin = computed(() => userRoles.value.includes('admin'));
const isHR = computed(() => userRoles.value.includes('hr'));
const isManager = computed(() => userRoles.value.includes('manager'));

// Role-aware breadcrumbs
const breadcrumbs = computed((): BreadcrumbItem[] => [
    {
        title: 'Dashboard',
        href: isAdmin.value ? route('admin.dashboard') : route('dashboard'),
    },
]);

// Welcome message based on role
const welcomeMessage = computed(() => {
    if (isAdmin.value) return `Welcome back, ${user.value?.name}! Here's your company overview.`;
    if (isHR.value) return `Welcome back, ${user.value?.name}! Here's your HR dashboard.`;
    if (isManager.value) return `Welcome back, ${user.value?.name}! Here's your team overview.`;
    return `Welcome back, ${user.value?.name}! Here's your dashboard.`;
});

// Mock stats - these would come from the backend in a real implementation
const stats = computed(() => ({
    totalEmployees: 45,
    pendingRequests: 8,
    approvedThisMonth: 23,
    leaveTypes: 6,
}));
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
            <!-- Welcome Section -->
            <div class="space-y-2">
                <h1 class="text-3xl font-bold tracking-tight">
                    {{ company?.name || 'Dashboard' }}
                </h1>
                <p class="text-muted-foreground">
                    {{ welcomeMessage }}
                </p>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <!-- Total Employees (Admin/HR only) -->
                <Card v-if="isAdmin || isHR">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Employees</CardTitle>
                        <UsersIcon class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.totalEmployees }}</div>
                        <p class="text-xs text-muted-foreground">
                            +2 from last month
                        </p>
                    </CardContent>
                </Card>

                <!-- Pending Requests -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">
                            {{ isAdmin || isHR || isManager ? 'Pending Requests' : 'My Requests' }}
                        </CardTitle>
                        <CalendarIcon class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.pendingRequests }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ isAdmin || isHR || isManager ? 'Awaiting approval' : 'Pending approval' }}
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
                        <div class="text-2xl font-bold">{{ stats.approvedThisMonth }}</div>
                        <p class="text-xs text-muted-foreground">
                            +12% from last month
                        </p>
                    </CardContent>
                </Card>

                <!-- Leave Types (Admin/HR only) -->
                <Card v-if="isAdmin || isHR">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Leave Types</CardTitle>
                        <FileCheckIcon class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.leaveTypes }}</div>
                        <p class="text-xs text-muted-foreground">
                            Active leave types
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Quick Actions -->
            <div class="grid gap-4 md:grid-cols-2">
                <!-- Recent Activity -->
                <Card>
                    <CardHeader>
                        <CardTitle>Recent Activity</CardTitle>
                        <CardDescription>Latest leave requests and updates</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div class="flex items-center space-x-4">
                                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                <div class="flex-1 space-y-1">
                                    <p class="text-sm font-medium">John Doe's annual leave approved</p>
                                    <p class="text-xs text-muted-foreground">2 hours ago</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-4">
                                <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
                                <div class="flex-1 space-y-1">
                                    <p class="text-sm font-medium">New sick leave request from Jane Smith</p>
                                    <p class="text-xs text-muted-foreground">4 hours ago</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-4">
                                <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                <div class="flex-1 space-y-1">
                                    <p class="text-sm font-medium">Team meeting scheduled</p>
                                    <p class="text-xs text-muted-foreground">1 day ago</p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Quick Actions -->
                <Card>
                    <CardHeader>
                        <CardTitle>Quick Actions</CardTitle>
                        <CardDescription>Common tasks and shortcuts</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-2">
                            <a
                                :href="route('leave-requests.create')"
                                class="flex items-center justify-between p-3 rounded-lg border hover:bg-accent transition-colors"
                            >
                                <span class="text-sm font-medium">Request Leave</span>
                                <CalendarIcon class="h-4 w-4" />
                            </a>

                            <a
                                :href="route('leave-requests.index')"
                                class="flex items-center justify-between p-3 rounded-lg border hover:bg-accent transition-colors"
                            >
                                <span class="text-sm font-medium">View My Requests</span>
                                <FileCheckIcon class="h-4 w-4" />
                            </a>

                            <a
                                v-if="isAdmin"
                                :href="route('admin.company.profile')"
                                class="flex items-center justify-between p-3 rounded-lg border hover:bg-accent transition-colors"
                            >
                                <span class="text-sm font-medium">Company Settings</span>
                                <UsersIcon class="h-4 w-4" />
                            </a>

                            <a
                                v-if="isAdmin || isHR"
                                :href="route('admin.users.index')"
                                class="flex items-center justify-between p-3 rounded-lg border hover:bg-accent transition-colors"
                            >
                                <span class="text-sm font-medium">Manage Users</span>
                                <UsersIcon class="h-4 w-4" />
                            </a>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
