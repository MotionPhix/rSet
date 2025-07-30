<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, usePage, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { UsersIcon, PlusIcon, SearchIcon, MailIcon, TrashIcon, UserPlusIcon, SendIcon } from 'lucide-vue-next';

const page = usePage<SharedData>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin Dashboard',
        href: route('admin.dashboard'),
    },
    {
        title: 'Company Profile',
        href: route('admin.company.profile'),
    },
    {
        title: 'Employees',
        href: route('admin.company.employees'),
    },
];

// Get company info
const company = computed(() => page.props.auth.company);

// Form for adding new employee
const addEmployeeForm = useForm({
    name: '',
    email: '',
    role: 'employee',
    team_id: '',
    default_password: 'Welcome123!',
});

// Mock employees data - this would come from the backend
const employees = computed(() => [
    {
        id: 1,
        name: 'John Doe',
        email: 'john@company.com',
        roles: [{ name: 'employee' }],
        team: { id: 1, name: 'Engineering' },
        status: 'active',
        last_login: '2024-07-29T10:30:00Z',
        leave_balance: 18,
        created_at: '2024-01-15T09:00:00Z',
    },
    {
        id: 2,
        name: 'Jane Smith',
        email: 'jane@company.com',
        roles: [{ name: 'manager' }],
        team: { id: 2, name: 'Marketing' },
        status: 'active',
        last_login: '2024-07-29T14:20:00Z',
        leave_balance: 21,
        created_at: '2024-02-01T10:30:00Z',
    },
    {
        id: 3,
        name: 'Mike Johnson',
        email: 'mike@company.com',
        roles: [{ name: 'hr' }],
        team: { id: 3, name: 'HR' },
        status: 'active',
        last_login: '2024-07-28T16:45:00Z',
        leave_balance: 15,
        created_at: '2024-01-20T11:15:00Z',
    },
    {
        id: 4,
        name: 'Sarah Wilson',
        email: 'sarah@company.com',
        roles: [{ name: 'employee' }],
        team: { id: 4, name: 'Design' },
        status: 'inactive',
        last_login: '2024-07-25T09:15:00Z',
        leave_balance: 12,
        created_at: '2024-03-10T14:20:00Z',
    },
]);

// Mock teams data
const teams = computed(() => [
    { id: 1, name: 'Engineering' },
    { id: 2, name: 'Marketing' },
    { id: 3, name: 'HR' },
    { id: 4, name: 'Design' },
    { id: 5, name: 'Sales' },
]);

const roles = [
    { value: 'employee', label: 'Employee' },
    { value: 'manager', label: 'Manager' },
    { value: 'hr', label: 'HR' },
];

const getRoleBadgeColor = (role: string) => {
    switch (role) {
        case 'admin': return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
        case 'hr': return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
        case 'manager': return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300';
        case 'employee': return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        default: return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
    }
};

