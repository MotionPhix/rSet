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
  CalendarIcon,
  PlusIcon,
  EditIcon,
  TrashIcon,
  XIcon,
  ToggleLeftIcon,
  ToggleRightIcon
} from 'lucide-vue-next';

interface LeaveType {
  id: number;
  name: string;
  description: string;
  color: string;
  days_allowed: number;
  requires_approval: boolean;
  carry_forward: boolean;
  is_active: boolean;
  usage_count: number;
  created_at: string;
}

interface Props {
  leave_types: LeaveType[];
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
    title: 'Leave Types',
    href: route('admin.settings.leave-types')
  }
]);

// Form state
const showCreateForm = ref(false);
const showEditForm = ref(false);
const editingLeaveType = ref<LeaveType | null>(null);

const createForm = useForm({
  name: '',
  description: '',
  color: '#3b82f6',
  days_allowed: 20,
  requires_approval: true,
  carry_forward: false,
  is_active: true
});

const editForm = useForm({
  name: '',
  description: '',
  color: '#3b82f6',
  days_allowed: 20,
  requires_approval: true,
  carry_forward: false,
  is_active: true
});

// Predefined colors
const colorOptions = [
  '#3b82f6', // Blue
  '#10b981', // Green
  '#f59e0b', // Yellow
  '#ef4444', // Red
  '#8b5cf6', // Purple
  '#06b6d4', // Cyan
  '#f97316', // Orange
  '#84cc16', // Lime
  '#ec4899', // Pink
  '#6b7280'  // Gray
];

// Create leave type
const createLeaveType = () => {
  createForm.post(route('admin.settings.leave-types.store'), {
    onSuccess: () => {
      showCreateForm.value = false;
      createForm.reset();
    }
  });
};

// Edit leave type
const editLeaveType = (leaveType: LeaveType) => {
  editingLeaveType.value = leaveType;
  editForm.name = leaveType.name;
  editForm.description = leaveType.description;
  editForm.color = leaveType.color;
  editForm.days_allowed = leaveType.days_allowed;
  editForm.requires_approval = leaveType.requires_approval;
  editForm.carry_forward = leaveType.carry_forward;
  editForm.is_active = leaveType.is_active;
  showEditForm.value = true;
};

const updateLeaveType = () => {
  if (!editingLeaveType.value) return;
  
  editForm.patch(route('admin.settings.leave-types.update', editingLeaveType.value.id), {
    onSuccess: () => {
      showEditForm.value = false;
      editingLeaveType.value = null;
      editForm.reset();
    }
  });
};

// Delete leave type
const deleteLeaveType = (leaveType: LeaveType) => {
  if (leaveType.usage_count > 0) {
    alert(`Cannot delete "${leaveType.name}" because it has been used in ${leaveType.usage_count} leave requests.`);
    return;
  }
  
  if (confirm(`Are you sure you want to delete the leave type "${leaveType.name}"? This action cannot be undone.`)) {
    router.delete(route('admin.settings.leave-types.destroy', leaveType.id));
  }
};

// Cancel forms
const cancelCreate = () => {
  showCreateForm.value = false;
  createForm.reset();
};

const cancelEdit = () => {
  showEditForm.value = false;
  editingLeaveType.value = null;
  editForm.reset();
};

// Format date
const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString();
};

// Statistics
const totalAllowedDays = computed(() => {
  return props.leave_types?.reduce((sum, type) => sum + type.days_allowed, 0) || 0;
});

const activeLeaveTypes = computed(() => {
  return props.leave_types?.filter(type => type.is_active).length || 0;
});

const mostUsedLeaveType = computed(() => {
  const sorted = [...(props.leave_types || [])].sort((a, b) => b.usage_count - a.usage_count);
  return sorted[0]?.name || 'None';
});
</script>

