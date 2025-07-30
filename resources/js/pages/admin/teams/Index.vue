<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { UsersIcon, PlusIcon, EditIcon, TrashIcon, Building2Icon, UserCheckIcon } from 'lucide-vue-next';

const page = usePage<SharedData>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin Dashboard',
        href: route('admin.dashboard'),
    },
    {
        title: 'Team Management',
        href: route('admin.teams.index'),
    },
];

// Get company info
const company = computed(() => page.props.auth.company);

// Mock teams data - this would come from the backend
const teams = computed(() => [
    {
        id: 1,
        name: 'Engineering',
        description: 'Software development and technical operations',
        manager: { name: 'John Smith', email: 'john.smith@company.com' },
        members_count: 12,
        active_leave_requests: 3,
        created_at: '2024-01-15T09:00:00Z',
    },
    {
        id: 2,
        name: 'Marketing',
        description: 'Brand management and customer acquisition',
        manager: { name: 'Sarah Johnson', email: 'sarah.johnson@company.com' },
        members_count: 8,
        active_leave_requests: 1,
        created_at: '2024-01-20T10:30:00Z',
    },
    {
        id: 3,
        name: 'Human Resources',
        description: 'Employee relations and organizational development',
        manager: { name: 'Mike Wilson', email: 'mike.wilson@company.com' },
        members_count: 4,
        active_leave_requests: 0,
        created_at: '2024-01-25T11:15:00Z',
    },
    {
        id: 4,
        name: 'Design',
        description: 'User experience and visual design',
        manager: null,
        members_count: 6,
        active_leave_requests: 2,
        created_at: '2024-02-01T14:20:00Z',
    },
    {
        id: 5,
        name: 'Sales',
        description: 'Revenue generation and client relationships',
        manager: { name: 'Lisa Brown', email: 'lisa.brown@company.com' },
        members_count: 10,
        active_leave_requests: 4,
        created_at: '2024-02-10T16:45:00Z',
    },
]);

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

// Stats
const stats = computed(() => ({
    totalTeams: teams.value.length,
    totalMembers: teams.value.reduce((sum, team) => sum + team.members_count, 0),
    teamsWithManagers: teams.value.filter(t => t.manager).length,
    activeLeaveRequests: teams.value.reduce((sum, team) => sum + team.active_leave_requests, 0),
}));
</script>

