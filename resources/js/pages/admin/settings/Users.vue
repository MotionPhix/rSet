<script setup lang="ts">
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { toast } from 'vue-sonner';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
  UserPlus,
  Users,
  Search,
  MoreHorizontal,
  Mail,
  Shield,
  Calendar,
  Edit,
  Trash2,
  UserCheck,
  UserX
} from 'lucide-vue-next';
import { type SharedData } from '@/types';

interface Props {
  users: Array<{
    id: number;
    name: string;
    email: string;
    roles: Array<{ name: string; display_name: string }>;
    team: { id: number; name: string } | null;
    created_at: string;
    last_login: string;
    is_active: boolean;
    avatar: string | null;
  }>;
  teams: Array<{
    id: number;
    name: string;
  }>;
  roles: Array<{
    id: number;
    name: string;
    display_name: string;
  }>;
}

const props = defineProps<Props>();

const page = usePage<SharedData>();
const userAbilities = computed(() => page.props.auth.abilities || {});

// State
const searchQuery = ref('');
const showCreateDialog = ref(false);
const showEditDialog = ref(false);
const selectedUser = ref(null);

// Create user form
const createForm = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: 'employee',
  team_id: '',
});

// Edit user form
const editForm = useForm({
  name: '',
  email: '',
  role: '',
  team_id: '',
  is_active: true,
});

// Computed
const filteredUsers = computed(() => {
  if (!searchQuery.value) return props.users;

  return props.users.filter(user =>
    user.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    user.email.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    user.roles.some(role => role.toLowerCase().includes(searchQuery.value.toLowerCase()))
  );
});

const stats = computed(() => ({
  total: props.users.length,
  active: props.users.filter(u => u.is_active).length,
  admins: props.users.filter(u => u.roles.includes('admin') || u.roles.includes('hr')).length,
  employees: props.users.filter(u => u.roles.includes('employee')).length,
}));

// Methods
const createUser = () => {
  createForm.post(route('admin.settings.users.store'), {
    preserveScroll: true,
    onSuccess: () => {
      toast.success('User created successfully');
      showCreateDialog.value = false;
      createForm.reset();
    },
    onError: () => {
      toast.error('Failed to create user');
    },
  });
};

const openEditDialog = (user: any) => {
  selectedUser.value = user;
  editForm.name = user.name;
  editForm.email = user.email;
  editForm.role = user.roles[0];
  editForm.team_id = user.team?.id || '';
  editForm.is_active = user.is_active;
  showEditDialog.value = true;
};

const updateUser = () => {
  if (!selectedUser.value) return;

  editForm.patch(route('admin.settings.users.update', selectedUser.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      toast.success('User updated successfully');
      showEditDialog.value = false;
      editForm.reset();
    },
    onError: () => {
      toast.error('Failed to update user');
    },
  });
};

const deleteUser = (user: any) => {
  if (confirm(`Are you sure you want to delete ${user.name}?`)) {
    router.delete(route('admin.settings.users.destroy', user.id), {
      onSuccess: () => {
        toast.success('User deleted successfully');
      },
      onError: () => {
        toast.error('Failed to delete user');
      },
    });
  }
};

const toggleUserStatus = (user: any) => {
  // Toggle user active status
  router.patch(route('admin.settings.users.update', user.id), {
    is_active: !user.is_active
  }, {
    onSuccess: () => {
      toast.success(`User ${user.is_active ? 'deactivated' : 'activated'} successfully`);
    },
    onError: () => {
      toast.error('Failed to update user status');
    },
  });
};

const getRoleBadge = (roles: string[]) => {
  const role = roles[0];
  switch (role) {
    case 'admin': return { variant: 'default', label: 'Admin' };
    case 'hr': return { variant: 'secondary', label: 'HR' };
    case 'manager': return { variant: 'outline', label: 'Manager' };
    default: return { variant: 'outline', label: 'Employee' };
  }
};

const getInitials = (name: string) => {
  return name.split(' ').map(n => n[0]).join('').toUpperCase();
};
</script>