const getStatusBadgeColor = (status: string) => {
    switch (status) {
        case 'active': return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        case 'inactive': return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
        case 'pending': return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
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

const formatDateTime = (dateString: string) => {
    return new Date(dateString).toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const submitAddEmployee = () => {
    addEmployeeForm.post(route('admin.users.store'), {
        onSuccess: () => {
            addEmployeeForm.reset();
        }
    });
};

// Stats
const stats = computed(() => ({
    totalEmployees: employees.value.length,
    activeEmployees: employees.value.filter(e => e.status === 'active').length,
    averageLeaveBalance: Math.round(employees.value.reduce((sum, emp) => sum + emp.leave_balance, 0) / employees.value.length),
    employeesWithoutTeam: employees.value.filter(e => !e.team).length,
}));
</script>

<template>
    <Head title="Company Employees" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
            <!-- Header Section -->
            <div class="flex items-center justify-between">
                <div class="space-y-2">
                    <h1 class="text-3xl font-bold tracking-tight">
                        Company Employees
                    </h1>
                    <p class="text-muted-foreground">
                        Manage employees for {{ company?.name }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <Dialog>
                        <DialogTrigger as-child>
                            <Button>
                                <UserPlusIcon class="h-4 w-4 mr-2" />
                                Add Employee
                            </Button>
                        </DialogTrigger>
                        <DialogContent class="sm:max-w-md">
                            <DialogHeader>
                                <DialogTitle>Add New Employee</DialogTitle>
                                <DialogDescription>
                                    Add a new employee to {{ company?.name }}. They will receive login credentials via email.
                                </DialogDescription>
                            </DialogHeader>
                            <form @submit.prevent="submitAddEmployee" class="space-y-4">
                                <div class="space-y-2">
                                    <Label for="name">Full Name *</Label>
                                    <Input
                                        id="name"
                                        v-model="addEmployeeForm.name"
                                        placeholder="Employee full name"
                                        required
                                    />
                                    <p v-if="addEmployeeForm.errors.name" class="text-sm text-destructive">
                                        {{ addEmployeeForm.errors.name }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="email">Email Address *</Label>
                                    <Input
                                        id="email"
                                        v-model="addEmployeeForm.email"
                                        type="email"
                                        placeholder="employee@company.com"
                                        required
                                    />
                                    <p v-if="addEmployeeForm.errors.email" class="text-sm text-destructive">
                                        {{ addEmployeeForm.errors.email }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="role">Role</Label>
                                    <Select v-model="addEmployeeForm.role">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select role" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem
                                                v-for="role in roles"
                                                :key="role.value"
                                                :value="role.value"
                                            >
                                                {{ role.label }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p v-if="addEmployeeForm.errors.role" class="text-sm text-destructive">
                                        {{ addEmployeeForm.errors.role }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="team">Team (Optional)</Label>
                                    <Select v-model="addEmployeeForm.team_id">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select team" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="">No Team</SelectItem>
                                            <SelectItem
                                                v-for="team in teams"
                                                :key="team.id"
                                                :value="team.id.toString()"
                                            >
                                                {{ team.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p v-if="addEmployeeForm.errors.team_id" class="text-sm text-destructive">
                                        {{ addEmployeeForm.errors.team_id }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="default_password">Default Password</Label>
                                    <Input
                                        id="default_password"
                                        v-model="addEmployeeForm.default_password"
                                        placeholder="Default password for new user"
                                    />
                                    <p class="text-xs text-muted-foreground">
                                        Employee can change this after first login
                                    </p>
                                    <p v-if="addEmployeeForm.errors.default_password" class="text-sm text-destructive">
                                        {{ addEmployeeForm.errors.default_password }}
                                    </p>
                                </div>

                                <div class="flex justify-end gap-2">
                                    <DialogTrigger as-child>
                                        <Button variant="outline" type="button">
                                            Cancel
                                        </Button>
                                    </DialogTrigger>
                                    <Button type="submit" :disabled="addEmployeeForm.processing">
                                        <SendIcon class="h-4 w-4 mr-2" />
                                        {{ addEmployeeForm.processing ? 'Adding...' : 'Add Employee' }}
                                    </Button>
                                </div>
                            </form>
                        </DialogContent>
                    </Dialog>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Employees</CardTitle>
                        <UsersIcon class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.totalEmployees }}</div>
                        <p class="text-xs text-muted-foreground">
                            of {{ company?.max_employees }} max
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Active Employees</CardTitle>
                        <UsersIcon class="h-4 w-4 text-green-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.activeEmployees }}</div>
                        <p class="text-xs text-muted-foreground">
                            Currently active
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Avg Leave Balance</CardTitle>
                        <UsersIcon class="h-4 w-4 text-blue-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.averageLeaveBalance }}</div>
                        <p class="text-xs text-muted-foreground">
                            Days remaining
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Without Team</CardTitle>
                        <UsersIcon class="h-4 w-4 text-orange-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.employeesWithoutTeam }}</div>
                        <p class="text-xs text-muted-foreground">
                            Need assignment
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Search -->
            <Card>
                <CardHeader>
                    <CardTitle>Search Employees</CardTitle>
                    <CardDescription>Find employees by name, email, or team</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="flex gap-4">
                        <div class="relative flex-1">
                            <SearchIcon class="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                            <Input
                                placeholder="Search employees..."
                                class="pl-10"
                            />
                        </div>
                        <Button variant="outline">
                            Search
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Employees Table -->
            <Card>
                <CardHeader>
                    <CardTitle>All Employees</CardTitle>
                    <CardDescription>
                        Complete list of employees in {{ company?.name }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Employee</TableHead>
                                <TableHead>Role</TableHead>
                                <TableHead>Team</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Leave Balance</TableHead>
                                <TableHead>Last Login</TableHead>
                                <TableHead>Joined</TableHead>
                                <TableHead>Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="employee in employees" :key="employee.id">
                                <TableCell class="font-medium">
                                    <div>
                                        <div class="font-medium">{{ employee.name }}</div>
                                        <div class="text-sm text-muted-foreground flex items-center gap-1">
                                            <MailIcon class="h-3 w-3" />
                                            {{ employee.email }}
                                        </div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div class="flex flex-wrap gap-1">
                                        <Badge
                                            v-for="role in employee.roles"
                                            :key="role.name"
                                            :class="getRoleBadgeColor(role.name)"
                                            variant="secondary"
                                        >
                                            {{ role.name.charAt(0).toUpperCase() + role.name.slice(1) }}
                                        </Badge>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <span v-if="employee.team">{{ employee.team.name }}</span>
                                    <Badge v-else variant="outline" class="text-xs">
                                        No Team
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <Badge :class="getStatusBadgeColor(employee.status)" variant="secondary">
                                        {{ employee.status.charAt(0).toUpperCase() + employee.status.slice(1) }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <div class="text-center">
                                        <div class="font-semibold">{{ employee.leave_balance }}</div>
                                        <div class="text-xs text-muted-foreground">days</div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <span class="text-sm">{{ formatDateTime(employee.last_login) }}</span>
                                </TableCell>
                                <TableCell>
                                    <span class="text-sm">{{ formatDate(employee.created_at) }}</span>
                                </TableCell>
                                <TableCell>
                                    <div class="flex gap-2">
                                        <Button variant="outline" size="sm" as-child>
                                            <a :href="route('admin.users.edit', employee.id)">
                                                Edit
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

            <!-- Quick Actions -->
            <Card>
                <CardHeader>
                    <CardTitle>Quick Actions</CardTitle>
                    <CardDescription>Common employee management tasks</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-3 md:grid-cols-3">
                        <Button variant="outline" class="justify-start">
                            <UserPlusIcon class="h-4 w-4 mr-2" />
                            Bulk Add Employees
                        </Button>

                        <Button variant="outline" class="justify-start">
                            <MailIcon class="h-4 w-4 mr-2" />
                            Send Welcome Emails
                        </Button>

                        <Button variant="outline" class="justify-start">
                            <UsersIcon class="h-4 w-4 mr-2" />
                            Export Employee List
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
