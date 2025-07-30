<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { FileCheckIcon, PlusIcon, EditIcon, TrashIcon, CalendarDaysIcon, ClockIcon, DollarSignIcon } from 'lucide-vue-next';

const page = usePage<SharedData>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin Dashboard',
        href: route('admin.dashboard'),
    },
    {
        title: 'Leave Types Management',
        href: route('admin.leave-types.index'),
    },
];

// Get company info
const company = computed(() => page.props.auth.company);

// Mock leave types data - this would come from the backend
const leaveTypes = computed(() => [
    {
        id: 1,
        name: 'Annual Leave',
        description: 'Yearly vacation leave',
        days_allowed: 21,
        min_duration: 1,
        max_duration: 21,
        allow_custom_duration: true,
        gender: null,
        requires_approval: true,
        requires_documentation: false,
        full_pay_days: 21,
        half_pay_days: 0,
        usage_count: 45,
        created_at: '2024-01-15T09:00:00Z',
    },
    {
        id: 2,
        name: 'Sick Leave',
        description: 'Medical leave for illness',
        days_allowed: 10,
        min_duration: 1,
        max_duration: 10,
        allow_custom_duration: true,
        gender: null,
        requires_approval: false,
        requires_documentation: true,
        full_pay_days: 10,
        half_pay_days: 0,
        usage_count: 23,
        created_at: '2024-01-15T09:00:00Z',
    },
    {
        id: 3,
        name: 'Maternity Leave',
        description: 'Leave for new mothers',
        days_allowed: 90,
        min_duration: 30,
        max_duration: 90,
        allow_custom_duration: false,
        gender: 'female',
        requires_approval: true,
        requires_documentation: true,
        full_pay_days: 60,
        half_pay_days: 30,
        usage_count: 3,
        created_at: '2024-01-15T09:00:00Z',
    },
    {
        id: 4,
        name: 'Paternity Leave',
        description: 'Leave for new fathers',
        days_allowed: 14,
        min_duration: 7,
        max_duration: 14,
        allow_custom_duration: false,
        gender: 'male',
        requires_approval: true,
        requires_documentation: false,
        full_pay_days: 14,
        half_pay_days: 0,
        usage_count: 5,
        created_at: '2024-01-15T09:00:00Z',
    },
    {
        id: 5,
        name: 'Personal Leave',
        description: 'Personal time off',
        days_allowed: 5,
        min_duration: 1,
        max_duration: 5,
        allow_custom_duration: true,
        gender: null,
        requires_approval: true,
        requires_documentation: false,
        full_pay_days: 0,
        half_pay_days: 0,
        usage_count: 12,
        created_at: '2024-02-01T10:30:00Z',
    },
]);

const getPayDescription = (fullPay: number, halfPay: number) => {
    if (fullPay === 0 && halfPay === 0) return 'Unpaid';
    if (halfPay > 0) {
        return `${fullPay} days full + ${halfPay} days half`;
    }
    return `${fullPay} days full pay`;
};

