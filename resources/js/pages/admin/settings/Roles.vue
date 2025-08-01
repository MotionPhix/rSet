<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { 
  ShieldIcon,
  UsersIcon,
  CheckIcon,
  XIcon,
  EditIcon
} from 'lucide-vue-next';

interface Permission {
  id: number;
  name: string;
  display_name: string;
  description: string;
  category: string;
}

interface Role {
  id: number;
  name: string;
  display_name: string;
  description: string;
  users_count: number;
  permissions: Permission[];
  created_at: string;
}

interface Props {
  roles: Role[];
  permissions: Permission[];
  permission_categories: string[];
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
    title: 'Roles & Permissions',
    href: route('admin.settings.roles')
  }
]);

// State
const editingRole = ref<Role | null>(null);
const showPermissionsDialog = ref(false);

const permissionsForm = useForm({
  permissions: [] as number[]
});

// Edit role permissions
const editRolePermissions = (role: Role) => {
  editingRole.value = role;
  permissionsForm.permissions = role.permissions.map(p => p.id);
  showPermissionsDialog.value = true;
};

const updatePermissions = () => {
  if (!editingRole.value) return;
  
  permissionsForm.patch(route('admin.settings.roles.permissions', editingRole.value.id), {
    onSuccess: () => {
      showPermissionsDialog.value = false;
      editingRole.value = null;
      permissionsForm.reset();
    }
  });
};

const cancelEdit = () => {
  showPermissionsDialog.value = false;
  editingRole.value = null;
  permissionsForm.reset();
};

// Group permissions by category
const permissionsByCategory = computed(() => {
  const grouped: { [key: string]: Permission[] } = {};
  
  props.permission_categories?.forEach(category => {
    grouped[category] = props.permissions?.filter(p => p.category === category) || [];
  });
  
  return grouped;
});

// Check if permission is selected
const isPermissionSelected = (permissionId: number) => {
  return permissionsForm.permissions.includes(permissionId);
};

// Toggle permission
const togglePermission = (permissionId: number) => {
  const index = permissionsForm.permissions.indexOf(permissionId);
  if (index > -1) {
    permissionsForm.permissions.splice(index, 1);
  } else {
    permissionsForm.permissions.push(permissionId);
  }
};

// Format date
const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString();
};

// Get role color
const getRoleColor = (roleName: string) => {
  const colorMap: { [key: string]: string } = {
    'admin': 'bg-red-100 text-red-800',
    'hr': 'bg-blue-100 text-blue-800',
    'manager': 'bg-purple-100 text-purple-800',
    'employee': 'bg-green-100 text-green-800'
  };
  
  return colorMap[roleName.toLowerCase()] || 'bg-gray-100 text-gray-800';
};

// Statistics
const totalPermissions = computed(() => props.permissions?.length || 0);
const totalRoles = computed(() => props.roles?.length || 0);
const totalUsers = computed(() => props.roles?.reduce((sum, role) => sum + role.users_count, 0) || 0);
</script>

