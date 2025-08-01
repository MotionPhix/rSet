<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { 
  UsersIcon,
  PlusIcon,
  EditIcon,
  TrashIcon,
  XIcon
} from 'lucide-vue-next';

interface Team {
  id: number;
  name: string;
  description: string;
  manager: {
    id: number;
    name: string;
    email: string;
  };
  members_count: number;
  created_at: string;
}

interface User {
  id: number;
  name: string;
  email: string;
}

interface Props {
  teams: Team[];
  users: User[];
}

const props = defineProps<Props>();

const breadcrumbs = computed((): BreadcrumbItem[] => [
  {
    title: 'Admin',
    href: route('admin.dashboard')
  },
  {
    title: 'Settings',
    href: route('admin.settings.users')
  },
  {
    title: 'Teams',
    href: route('admin.settings.teams')
  }
]);

// Form state
const showCreateForm = ref(false);
const showEditForm = ref(false);
const editingTeam = ref<Team | null>(null);

const createForm = useForm({
  name: '',
  description: '',
  manager_id: ''
});

const editForm = useForm({
  name: '',
  description: '',
  manager_id: ''
});

// Create team
const createTeam = () => {
  createForm.post(route('admin.settings.teams.store'), {
    onSuccess: () => {
      showCreateForm.value = false;
      createForm.reset();
    }
  });
};

// Edit team
const editTeam = (team: Team) => {
  editingTeam.value = team;
  editForm.name = team.name;
  editForm.description = team.description;
  editForm.manager_id = team.manager.id.toString();
  showEditForm.value = true;
};

const updateTeam = () => {
  if (!editingTeam.value) return;
  
  editForm.patch(route('admin.settings.teams.update', editingTeam.value.id), {
    onSuccess: () => {
      showEditForm.value = false;
      editingTeam.value = null;
      editForm.reset();
    }
  });
};

// Delete team
const deleteTeam = (team: Team) => {
  if (confirm(`Are you sure you want to delete the team "${team.name}"? This action cannot be undone.`)) {
    router.delete(route('admin.settings.teams.destroy', team.id));
  }
};

// Cancel forms
const cancelCreate = () => {
  showCreateForm.value = false;
  createForm.reset();
};

const cancelEdit = () => {
  showEditForm.value = false;
  editingTeam.value = null;
  editForm.reset();
};

// Format date
const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString();
};
</script>