<template>
    <Head title="Team Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
            <!-- Header Section -->
            <div class="flex items-center justify-between">
                <div class="space-y-2">
                    <h1 class="text-3xl font-bold tracking-tight">
                        Team Management
                    </h1>
                    <p class="text-muted-foreground">
                        Organize and manage teams within {{ company?.name }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <Button as-child>
                        <a :href="route('admin.teams.create')">
                            <PlusIcon class="h-4 w-4 mr-2" />
                            Create Team
                        </a>
                    </Button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Teams</CardTitle>
                        <Building2Icon class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.totalTeams }}</div>
                        <p class="text-xs text-muted-foreground">
                            Active teams
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Members</CardTitle>
                        <UsersIcon class="h-4 w-4 text-blue-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.totalMembers }}</div>
                        <p class="text-xs text-muted-foreground">
                            Across all teams
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Teams with Managers</CardTitle>
                        <UserCheckIcon class="h-4 w-4 text-green-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.teamsWithManagers }}</div>
                        <p class="text-xs text-muted-foreground">
                            of {{ stats.totalTeams }} teams
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Active Leave Requests</CardTitle>
                        <UsersIcon class="h-4 w-4 text-orange-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.activeLeaveRequests }}</div>
                        <p class="text-xs text-muted-foreground">
                            Pending approval
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Teams Grid -->
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <Card v-for="team in teams" :key="team.id" class="relative">
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <CardTitle class="text-lg">{{ team.name }}</CardTitle>
                            <div class="flex gap-1">
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    as-child
                                >
                                    <a :href="route('admin.teams.edit', team.id)">
                                        <EditIcon class="h-4 w-4" />
                                    </a>
                                </Button>
                            </div>
                        </div>
                        <CardDescription>{{ team.description }}</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <!-- Team Manager -->
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium">Manager</span>
                            <div class="text-right">
                                <div v-if="team.manager" class="text-sm">
                                    <div class="font-medium">{{ team.manager.name }}</div>
                                    <div class="text-xs text-muted-foreground">{{ team.manager.email }}</div>
                                </div>
                                <Badge v-else variant="outline" class="text-xs">
                                    No Manager
                                </Badge>
                            </div>
                        </div>

                        <!-- Team Size -->
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium">Team Size</span>
                            <div class="flex items-center gap-1">
                                <UsersIcon class="h-4 w-4 text-muted-foreground" />
                                <span class="font-semibold">{{ team.members_count }}</span>
                            </div>
                        </div>

                        <!-- Active Leave Requests -->
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium">Active Requests</span>
                            <Badge
                                :variant="team.active_leave_requests > 0 ? 'default' : 'secondary'"
                                class="text-xs"
                            >
                                {{ team.active_leave_requests }}
                            </Badge>
                        </div>

                        <!-- Created Date -->
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium">Created</span>
                            <span class="text-sm">{{ formatDate(team.created_at) }}</span>
                        </div>

                        <!-- Action Buttons -->
                        <div class="grid gap-2 pt-2">
                            <Button class="w-full" variant="outline" as-child>
                                <a :href="route('admin.teams.edit', team.id)">
                                    Manage Team
                                </a>
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Teams Table -->
            <Card>
                <CardHeader>
                    <CardTitle>All Teams</CardTitle>
                    <CardDescription>
                        Complete overview of all teams in {{ company?.name }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Team Name</TableHead>
                                <TableHead>Manager</TableHead>
                                <TableHead>Members</TableHead>
                                <TableHead>Active Requests</TableHead>
                                <TableHead>Created</TableHead>
                                <TableHead>Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="team in teams" :key="team.id">
                                <TableCell class="font-medium">
                                    <div>
                                        <div class="font-medium">{{ team.name }}</div>
                                        <div class="text-sm text-muted-foreground">{{ team.description }}</div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div v-if="team.manager">
                                        <div class="font-medium">{{ team.manager.name }}</div>
                                        <div class="text-sm text-muted-foreground">{{ team.manager.email }}</div>
                                    </div>
                                    <Badge v-else variant="outline">
                                        No Manager
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center gap-1">
                                        <UsersIcon class="h-4 w-4 text-muted-foreground" />
                                        <span class="font-semibold">{{ team.members_count }}</span>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge
                                        :variant="team.active_leave_requests > 0 ? 'default' : 'secondary'"
                                    >
                                        {{ team.active_leave_requests }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <span class="text-sm">{{ formatDate(team.created_at) }}</span>
                                </TableCell>
                                <TableCell>
                                    <div class="flex gap-2">
                                        <Button variant="outline" size="sm" as-child>
                                            <a :href="route('admin.teams.edit', team.id)">
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
                        <CardDescription>Common team management tasks</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-3">
                            <Button variant="outline" class="justify-start" as-child>
                                <a :href="route('admin.teams.create')">
                                    <PlusIcon class="h-4 w-4 mr-2" />
                                    Create New Team
                                </a>
                            </Button>

                            <Button variant="outline" class="justify-start">
                                <UsersIcon class="h-4 w-4 mr-2" />
                                Assign Team Managers
                            </Button>

                            <Button variant="outline" class="justify-start">
                                <Building2Icon class="h-4 w-4 mr-2" />
                                Reorganize Teams
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Team Insights</CardTitle>
                        <CardDescription>Team performance and activity metrics</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium">Average Team Size</span>
                                <span class="text-sm font-bold">{{ Math.round(stats.totalMembers / stats.totalTeams) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium">Teams with Managers</span>
                                <span class="text-sm font-bold">{{ Math.round((stats.teamsWithManagers / stats.totalTeams) * 100) }}%</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium">Most Active Team</span>
                                <span class="text-sm font-bold">Engineering</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium">Largest Team</span>
                                <span class="text-sm font-bold">Engineering (12)</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