<template>
  <Head title="Leave Types Management" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div class="space-y-1">
          <h1 class="text-3xl font-bold tracking-tight">Leave Types Management</h1>
          <p class="text-muted-foreground">
            Configure the types of leave available to your employees
          </p>
        </div>
        <Button @click="showCreateForm = true">
          <PlusIcon class="h-4 w-4 mr-2" />
          Add Leave Type
        </Button>
      </div>

      <!-- Leave Types Overview -->
      <div class="grid gap-4 md:grid-cols-4">
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Leave Types</CardTitle>
            <CalendarIcon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ props.leave_types?.length || 0 }}</div>
            <p class="text-xs text-muted-foreground">
              Configured types
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Active Types</CardTitle>
            <CalendarIcon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ activeLeaveTypes }}</div>
            <p class="text-xs text-muted-foreground">
              Currently available
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Days</CardTitle>
            <CalendarIcon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ totalAllowedDays }}</div>
            <p class="text-xs text-muted-foreground">
              Allowed annually
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Most Used</CardTitle>
            <CalendarIcon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-lg font-bold">{{ mostUsedLeaveType }}</div>
            <p class="text-xs text-muted-foreground">
              Popular type
            </p>
          </CardContent>
        </Card>
      </div>

      <!-- Leave Types Table -->
      <Card>
        <CardHeader>
          <CardTitle>Leave Types</CardTitle>
          <CardDescription>
            Manage the different types of leave your employees can request
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="rounded-md border">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Name</TableHead>
                  <TableHead>Description</TableHead>
                  <TableHead>Days Allowed</TableHead>
                  <TableHead>Settings</TableHead>
                  <TableHead>Usage</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead class="text-right">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-if="!props.leave_types?.length">
                  <TableCell colspan="7" class="text-center text-muted-foreground py-8">
                    No leave types found. Create your first leave type to get started.
                  </TableCell>
                </TableRow>
                <TableRow 
                  v-for="leaveType in props.leave_types" 
                  :key="leaveType.id"
                  class="hover:bg-muted/50"
                >
                  <TableCell>
                    <div class="flex items-center space-x-3">
                      <div 
                        class="w-4 h-4 rounded-full"
                        :style="{ backgroundColor: leaveType.color }"
                      ></div>
                      <div>
                        <p class="font-medium">{{ leaveType.name }}</p>
                      </div>
                    </div>
                  </TableCell>
                  <TableCell>
                    <p class="text-sm text-muted-foreground max-w-xs truncate">
                      {{ leaveType.description || 'No description' }}
                    </p>
                  </TableCell>
                  <TableCell>
                    <Badge variant="outline">
                      {{ leaveType.days_allowed }} {{ leaveType.days_allowed === 1 ? 'day' : 'days' }}
                    </Badge>
                  </TableCell>
                  <TableCell>
                    <div class="space-y-1">
                      <div class="flex items-center space-x-2 text-sm">
                        <component 
                          :is="leaveType.requires_approval ? ToggleRightIcon : ToggleLeftIcon"
                          class="h-4 w-4"
                          :class="leaveType.requires_approval ? 'text-green-600' : 'text-gray-400'"
                        />
                        <span class="text-xs">Approval</span>
                      </div>
                      <div class="flex items-center space-x-2 text-sm">
                        <component 
                          :is="leaveType.carry_forward ? ToggleRightIcon : ToggleLeftIcon"
                          class="h-4 w-4"
                          :class="leaveType.carry_forward ? 'text-green-600' : 'text-gray-400'"
                        />
                        <span class="text-xs">Carry Forward</span>
                      </div>
                    </div>
                  </TableCell>
                  <TableCell>
                    <div class="text-sm">
                      <p class="font-medium">{{ leaveType.usage_count }}</p>
                      <p class="text-muted-foreground text-xs">requests</p>
                    </div>
                  </TableCell>
                  <TableCell>
                    <Badge :variant="leaveType.is_active ? 'default' : 'secondary'">
                      {{ leaveType.is_active ? 'Active' : 'Inactive' }}
                    </Badge>
                  </TableCell>
                  <TableCell class="text-right">
                    <div class="flex items-center justify-end space-x-2">
                      <Button variant="ghost" size="sm" @click="editLeaveType(leaveType)">
                        <EditIcon class="h-4 w-4" />
                      </Button>
                      <Button 
                        variant="ghost" 
                        size="sm" 
                        @click="deleteLeaveType(leaveType)"
                        class="text-red-600 hover:text-red-700"
                        :disabled="leaveType.usage_count > 0"
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

      <!-- Create Leave Type Dialog -->
      <div v-if="showCreateForm" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <Card class="w-full max-w-lg mx-4">
          <CardHeader>
            <div class="flex items-center justify-between">
              <CardTitle>Create New Leave Type</CardTitle>
              <Button variant="ghost" size="sm" @click="cancelCreate">
                <XIcon class="h-4 w-4" />
              </Button>
            </div>
            <CardDescription>
              Add a new type of leave for your employees
            </CardDescription>
          </CardHeader>
          <CardContent>
            <form @submit.prevent="createLeaveType" class="space-y-4">
              <div class="grid gap-4 md:grid-cols-2">
                <div class="space-y-2">
                  <Label for="create_name">Leave Type Name *</Label>
                  <Input 
                    id="create_name"
                    v-model="createForm.name" 
                    placeholder="e.g., Annual Leave, Sick Leave"
                    required
                    :class="{ 'border-red-500': createForm.errors.name }"
                  />
                  <p v-if="createForm.errors.name" class="text-sm text-red-500">{{ createForm.errors.name }}</p>
                </div>

                <div class="space-y-2">
                  <Label for="create_days">Days Allowed *</Label>
                  <Input 
                    id="create_days"
                    v-model.number="createForm.days_allowed" 
                    type="number"
                    min="1"
                    max="365"
                    required
                    :class="{ 'border-red-500': createForm.errors.days_allowed }"
                  />
                  <p v-if="createForm.errors.days_allowed" class="text-sm text-red-500">{{ createForm.errors.days_allowed }}</p>
                </div>
              </div>

              <div class="space-y-2">
                <Label for="create_description">Description</Label>
                <Textarea 
                  id="create_description"
                  v-model="createForm.description" 
                  placeholder="Brief description of this leave type..."
                  rows="3"
                  :class="{ 'border-red-500': createForm.errors.description }"
                />
                <p v-if="createForm.errors.description" class="text-sm text-red-500">{{ createForm.errors.description }}</p>
              </div>

              <div class="space-y-2">
                <Label for="create_color">Color</Label>
                <div class="flex space-x-2">
                  <button
                    v-for="color in colorOptions"
                    :key="color"
                    type="button"
                    class="w-8 h-8 rounded-full border-2 border-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                    :class="{ 'ring-2 ring-primary': createForm.color === color }"
                    :style="{ backgroundColor: color }"
                    @click="createForm.color = color"
                  ></button>
                </div>
              </div>

              <div class="space-y-4">
                <div class="flex items-center justify-between">
                  <div class="space-y-0.5">
                    <Label>Requires Approval</Label>
                    <p class="text-sm text-muted-foreground">Must be approved by manager</p>
                  </div>
                  <input 
                    type="checkbox" 
                    v-model="createForm.requires_approval"
                    class="w-4 h-4 text-primary"
                  />
                </div>

                <div class="flex items-center justify-between">
                  <div class="space-y-0.5">
                    <Label>Carry Forward</Label>
                    <p class="text-sm text-muted-foreground">Unused days carry to next year</p>
                  </div>
                  <input 
                    type="checkbox" 
                    v-model="createForm.carry_forward"
                    class="w-4 h-4 text-primary"
                  />
                </div>

                <div class="flex items-center justify-between">
                  <div class="space-y-0.5">
                    <Label>Active</Label>
                    <p class="text-sm text-muted-foreground">Available for employees to use</p>
                  </div>
                  <input 
                    type="checkbox" 
                    v-model="createForm.is_active"
                    class="w-4 h-4 text-primary"
                  />
                </div>
              </div>

              <div class="flex justify-end space-x-2">
                <Button variant="outline" @click="cancelCreate">
                  Cancel
                </Button>
                <Button type="submit" :disabled="createForm.processing">
                  {{ createForm.processing ? 'Creating...' : 'Create Leave Type' }}
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>

      <!-- Edit Leave Type Dialog -->
      <div v-if="showEditForm && editingLeaveType" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <Card class="w-full max-w-lg mx-4">
          <CardHeader>
            <div class="flex items-center justify-between">
              <CardTitle>Edit Leave Type</CardTitle>
              <Button variant="ghost" size="sm" @click="cancelEdit">
                <XIcon class="h-4 w-4" />
              </Button>
            </div>
            <CardDescription>
              Update leave type configuration
            </CardDescription>
          </CardHeader>
          <CardContent>
            <form @submit.prevent="updateLeaveType" class="space-y-4">
              <div class="grid gap-4 md:grid-cols-2">
                <div class="space-y-2">
                  <Label for="edit_name">Leave Type Name *</Label>
                  <Input 
                    id="edit_name"
                    v-model="editForm.name" 
                    placeholder="e.g., Annual Leave, Sick Leave"
                    required
                    :class="{ 'border-red-500': editForm.errors.name }"
                  />
                  <p v-if="editForm.errors.name" class="text-sm text-red-500">{{ editForm.errors.name }}</p>
                </div>

                <div class="space-y-2">
                  <Label for="edit_days">Days Allowed *</Label>
                  <Input 
                    id="edit_days"
                    v-model.number="editForm.days_allowed" 
                    type="number"
                    min="1"
                    max="365"
                    required
                    :class="{ 'border-red-500': editForm.errors.days_allowed }"
                  />
                  <p v-if="editForm.errors.days_allowed" class="text-sm text-red-500">{{ editForm.errors.days_allowed }}</p>
                </div>
              </div>

              <div class="space-y-2">
                <Label for="edit_description">Description</Label>
                <Textarea 
                  id="edit_description"
                  v-model="editForm.description" 
                  placeholder="Brief description of this leave type..."
                  rows="3"
                  :class="{ 'border-red-500': editForm.errors.description }"
                />
                <p v-if="editForm.errors.description" class="text-sm text-red-500">{{ editForm.errors.description }}</p>
              </div>

              <div class="space-y-2">
                <Label for="edit_color">Color</Label>
                <div class="flex space-x-2">
                  <button
                    v-for="color in colorOptions"
                    :key="color"
                    type="button"
                    class="w-8 h-8 rounded-full border-2 border-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                    :class="{ 'ring-2 ring-primary': editForm.color === color }"
                    :style="{ backgroundColor: color }"
                    @click="editForm.color = color"
                  ></button>
                </div>
              </div>

              <div class="space-y-4">
                <div class="flex items-center justify-between">
                  <div class="space-y-0.5">
                    <Label>Requires Approval</Label>
                    <p class="text-sm text-muted-foreground">Must be approved by manager</p>
                  </div>
                  <input 
                    type="checkbox" 
                    v-model="editForm.requires_approval"
                    class="w-4 h-4 text-primary"
                  />
                </div>

                <div class="flex items-center justify-between">
                  <div class="space-y-0.5">
                    <Label>Carry Forward</Label>
                    <p class="text-sm text-muted-foreground">Unused days carry to next year</p>
                  </div>
                  <input 
                    type="checkbox" 
                    v-model="editForm.carry_forward"
                    class="w-4 h-4 text-primary"
                  />
                </div>

                <div class="flex items-center justify-between">
                  <div class="space-y-0.5">
                    <Label>Active</Label>
                    <p class="text-sm text-muted-foreground">Available for employees to use</p>
                  </div>
                  <input 
                    type="checkbox" 
                    v-model="editForm.is_active"
                    class="w-4 h-4 text-primary"
                  />
                </div>
              </div>

              <div class="flex justify-end space-x-2">
                <Button variant="outline" @click="cancelEdit">
                  Cancel
                </Button>
                <Button type="submit" :disabled="editForm.processing">
                  {{ editForm.processing ? 'Updating...' : 'Update Leave Type' }}
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
