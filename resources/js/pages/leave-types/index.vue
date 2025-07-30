<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { FileCheckIcon, PlusIcon, EditIcon, CalendarDaysIcon } from 'lucide-vue-next';

const page = usePage<SharedData>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Leave Types',
        href: route('leave-types.index'),
    },
];

// Get user info and roles
const user = computed(() => page.props.auth.user);
const userRoles = computed(() => user.value?.roles?.map(role => role.name) || []);
const isAdmin = computed(() => userRoles.value.includes('admin'));
const isHR = computed(() => userRoles.value.includes('hr'));

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
    },
]);

const getPayDescription = (fullPay: number, halfPay: number) => {
    if (halfPay > 0) {
        return `${fullPay} days full pay + ${halfPay} days half pay`;
    }
    return `${fullPay} days full pay`;
};

const getGenderBadge = (gender: string | null) => {
    if (!gender) return null;
    return gender === 'male' ? 'Male Only' : 'Female Only';
};
</script>

<template>
    <Head title="Leave Types" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
            <!-- Header Section -->
            <div class="flex items-center justify-between">
                <div class="space-y-2">
                    <h1 class="text-3xl font-bold tracking-tight">
                        Leave Types
                    </h1>
                    <p class="text-muted-foreground">
                        {{ isAdmin || isHR ? 'Manage available leave types for your company' : 'View available leave types' }}
                    </p>
                </div>
                <div class="flex gap-2" v-if="isAdmin || isHR">
                    <Button as-child>
                        <a :href="route('admin.leave-types.create')">
                            <PlusIcon class="h-4 w-4 mr-2" />
                            Add Leave Type
                        </a>
                    </Button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Leave Types</CardTitle>
                        <FileCheckIcon class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ leaveTypes.length }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Require Approval</CardTitle>
                        <FileCheckIcon class="h-4 w-4 text-orange-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ leaveTypes.filter(t => t.requires_approval).length }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Require Documentation</CardTitle>
                        <FileCheckIcon class="h-4 w-4 text-blue-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ leaveTypes.filter(t => t.requires_documentation).length }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Gender Specific</CardTitle>
                        <FileCheckIcon class="h-4 w-4 text-purple-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ leaveTypes.filter(t => t.gender).length }}</div>
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
                                <Badge v-if="getGenderBadge(leaveType.gender)" variant="secondary">
                                    {{ getGenderBadge(leaveType.gender) }}
                                </Badge>
                                <Button
                                    v-if="isAdmin || isHR"
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
                            <span class="text-sm">{{ getPayDescription(leaveType.full_pay_days, leaveType.half_pay_days) }}</span>
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

                        <!-- Action Button -->
                        <Button class="w-full" variant="outline" as-child>
                            <a :href="route('leave-requests.create', { type: leaveType.id })">
                                Request {{ leaveType.name }}
                            </a>
                        </Button>
                    </CardContent>
                </Card>
            </div>

            <!-- Detailed Table View (Admin/HR only) -->
            <Card v-if="isAdmin || isHR">
                <CardHeader>
                    <CardTitle>Detailed Leave Types Management</CardTitle>
                    <CardDescription>Complete overview of all leave types with management options</CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Days Allowed</TableHead>
                                <TableHead>Duration Range</TableHead>
                                <TableHead>Gender</TableHead>
                                <TableHead>Approval</TableHead>
                                <TableHead>Documentation</TableHead>
                                <TableHead>Pay Structure</TableHead>
                                <TableHead>Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="leaveType in leaveTypes" :key="leaveType.id">
                                <TableCell class="font-medium">
                                    <div>
                                        <div class="font-medium">{{ leaveType.name }}</div>
                                        <div class="text-sm text-muted-foreground">{{ leaveType.description }}</div>
                                    </div>
                                </TableCell>
                                <TableCell>{{ leaveType.days_allowed }}</TableCell>
                                <TableCell>{{ leaveType.min_duration }}-{{ leaveType.max_duration }} days</TableCell>
                                <TableCell>
                                    <Badge v-if="leaveType.gender" variant="secondary">
                                        {{ leaveType.gender }}
                                    </Badge>
                                    <span v-else class="text-muted-foreground">All</span>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="leaveType.requires_approval ? 'default' : 'secondary'">
                                        {{ leaveType.requires_approval ? 'Required' : 'Not Required' }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="leaveType.requires_documentation ? 'default' : 'secondary'">
                                        {{ leaveType.requires_documentation ? 'Required' : 'Not Required' }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-sm">
                                    {{ getPayDescription(leaveType.full_pay_days, leaveType.half_pay_days) }}
                                </TableCell>
                                <TableCell>
                                    <Button variant="outline" size="sm" as-child>
                                        <a :href="route('admin.leave-types.edit', leaveType.id)">
                                            Edit
                                        </a>
                                    </Button>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