<template>
  <Head title="Team Management" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div class="space-y-1">
          <h1 class="text-3xl font-bold tracking-tight">Team Management</h1>
          <p class="text-muted-foreground">
            Organize your employees into teams and assign managers
          </p>
        </div>
        <Button @click="showCreateForm = true">
          <PlusIcon class="h-4 w-4 mr-2" />
          Add Team
        </Button>
      </div>

      <!-- Teams Overview -->
      <div class="grid gap-4 md:grid-cols-3">
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Teams</CardTitle>
            <UsersIcon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ props.teams?.length || 0 }}</div>
            <p class="text-xs text-muted-foreground">
              Active teams
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Members</CardTitle>
            <UsersIcon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">
              {{ props.teams?.reduce((sum, team) => sum + team.members_count, 0) || 0 }}
            </div>
            <p class="text-xs text-muted-foreground">
              Across all teams
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Average Team Size</CardTitle>
            <UsersIcon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">
              {{ props.teams?.length ? Math.round(props.teams.reduce((sum, team) => sum + team.members_count, 0) / props.teams.length) : 0 }}
            </div>
            <p class="text-xs text-muted-foreground">
              Members per team
            </p>
          </CardContent>
        </Card>
      </div>

      <!-- Teams Table -->
      <Card>
        <CardHeader>
          <CardTitle>Teams</CardTitle>
          <CardDescription>
            Manage your company teams and their managers
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="rounded-md border">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Team Name</TableHead>
                  <TableHead>Description</TableHead>
                  <TableHead>Manager</TableHead>
                  <TableHead>Members</TableHead>
                  <TableHead>Created</TableHead>
                  <TableHead class="text-right">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-if="!props.teams?.length">
                  <TableCell colspan="6" class="text-center text-muted-foreground py-8">
                    No teams found. Create your first team to get started.
                  </TableCell>
                </TableRow>
                <TableRow 
                  v-for="team in props.teams" 
                  :key="team.id"
                  class="hover:bg-muted/50"
                >
                  <TableCell>
                    <div>
                      <p class="font-medium">{{ team.name }}</p>
                    </div>
                  </TableCell>
                  <TableCell>
                    <p class="text-sm text-muted-foreground max-w-xs truncate">
                      {{ team.description || 'No description' }}
                    </p>
                  </TableCell>
                  <TableCell>
                    <div>
                      <p class="font-medium">{{ team.manager.name }}</p>
                      <p class="text-sm text-muted-foreground">{{ team.manager.email }}</p>
                    </div>
                  </TableCell>
                  <TableCell>
                    <Badge variant="outline">
                      {{ team.members_count }} {{ team.members_count === 1 ? 'member' : 'members' }}
                    </Badge>
                  </TableCell>
                  <TableCell class="text-sm text-muted-foreground">
                    {{ formatDate(team.created_at) }}
                  </TableCell>
                  <TableCell class="text-right">
                    <div class="flex items-center justify-end space-x-2">
                      <Button variant="ghost" size="sm" @click="editTeam(team)">
                        <EditIcon class="h-4 w-4" />
                      </Button>
                      <Button 
                        variant="ghost" 
                        size="sm" 
                        @click="deleteTeam(team)"
                        class="text-red-600 hover:text-red-700"
                      >
                        <TrashIcon class="h-4 w-4" />
                      </Button>
                    </div>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
        </CardContent>
      </Card>

      <!-- Create Team Dialog -->
      <div v-if="showCreateForm" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <Card class="w-full max-w-md mx-4">
          <CardHeader>
            <div class="flex items-center justify-between">
              <CardTitle>Create New Team</CardTitle>
              <Button variant="ghost" size="sm" @click="cancelCreate">
                <XIcon class="h-4 w-4" />
              </Button>
            </div>
            <CardDescription>
              Add a new team to your organization
            </CardDescription>
          </CardHeader>
          <CardContent>
            <form @submit.prevent="createTeam" class="space-y-4">
              <div class="space-y-2">
                <Label for="create_name">Team Name *</Label>
                <Input 
                  id="create_name"
                  v-model="createForm.name" 
                  placeholder="e.g., Engineering, Marketing"
                  required
                  :class="{ 'border-red-500': createForm.errors.name }"
                />
                <p v-if="createForm.errors.name" class="text-sm text-red-500">{{ createForm.errors.name }}</p>
              </div>

              <div class="space-y-2">
                <Label for="create_description">Description</Label>
                <Textarea 
                  id="create_description"
                  v-model="createForm.description" 
                  placeholder="Brief description of the team's responsibilities..."
                  rows="3"
                  :class="{ 'border-red-500': createForm.errors.description }"
                />
                <p v-if="createForm.errors.description" class="text-sm text-red-500">{{ createForm.errors.description }}</p>
              </div>

              <div class="space-y-2">
                <Label for="create_manager">Team Manager *</Label>
                <select 
                  id="create_manager"
                  v-model="createForm.manager_id" 
                  required
                  class="w-full p-2 border border-input rounded-md"
                  :class="{ 'border-red-500': createForm.errors.manager_id }"
                >
                  <option value="">Select a manager</option>
                  <option 
                    v-for="user in props.users" 
                    :key="user.id" 
                    :value="user.id"
                  >
                    {{ user.name }} ({{ user.email }})
                  </option>
                </select>
                <p v-if="createForm.errors.manager_id" class="text-sm text-red-500">{{ createForm.errors.manager_id }}</p>
              </div>

              <div class="flex justify-end space-x-2">
                <Button variant="outline" @click="cancelCreate">
                  Cancel
                </Button>
                <Button type="submit" :disabled="createForm.processing">
                  {{ createForm.processing ? 'Creating...' : 'Create Team' }}
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>

      <!-- Edit Team Dialog -->
      <div v-if="showEditForm && editingTeam" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <Card class="w-full max-w-md mx-4">
          <CardHeader>
            <div class="flex items-center justify-between">
              <CardTitle>Edit Team</CardTitle>
              <Button variant="ghost" size="sm" @click="cancelEdit">
                <XIcon class="h-4 w-4" />
              </Button>
            </div>
            <CardDescription>
              Update team information
            </CardDescription>
          </CardHeader>
          <CardContent>
            <form @submit.prevent="updateTeam" class="space-y-4">
              <div class="space-y-2">
                <Label for="edit_name">Team Name *</Label>
                <Input 
                  id="edit_name"
                  v-model="editForm.name" 
                  placeholder="e.g., Engineering, Marketing"
                  required
                  :class="{ 'border-red-500': editForm.errors.name }"
                />
                <p v-if="editForm.errors.name" class="text-sm text-red-500">{{ editForm.errors.name }}</p>
              </div>

              <div class="space-y-2">
                <Label for="edit_description">Description</Label>
                <Textarea 
                  id="edit_description"
                  v-model="editForm.description" 
                  placeholder="Brief description of the team's responsibilities..."
                  rows="3"
                  :class="{ 'border-red-500': editForm.errors.description }"
                />
                <p v-if="editForm.errors.description" class="text-sm text-red-500">{{ editForm.errors.description }}</p>
              </div>

              <div class="space-y-2">
                <Label for="edit_manager">Team Manager *</Label>
                <select 
                  id="edit_manager"
                  v-model="editForm.manager_id" 
                  required
                  class="w-full p-2 border border-input rounded-md"
                  :class="{ 'border-red-500': editForm.errors.manager_id }"
                >
                  <option value="">Select a manager</option>
                  <option 
                    v-for="user in props.users" 
                    :key="user.id" 
                    :value="user.id"
                  >
                    {{ user.name }} ({{ user.email }})
                  </option>
                </select>
                <p v-if="editForm.errors.manager_id" class="text-sm text-red-500">{{ editForm.errors.manager_id }}</p>
              </div>

              <div class="flex justify-end space-x-2">
                <Button variant="outline" @click="cancelEdit">
                  Cancel
                </Button>
                <Button type="submit" :disabled="editForm.processing">
                  {{ editForm.processing ? 'Updating...' : 'Update Team' }}
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