<template>
  <Head title="Roles & Permissions" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
      <!-- Header -->
      <div class="space-y-1">
        <h1 class="text-3xl font-bold tracking-tight">Roles & Permissions</h1>
        <p class="text-muted-foreground">
          Manage user roles and their associated permissions
        </p>
      </div>

      <!-- Overview Stats -->
      <div class="grid gap-4 md:grid-cols-4">
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Roles</CardTitle>
            <ShieldIcon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ totalRoles }}</div>
            <p class="text-xs text-muted-foreground">
              System roles
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Users</CardTitle>
            <UsersIcon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ totalUsers }}</div>
            <p class="text-xs text-muted-foreground">
              With assigned roles
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Permissions</CardTitle>
            <ShieldIcon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ totalPermissions }}</div>
            <p class="text-xs text-muted-foreground">
              Available permissions
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Categories</CardTitle>
            <ShieldIcon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ props.permission_categories?.length || 0 }}</div>
            <p class="text-xs text-muted-foreground">
              Permission groups
            </p>
          </CardContent>
        </Card>
      </div>

      <Tabs default-value="roles" class="w-full">
        <TabsList>
          <TabsTrigger value="roles">Roles</TabsTrigger>
          <TabsTrigger value="permissions">Permissions</TabsTrigger>
        </TabsList>

        <!-- Roles Tab -->
        <TabsContent value="roles" class="space-y-4">
          <Card>
            <CardHeader>
              <CardTitle>User Roles</CardTitle>
              <CardDescription>
                Manage roles and their permissions within your organization
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div class="rounded-md border">
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Role</TableHead>
                      <TableHead>Description</TableHead>
                      <TableHead>Users</TableHead>
                      <TableHead>Permissions</TableHead>
                      <TableHead>Created</TableHead>
                      <TableHead class="text-right">Actions</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    <TableRow 
                      v-for="role in props.roles" 
                      :key="role.id"
                      class="hover:bg-muted/50"
                    >
                      <TableCell>
                        <div class="flex items-center space-x-3">
                          <Badge :class="getRoleColor(role.name)">
                            {{ role.display_name }}
                          </Badge>
                        </div>
                      </TableCell>
                      <TableCell>
                        <p class="text-sm text-muted-foreground max-w-xs">
                          {{ role.description }}
                        </p>
                      </TableCell>
                      <TableCell>
                        <div class="flex items-center space-x-2">
                          <UsersIcon class="h-4 w-4 text-muted-foreground" />
                          <span class="font-medium">{{ role.users_count }}</span>
                        </div>
                      </TableCell>
                      <TableCell>
                        <Badge variant="outline">
                          {{ role.permissions.length }} permissions
                        </Badge>
                      </TableCell>
                      <TableCell class="text-sm text-muted-foreground">
                        {{ formatDate(role.created_at) }}
                      </TableCell>
                      <TableCell class="text-right">
                        <Button 
                          variant="ghost" 
                          size="sm" 
                          @click="editRolePermissions(role)"
                        >
                          <EditIcon class="h-4 w-4 mr-2" />
                          Permissions
                        </Button>
                      </TableCell>
                    </TableRow>
                  </TableBody>
                </Table>
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        <!-- Permissions Tab -->
        <TabsContent value="permissions" class="space-y-4">
          <div class="grid gap-4">
            <Card v-for="(permissions, category) in permissionsByCategory" :key="category">
              <CardHeader>
                <CardTitle class="capitalize">{{ category.replace('_', ' ') }}</CardTitle>
                <CardDescription>
                  {{ permissions.length }} permissions in this category
                </CardDescription>
              </CardHeader>
              <CardContent>
                <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-3">
                  <div 
                    v-for="permission in permissions" 
                    :key="permission.id"
                    class="flex items-start space-x-3 p-3 border rounded-lg"
                  >
                    <div class="flex-1">
                      <p class="font-medium text-sm">{{ permission.display_name }}</p>
                      <p class="text-xs text-muted-foreground">{{ permission.description }}</p>
                      <p class="text-xs text-muted-foreground mt-1">
                        <code>{{ permission.name }}</code>
                      </p>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
        </TabsContent>
      </Tabs>

      <!-- Edit Permissions Dialog -->
      <div v-if="showPermissionsDialog && editingRole" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <Card class="w-full max-w-4xl mx-4 max-h-[80vh] overflow-hidden">
          <CardHeader>
            <div class="flex items-center justify-between">
              <div>
                <CardTitle>Edit Permissions - {{ editingRole.display_name }}</CardTitle>
                <CardDescription>
                  Select the permissions for this role
                </CardDescription>
              </div>
              <Button variant="ghost" size="sm" @click="cancelEdit">
                <XIcon class="h-4 w-4" />
              </Button>
            </div>
          </CardHeader>
          <CardContent class="overflow-y-auto max-h-[60vh]">
            <form @submit.prevent="updatePermissions" class="space-y-6">
              <div class="space-y-6">
                <div v-for="(permissions, category) in permissionsByCategory" :key="category">
                  <h3 class="font-semibold text-lg mb-3 capitalize">{{ category.replace('_', ' ') }}</h3>
                  <div class="grid gap-3 md:grid-cols-2">
                    <div 
                      v-for="permission in permissions" 
                      :key="permission.id"
                      class="flex items-start space-x-3 p-3 border rounded-lg cursor-pointer hover:bg-muted/50"
                      @click="togglePermission(permission.id)"
                    >
                      <div class="mt-1">
                        <div 
                          class="w-4 h-4 border-2 rounded flex items-center justify-center"
                          :class="isPermissionSelected(permission.id) ? 'bg-primary border-primary' : 'border-gray-300'"
                        >
                          <CheckIcon 
                            v-if="isPermissionSelected(permission.id)" 
                            class="h-3 w-3 text-white" 
                          />
                        </div>
                      </div>
                      <div class="flex-1">
                        <p class="font-medium text-sm">{{ permission.display_name }}</p>
                        <p class="text-xs text-muted-foreground">{{ permission.description }}</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="flex justify-end space-x-2 pt-4 border-t">
                <Button variant="outline" @click="cancelEdit">
                  Cancel
                </Button>
                <Button type="submit" :disabled="permissionsForm.processing">
                  {{ permissionsForm.processing ? 'Updating...' : 'Update Permissions' }}
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
