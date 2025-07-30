<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Input } from '@/components/ui/input';
import { UsersIcon, PlusIcon, SearchIcon, FilterIcon, DownloadIcon, EditIcon, TrashIcon } from 'lucide-vue-next';

const page = usePage<SharedData>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin Dashboard',
        href: route('admin.dashboard'),
    },
    {
        title: 'User Management',
        href: route('admin.users.index'),
    },
];

// Get company info
const company = computed(() => page.props.auth.company);

// Mock users data - this would come from the backend
const users = computed(() => [
    {
        id: 1,
        name: 'John Doe',
        email: 'john@company.com',
        roles: [{ name: 'employee' }],
        team: { name: 'Engineering' },
        status: 'active',
        last_login: '2024-07-29T10:30:00Z',
        created_at: '2024-01-15T09:00:00Z',
    },
    {
        id: 2,
        name: 'Jane Smith',
        email: 'jane@company.com',
        roles: [{ name: 'manager' }],
        team: { name: 'Marketing' },
        status: 'active',
        last_login: '2024-07-29T14:20:00Z',
        created_at: '2024-02-01T10:30:00Z',
    },
    {
        id: 3,
        name: 'Mike Johnson',
        email: 'mike@company.com',
        roles: [{ name: 'hr' }],
        team: { name: 'HR' },
        status: 'active',
        last_login: '2024-07-28T16:45:00Z',
        created_at: '2024-01-20T11:15:00Z',
    },
    {
        id: 4,
        name: 'Sarah Wilson',
        email: 'sarah@company.com',
        roles: [{ name: 'employee' }],
        team: { name: 'Design' },
        status: 'inactive',
        last_login: '2024-07-25T09:15:00Z',
        created_at: '2024-03-10T14:20:00Z',
    },
]);

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

// Stats
const stats = computed(() => ({
    totalUsers: users.value.length,
    activeUsers: users.value.filter(u => u.status === 'active').length,
    admins: users.value.filter(u => u.roles.some(r => r.name === 'admin')).length,
    employees: users.value.filter(u => u.roles.some(r => r.name === 'employee')).length,
}));
</script>

<template>
    <Head title="User Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
            <!-- Header Section -->
            <div class="flex items-center justify-between">
                <div class="space-y-2">
                    <h1 class="text-3xl font-bold tracking-tight">
                        User Management
                    </h1>
                    <p class="text-muted-foreground">
                        Manage users and their roles within {{ company?.name }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline">
                        <FilterIcon class="h-4 w-4 mr-2" />
                        Filter
                    </Button>
                    <Button variant="outline">
                        <DownloadIcon class="h-4 w-4 mr-2" />
                        Export
                    </Button>
                    <Button as-child>
                        <a :href="route('admin.users.create')">
                            <PlusIcon class="h-4 w-4 mr-2" />
                            Add User
                        </a>
                    </Button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Users</CardTitle>
                        <UsersIcon class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.totalUsers }}</div>
                        <p class="text-xs text-muted-foreground">
                            of {{ company?.max_employees }} max
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Active Users</CardTitle>
                        <UsersIcon class="h-4 w-4 text-green-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.activeUsers }}</div>
                        <p class="text-xs text-muted-foreground">
                            Currently active
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Admins</CardTitle>
                        <UsersIcon class="h-4 w-4 text-red-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.admins }}</div>
                        <p class="text-xs text-muted-foreground">
                            Admin users
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Employees</CardTitle>
                        <UsersIcon class="h-4 w-4 text-blue-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.employees }}</div>
                        <p class="text-xs text-muted-foreground">
                            Regular employees
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Search and Filters -->
            <Card>
                <CardHeader>
                    <CardTitle>Search Users</CardTitle>
                    <CardDescription>Find users by name, email, or role</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="flex gap-4">
                        <div class="relative flex-1">
                            <SearchIcon class="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                            <Input
                                placeholder="Search users..."
                                class="pl-10"
                            />
                        </div>
                        <Button variant="outline">
                            Search
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Users Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Company Users</CardTitle>
                    <CardDescription>
                        All users registered under {{ company?.name }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>User</TableHead>
                                <TableHead>Role</TableHead>
                                <TableHead>Team</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Last Login</TableHead>
                                <TableHead>Joined</TableHead>
                                <TableHead>Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="user in users" :key="user.id">
                                <TableCell class="font-medium">
                                    <div>
                                        <div class="font-medium">{{ user.name }}</div>
                                        <div class="text-sm text-muted-foreground">{{ user.email }}</div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div class="flex flex-wrap gap-1">
                                        <Badge
                                            v-for="role in user.roles"
                                            :key="role.name"
                                            :class="getRoleBadgeColor(role.name)"
                                            variant="secondary"
                                        >
                                            {{ role.name.charAt(0).toUpperCase() + role.name.slice(1) }}
                                        </Badge>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <span v-if="user.team">{{ user.team.name }}</span>
                                    <span v-else class="text-muted-foreground">No team</span>
                                </TableCell>
                                <TableCell>
                                    <Badge :class="getStatusBadgeColor(user.status)" variant="secondary">
                                        {{ user.status.charAt(0).toUpperCase() + user.status.slice(1) }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <span class="text-sm">{{ formatDateTime(user.last_login) }}</span>
                                </TableCell>
                                <TableCell>
                                    <span class="text-sm">{{ formatDate(user.created_at) }}</span>
                                </TableCell>
                                <TableCell>
                                    <div class="flex gap-2">
                                        <Button variant="outline" size="sm" as-child>
                                            <a :href="route('admin.users.edit', user.id)">
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

            <!-- Quick Actions -->
            <div class="grid gap-6 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Quick Actions</CardTitle>
                        <CardDescription>Common user management tasks</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-3">
                            <Button variant="outline" class="justify-start" as-child>
                                <a :href="route('admin.users.create')">
                                    <PlusIcon class="h-4 w-4 mr-2" />
                                    Add New User
                                </a>
                            </Button>

                            <Button variant="outline" class="justify-start">
                                <DownloadIcon class="h-4 w-4 mr-2" />
                                Export User List
                            </Button>

                            <Button variant="outline" class="justify-start">
                                <UsersIcon class="h-4 w-4 mr-2" />
                                Bulk Role Assignment
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>User Statistics</CardTitle>
                        <CardDescription>User activity and engagement metrics</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium">Login Rate (30 days)</span>
                                <span class="text-sm font-bold">85%</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium">Average Session Time</span>
                                <span class="text-sm font-bold">2h 15m</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium">New Users (This Month)</span>
                                <span class="text-sm font-bold">3</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium">Password Resets</span>
                                <span class="text-sm font-bold">2</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