<template>
  <Head title="User Management" />

  <SettingsLayout>
    <div class="space-y-6 p-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <HeadingSmall
          title="User Management"
          description="Manage company users, roles, and permissions"
        />
        <Button
          v-if="userAbilities.createUsers"
          @click="showCreateDialog = true"
        >
          <UserPlus class="h-4 w-4 mr-2" />
          Add User
        </Button>
      </div>

      <!-- Stats Cards -->
      <div class="grid gap-4 md:grid-cols-4">
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Users</CardTitle>
            <Users class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ stats.total }}</div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Active Users</CardTitle>
            <UserCheck class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ stats.active }}</div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Administrators</CardTitle>
            <Shield class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ stats.admins }}</div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Employees</CardTitle>
            <Users class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ stats.employees }}</div>
          </CardContent>
        </Card>
      </div>

      <!-- Search and Filters -->
      <Card>
        <CardHeader>
          <CardTitle>Users</CardTitle>
          <CardDescription>
            Manage user accounts, roles, and team assignments
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="flex items-center space-x-2 mb-4">
            <div class="relative flex-1">
              <Search class="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
              <Input
                v-model="searchQuery"
                placeholder="Search users by name, email, or role..."
                class="pl-10"
              />
            </div>
          </div>

          <!-- Users Table -->
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>User</TableHead>
                <TableHead>Role</TableHead>
                <TableHead>Team</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Last Login</TableHead>
                <TableHead>Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="user in filteredUsers" :key="user.id">
                <TableCell>
                  <div class="flex items-center space-x-3">
                    <Avatar class="h-8 w-8">
                      <AvatarImage :src="user.avatar" />
                      <AvatarFallback>{{ getInitials(user.name) }}</AvatarFallback>
                    </Avatar>
                    <div>
                      <div class="font-medium">{{ user.name }}</div>
                      <div class="text-sm text-muted-foreground">{{ user.email }}</div>
                    </div>
                  </div>
                </TableCell>
                <TableCell>
                  <Badge :variant="getRoleBadge(user.roles).variant">
                    {{ getRoleBadge(user.roles).label }}
                  </Badge>
                </TableCell>
                <TableCell>
                  <span v-if="user.team">{{ user.team.name }}</span>
                  <span v-else class="text-muted-foreground">No team</span>
                </TableCell>
                <TableCell>
                  <Badge :variant="user.is_active ? 'default' : 'secondary'">
                    {{ user.is_active ? 'Active' : 'Inactive' }}
                  </Badge>
                </TableCell>
                <TableCell>
                  <div class="flex items-center space-x-1">
                    <Calendar class="h-3 w-3 text-muted-foreground" />
                    <span class="text-sm">
                      {{ new Date(user.last_login).toLocaleDateString() }}
                    </span>
                  </div>
                </TableCell>
                <TableCell>
                  <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                      <Button variant="ghost" size="sm">
                        <MoreHorizontal class="h-4 w-4" />
                      </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                      <DropdownMenuItem
                        v-if="userAbilities.editUsers"
                        @click="openEditDialog(user)"
                      >
                        <Edit class="h-4 w-4 mr-2" />
                        Edit User
                      </DropdownMenuItem>
                      <DropdownMenuItem
                        v-if="userAbilities.editUsers"
                        @click="toggleUserStatus(user)"
                      >
                        <component :is="user.is_active ? UserX : UserCheck" class="h-4 w-4 mr-2" />
                        {{ user.is_active ? 'Deactivate' : 'Activate' }}
                      </DropdownMenuItem>
                      <DropdownMenuSeparator />
                      <DropdownMenuItem
                        v-if="userAbilities.deleteUsers"
                        @click="deleteUser(user)"
                        class="text-destructive"
                      >
                        <Trash2 class="h-4 w-4 mr-2" />
                        Delete User
                      </DropdownMenuItem>
                    </DropdownMenuContent>
                  </DropdownMenu>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
      </Card>

      <!-- Create User Dialog -->
      <Dialog v-model:open="showCreateDialog">
        <DialogContent class="sm:max-w-[425px]">
          <DialogHeader>
            <DialogTitle>Add New User</DialogTitle>
            <DialogDescription>
              Create a new user account for your company
            </DialogDescription>
          </DialogHeader>
          <form @submit.prevent="createUser" class="space-y-4">
            <div class="space-y-2">
              <Label for="name">Full Name</Label>
              <Input
                id="name"
                v-model="createForm.name"
                placeholder="Enter full name"
                required
              />
              <div v-if="createForm.errors.name" class="text-sm text-destructive">
                {{ createForm.errors.name }}
              </div>
            </div>

            <div class="space-y-2">
              <Label for="email">Email Address</Label>
              <Input
                id="email"
                v-model="createForm.email"
                type="email"
                placeholder="user@company.com"
                required
              />
              <div v-if="createForm.errors.email" class="text-sm text-destructive">
                {{ createForm.errors.email }}
              </div>
            </div>

            <div class="space-y-2">
              <Label for="password">Password</Label>
              <Input
                id="password"
                v-model="createForm.password"
                type="password"
                placeholder="Enter password"
                required
              />
              <div v-if="createForm.errors.password" class="text-sm text-destructive">
                {{ createForm.errors.password }}
              </div>
            </div>

            <div class="space-y-2">
              <Label for="password_confirmation">Confirm Password</Label>
              <Input
                id="password_confirmation"
                v-model="createForm.password_confirmation"
                type="password"
                placeholder="Confirm password"
                required
              />
            </div>

            <div class="grid gap-4 md:grid-cols-2">
              <div class="space-y-2">
                <Label for="role">Role</Label>
                <Select v-model="createForm.role">
                  <SelectTrigger>
                    <SelectValue placeholder="Select role" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem
                      v-for="role in roles"
                      :key="role.name"
                      :value="role.name"
                    >
                      {{ role.display_name }}
                    </SelectItem>
                  </SelectContent>
                </Select>
                <div v-if="createForm.errors.role" class="text-sm text-destructive">
                  {{ createForm.errors.role }}
                </div>
              </div>

              <div class="space-y-2">
                <Label for="team">Team</Label>
                <Select v-model="createForm.team_id">
                  <SelectTrigger>
                    <SelectValue placeholder="Select team" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem
                      v-for="team in teams"
                      :key="team.id"
                      :value="team.id.toString()"
                    >
                      {{ team.name }}
                    </SelectItem>
                  </SelectContent>
                </Select>
                <div v-if="createForm.errors.team_id" class="text-sm text-destructive">
                  {{ createForm.errors.team_id }}
                </div>
              </div>
            </div>
          </form>
          <DialogFooter>
            <Button
              type="button"
              variant="outline"
              @click="showCreateDialog = false"
            >
              Cancel
            </Button>
            <Button
              type="submit"
              @click="createUser"
              :disabled="createForm.processing"
            >
              {{ createForm.processing ? 'Creating...' : 'Create User' }}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      <!-- Edit User Dialog -->
      <Dialog v-model:open="showEditDialog">
        <DialogContent class="sm:max-w-[425px]">
          <DialogHeader>
            <DialogTitle>Edit User</DialogTitle>
            <DialogDescription>
              Update user information and permissions
            </DialogDescription>
          </DialogHeader>
          <form @submit.prevent="updateUser" class="space-y-4">
            <div class="space-y-2">
              <Label for="edit_name">Full Name</Label>
              <Input
                id="edit_name"
                v-model="editForm.name"
                placeholder="Enter full name"
                required
              />
              <div v-if="editForm.errors.name" class="text-sm text-destructive">
                {{ editForm.errors.name }}
              </div>
            </div>

            <div class="space-y-2">
              <Label for="edit_email">Email Address</Label>
              <Input
                id="edit_email"
                v-model="editForm.email"
                type="email"
                placeholder="user@company.com"
                required
              />
              <div v-if="editForm.errors.email" class="text-sm text-destructive">
                {{ editForm.errors.email }}
              </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
              <div class="space-y-2">
                <Label for="edit_role">Role</Label>
                <Select v-model="editForm.role">
                  <SelectTrigger>
                    <SelectValue placeholder="Select role" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem
                      v-for="role in roles"
                      :key="role.name"
                      :value="role.name"
                    >
                      {{ role.display_name }}
                    </SelectItem>
                  </SelectContent>
                </Select>
                <div v-if="editForm.errors.role" class="text-sm text-destructive">
                  {{ editForm.errors.role }}
                </div>
              </div>

              <div class="space-y-2">
                <Label for="edit_team">Team</Label>
                <Select v-model="editForm.team_id">
                  <SelectTrigger>
                    <SelectValue placeholder="Select team" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem
                      v-for="team in teams"
                      :key="team.id"
                      :value="team.id.toString()"
                    >
                      {{ team.name }}
                    </SelectItem>
                  </SelectContent>
                </Select>
                <div v-if="editForm.errors.team_id" class="text-sm text-destructive">
                  {{ editForm.errors.team_id }}
                </div>
              </div>
            </div>
          </form>
          <DialogFooter>
            <Button
              type="button"
              variant="outline"
              @click="showEditDialog = false"
            >
              Cancel
            </Button>
            <Button
              type="submit"
              @click="updateUser"
              :disabled="editForm.processing"
            >
              {{ editForm.processing ? 'Updating...' : 'Update User' }}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  </SettingsLayout>
</template>
