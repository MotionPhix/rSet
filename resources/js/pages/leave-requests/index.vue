<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { CalendarIcon, PlusIcon, FilterIcon, DownloadIcon } from 'lucide-vue-next';

const page = usePage<SharedData>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Leave Requests',
        href: route('leave-requests.index'),
    },
];

// Get user info and roles
const user = computed(() => page.props.auth.user);
const userRoles = computed(() => user.value?.roles?.map(role => role.name) || []);
const isAdmin = computed(() => userRoles.value.includes('admin'));
const isHR = computed(() => userRoles.value.includes('hr'));
const isManager = computed(() => userRoles.value.includes('manager'));

// Mock leave requests data - this would come from the backend
const leaveRequests = computed(() => [
    {
        id: 1,
        user: { name: 'John Doe', email: 'john@company.com' },
        type: 'Annual Leave',
        start_date: '2024-08-15',
        end_date: '2024-08-20',
        days: 5,
        status: 'pending',
        reason: 'Family vacation',
        created_at: '2024-07-25',
    },
    {
        id: 2,
        user: { name: 'Jane Smith', email: 'jane@company.com' },
        type: 'Sick Leave',
        start_date: '2024-07-30',
        end_date: '2024-07-31',
        days: 2,
        status: 'approved',
        reason: 'Medical appointment',
        created_at: '2024-07-28',
    },
    {
        id: 3,
        user: { name: 'Mike Johnson', email: 'mike@company.com' },
        type: 'Personal Leave',
        start_date: '2024-08-05',
        end_date: '2024-08-05',
        days: 1,
        status: 'rejected',
        reason: 'Personal matters',
        created_at: '2024-07-20',
    },
]);

const getStatusColor = (status: string) => {
    switch (status) {
        case 'pending': return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
        case 'approved': return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        case 'rejected': return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
        default: return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
    }
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const pageTitle = computed(() => {
    if (isAdmin.value || isHR.value) return 'All Leave Requests';
    if (isManager.value) return 'Team Leave Requests';
    return 'My Leave Requests';
});
</script>

<template>
    <Head title="Leave Requests" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
            <!-- Header Section -->
            <div class="flex items-center justify-between">
                <div class="space-y-2">
                    <h1 class="text-3xl font-bold tracking-tight">
                        {{ pageTitle }}
                    </h1>
                    <p class="text-muted-foreground">
                        {{ isAdmin || isHR || isManager ? 'Manage and review leave requests' : 'View and manage your leave requests' }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline">
                        <FilterIcon class="h-4 w-4 mr-2" />
                        Filter
                    </Button>
                    <Button variant="outline" v-if="isAdmin || isHR">
                        <DownloadIcon class="h-4 w-4 mr-2" />
                        Export
                    </Button>
                    <Button as-child>
                        <a :href="route('leave-requests.create')">
                            <PlusIcon class="h-4 w-4 mr-2" />
                            New Request
                        </a>
                    </Button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Requests</CardTitle>
                        <CalendarIcon class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ leaveRequests.length }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Pending</CardTitle>
                        <CalendarIcon class="h-4 w-4 text-yellow-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ leaveRequests.filter(r => r.status === 'pending').length }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Approved</CardTitle>
                        <CalendarIcon class="h-4 w-4 text-green-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ leaveRequests.filter(r => r.status === 'approved').length }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Rejected</CardTitle>
                        <CalendarIcon class="h-4 w-4 text-red-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ leaveRequests.filter(r => r.status === 'rejected').length }}</div>
                    </CardContent>
                </Card>
            </div>

            <!-- Leave Requests Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Leave Requests</CardTitle>
                    <CardDescription>
                        {{ isAdmin || isHR || isManager ? 'Review and manage leave requests from your team' : 'Your submitted leave requests' }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead v-if="isAdmin || isHR || isManager">Employee</TableHead>
                                <TableHead>Leave Type</TableHead>
                                <TableHead>Start Date</TableHead>
                                <TableHead>End Date</TableHead>
                                <TableHead>Days</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Reason</TableHead>
                                <TableHead>Submitted</TableHead>
                                <TableHead>Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="request in leaveRequests" :key="request.id">
                                <TableCell v-if="isAdmin || isHR || isManager" class="font-medium">
                                    <div>
                                        <div class="font-medium">{{ request.user.name }}</div>
                                        <div class="text-sm text-muted-foreground">{{ request.user.email }}</div>
                                    </div>
                                </TableCell>
                                <TableCell>{{ request.type }}</TableCell>
                                <TableCell>{{ formatDate(request.start_date) }}</TableCell>
                                <TableCell>{{ formatDate(request.end_date) }}</TableCell>
                                <TableCell>{{ request.days }}</TableCell>
                                <TableCell>
                                    <Badge :class="getStatusColor(request.status)" variant="secondary">
                                        {{ request.status.charAt(0).toUpperCase() + request.status.slice(1) }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="max-w-xs truncate">{{ request.reason }}</TableCell>
                                <TableCell>{{ formatDate(request.created_at) }}</TableCell>
                                <TableCell>
                                    <div class="flex gap-2">
                                        <Button variant="outline" size="sm" as-child>
                                            <a :href="route('leave-requests.show', request.id)">
                                                View
                                            </a>
                                        </Button>
                                        <Button
                                            v-if="request.status === 'pending' && (isAdmin || isHR || isManager)"
                                            variant="outline"
                                            size="sm"
                                        >
                                            Approve
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