const getGenderBadge = (gender: string | null) => {
    if (!gender) return null;
    return gender === 'male' ? 'Male Only' : 'Female Only';
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

// Stats
const stats = computed(() => ({
    totalLeaveTypes: leaveTypes.value.length,
    requireApproval: leaveTypes.value.filter(t => t.requires_approval).length,
    requireDocumentation: leaveTypes.value.filter(t => t.requires_documentation).length,
    genderSpecific: leaveTypes.value.filter(t => t.gender).length,
    totalUsage: leaveTypes.value.reduce((sum, type) => sum + type.usage_count, 0),
}));
</script>

<template>
    <Head title="Leave Types Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
            <!-- Header Section -->
            <div class="flex items-center justify-between">
                <div class="space-y-2">
                    <h1 class="text-3xl font-bold tracking-tight">
                        Leave Types Management
                    </h1>
                    <p class="text-muted-foreground">
                        Configure and manage leave types for {{ company?.name }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <Button as-child>
                        <a :href="route('admin.leave-types.create')">
                            <PlusIcon class="h-4 w-4 mr-2" />
                            Add Leave Type
                        </a>
                    </Button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-5">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Types</CardTitle>
                        <FileCheckIcon class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.totalLeaveTypes }}</div>
                        <p class="text-xs text-muted-foreground">
                            Active leave types
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Require Approval</CardTitle>
                        <ClockIcon class="h-4 w-4 text-orange-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.requireApproval }}</div>
                        <p class="text-xs text-muted-foreground">
                            Need manager approval
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Require Docs</CardTitle>
                        <FileCheckIcon class="h-4 w-4 text-blue-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.requireDocumentation }}</div>
                        <p class="text-xs text-muted-foreground">
                            Need documentation
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Gender Specific</CardTitle>
                        <FileCheckIcon class="h-4 w-4 text-purple-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.genderSpecific }}</div>
                        <p class="text-xs text-muted-foreground">
                            Gender restricted
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Usage</CardTitle>
                        <CalendarDaysIcon class="h-4 w-4 text-green-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.totalUsage }}</div>
                        <p class="text-xs text-muted-foreground">
                            Requests this year
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Leave Types Grid -->
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <Card v-for="leaveType in leaveTypes" :key="leaveType.id" class="relative">
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <CardTitle class="text-lg">{{ leaveType.name }}</CardTitle>
                            <div class="flex gap-1">
                                <Badge v-if="getGenderBadge(leaveType.gender)" variant="secondary" class="text-xs">
                                    {{ getGenderBadge(leaveType.gender) }}
                                </Badge>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    as-child
                                >
                                    <a :href="route('admin.leave-types.edit', leaveType.id)">
                                        <EditIcon class="h-4 w-4" />
                                    </a>
                                </Button>
                            </div>
                        </div>
                        <CardDescription>{{ leaveType.description }}</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <!-- Days Allowed -->
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium">Days Allowed</span>
                            <div class="flex items-center gap-1">
                                <CalendarDaysIcon class="h-4 w-4 text-muted-foreground" />
                                <span class="font-semibold">{{ leaveType.days_allowed }}</span>
                            </div>
                        </div>

                        <!-- Duration Range -->
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium">Duration Range</span>
                            <span class="text-sm">{{ leaveType.min_duration }} - {{ leaveType.max_duration }} days</span>
                        </div>

                        <!-- Pay Structure -->
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium">Pay Structure</span>
                            <div class="flex items-center gap-1">
                                <DollarSignIcon class="h-4 w-4 text-muted-foreground" />
                                <span class="text-sm">{{ getPayDescription(leaveType.full_pay_days, leaveType.half_pay_days) }}</span>
                            </div>
                        </div>

                        <!-- Usage Count -->
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium">Usage This Year</span>
                            <Badge variant="outline">
                                {{ leaveType.usage_count }} requests
                            </Badge>
                        </div>

                        <!-- Requirements -->
                        <div class="space-y-2">
                            <span class="text-sm font-medium">Requirements</span>
                            <div class="flex flex-wrap gap-1">
                                <Badge
                                    v-if="leaveType.requires_approval"
                                    variant="outline"
                                    class="text-xs"
                                >
                                    Approval Required
                                </Badge>
                                <Badge
                                    v-if="leaveType.requires_documentation"
                                    variant="outline"
                                    class="text-xs"
                                >
                                    Documentation Required
                                </Badge>
                                <Badge
                                    v-if="leaveType.allow_custom_duration"
                                    variant="outline"
                                    class="text-xs"
                                >
                                    Custom Duration
                                </Badge>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="grid gap-2 pt-2">
                            <Button class="w-full" variant="outline" as-child>
                                <a :href="route('admin.leave-types.edit', leaveType.id)">
                                    Edit Leave Type
                                </a>
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Detailed Table View -->
            <Card>
                <CardHeader>
                    <CardTitle>All Leave Types</CardTitle>
                    <CardDescription>
                        Complete overview of all leave types configured for {{ company?.name }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Days Allowed</TableHead>
                                <TableHead>Duration Range</TableHead>
                                <TableHead>Pay Structure</TableHead>
                                <TableHead>Requirements</TableHead>
                                <TableHead>Usage</TableHead>
                                <TableHead>Created</TableHead>
                                <TableHead>Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="leaveType in leaveTypes" :key="leaveType.id">
                                <TableCell class="font-medium">
                                    <div>
                                        <div class="font-medium">{{ leaveType.name }}</div>
                                        <div class="text-sm text-muted-foreground">{{ leaveType.description }}</div>
                                        <div v-if="leaveType.gender" class="mt-1">
                                            <Badge variant="secondary" class="text-xs">
                                                {{ getGenderBadge(leaveType.gender) }}
                                            </Badge>
                                        </div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center gap-1">
                                        <CalendarDaysIcon class="h-4 w-4 text-muted-foreground" />
                                        <span class="font-semibold">{{ leaveType.days_allowed }}</span>
                                    </div>
                                </TableCell>
                                <TableCell>{{ leaveType.min_duration }}-{{ leaveType.max_duration }} days</TableCell>
                                <TableCell>
                                    <div class="flex items-center gap-1">
                                        <DollarSignIcon class="h-4 w-4 text-muted-foreground" />
                                        <span class="text-sm">{{ getPayDescription(leaveType.full_pay_days, leaveType.half_pay_days) }}</span>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div class="flex flex-wrap gap-1">
                                        <Badge
                                            v-if="leaveType.requires_approval"
                                            variant="outline"
                                            class="text-xs"
                                        >
                                            Approval
                                        </Badge>
                                        <Badge
                                            v-if="leaveType.requires_documentation"
                                            variant="outline"
                                            class="text-xs"
                                        >
                                            Docs
                                        </Badge>
                                        <Badge
                                            v-if="leaveType.allow_custom_duration"
                                            variant="outline"
                                            class="text-xs"
                                        >
                                            Custom
                                        </Badge>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge variant="outline">
                                        {{ leaveType.usage_count }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <span class="text-sm">{{ formatDate(leaveType.created_at) }}</span>
                                </TableCell>
                                <TableCell>
                                    <div class="flex gap-2">
                                        <Button variant="outline" size="sm" as-child>
                                            <a :href="route('admin.leave-types.edit', leaveType.id)">
                                                <EditIcon class="h-4 w-4" />
                                            </a>
                                        </Button>
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            class="text-destructive hover:text-destructive"
                                        >
                                            <TrashIcon class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <!-- Quick Actions and Analytics -->
            <div class="grid gap-6 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Quick Actions</CardTitle>
                        <CardDescription>Common leave type management tasks</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-3">
                            <Button variant="outline" class="justify-start" as-child>
                                <a :href="route('admin.leave-types.create')">
                                    <PlusIcon class="h-4 w-4 mr-2" />
                                    Create New Leave Type
                                </a>
                            </Button>

                            <Button variant="outline" class="justify-start">
                                <FileCheckIcon class="h-4 w-4 mr-2" />
                                Bulk Edit Leave Types
                            </Button>

                            <Button variant="outline" class="justify-start">
                                <CalendarDaysIcon class="h-4 w-4 mr-2" />
                                Import Leave Types
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Usage Analytics</CardTitle>
                        <CardDescription>Leave type usage patterns and insights</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium">Most Used Type</span>
                                <span class="text-sm font-bold">Annual Leave (45)</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium">Least Used Type</span>
                                <span class="text-sm font-bold">Maternity Leave (3)</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium">Average Days per Type</span>
                                <span class="text-sm font-bold">{{ Math.round(leaveTypes.reduce((sum, type) => sum + type.days_allowed, 0) / leaveTypes.length) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium">Approval Rate</span>
                                <span class="text-sm font-bold">{{ Math.round((stats.requireApproval / stats.totalLeaveTypes) * 100) }}%</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
