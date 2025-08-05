<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import AppLayout from '@/layouts/AppLayout.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { 
  Dialog, 
  DialogContent, 
  DialogDescription, 
  DialogFooter, 
  DialogHeader, 
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog';
import { 
  AlertDialog, 
  AlertDialogAction, 
  AlertDialogCancel, 
  AlertDialogContent, 
  AlertDialogDescription, 
  AlertDialogFooter, 
  AlertDialogHeader, 
  AlertDialogTitle, 
  AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import DatePicker from '@/components/DatePicker.vue';
import {
  Calendar as CalendarIcon,
  Plus,
  Edit,
  Trash2,
  Users,
  RefreshCw,
  Star,
  Filter,
} from 'lucide-vue-next';
import { format, parseISO } from 'date-fns';

interface Holiday {
  id: number;
  uuid: string;
  name: string;
  description?: string;
  date: string;
  is_recurring: boolean;
  recurrence_type: 'yearly' | 'none';
  color: string;
  is_active: boolean;
  created_by: number;
  creator: {
    id: number;
    name: string;
  };
}

interface Props {
  holidays: Holiday[];
  currentYear: number;
  years: number[];
}

const props = defineProps<Props>();

// Reactive state
const showCreateDialog = ref(false);
const showEditDialog = ref(false);
const editingHoliday = ref<Holiday | null>(null);
const selectedYear = ref(props.currentYear);

// Form data
const form = ref({
  name: '',
  description: '',
  date: '',
  is_recurring: false,
  color: '#ef4444',
});

// Predefined holiday colors
const holidayColors = [
  { value: '#ef4444', label: 'Red', class: 'bg-red-500' },
  { value: '#f97316', label: 'Orange', class: 'bg-orange-500' },
  { value: '#eab308', label: 'Yellow', class: 'bg-yellow-500' },
  { value: '#22c55e', label: 'Green', class: 'bg-green-500' },
  { value: '#3b82f6', label: 'Blue', class: 'bg-blue-500' },
  { value: '#8b5cf6', label: 'Purple', class: 'bg-purple-500' },
  { value: '#ec4899', label: 'Pink', class: 'bg-pink-500' },
  { value: '#6b7280', label: 'Gray', class: 'bg-gray-500' },
];

// Computed properties
const filteredHolidays = computed(() => {
  return props.holidays.filter(holiday => {
    const holidayYear = new Date(holiday.date).getFullYear();
    return holidayYear === selectedYear.value;
  });
});

const upcomingHolidays = computed(() => {
  const today = new Date();
  return filteredHolidays.value
    .filter(holiday => new Date(holiday.date) >= today)
    .sort((a, b) => new Date(a.date).getTime() - new Date(b.date).getTime())
    .slice(0, 3);
});

// Functions
const resetForm = () => {
  form.value = {
    name: '',
    description: '',
    date: format(new Date(), 'yyyy-MM-dd'),
    is_recurring: false,
    color: '#ef4444',
  };
};

const openCreateDialog = () => {
  resetForm();
  showCreateDialog.value = true;
};

const openEditDialog = (holiday: Holiday) => {
  editingHoliday.value = holiday;
  form.value = {
    name: holiday.name,
    description: holiday.description || '',
    date: holiday.date,
    is_recurring: holiday.is_recurring,
    color: holiday.color,
  };
  showEditDialog.value = true;
};

const createHoliday = () => {
  router.post(route('admin.holidays.store'), {
    name: form.value.name,
    description: form.value.description,
    date: form.value.date,
    is_recurring: form.value.is_recurring,
    color: form.value.color,
  }, {
    onSuccess: () => {
      toast.success('Holiday created successfully!');
      showCreateDialog.value = false;
      resetForm();
    },
    onError: (errors) => {
      const errorMessages = Object.values(errors).flat();
      errorMessages.forEach((message) => {
        toast.error(message as string);
      });
    },
  });
};

const updateHoliday = () => {
  if (!editingHoliday.value) return;

  router.put(route('admin.holidays.update', editingHoliday.value.id), {
    name: form.value.name,
    description: form.value.description,
    date: form.value.date,
    is_recurring: form.value.is_recurring,
    color: form.value.color,
    is_active: true,
  }, {
    onSuccess: () => {
      toast.success('Holiday updated successfully!');
      showEditDialog.value = false;
      editingHoliday.value = null;
      resetForm();
    },
    onError: (errors) => {
      const errorMessages = Object.values(errors).flat();
      errorMessages.forEach((message) => {
        toast.error(message as string);
      });
    },
  });
};

const deleteHoliday = (holiday: Holiday) => {
  router.delete(route('admin.holidays.destroy', holiday.id), {
    onSuccess: () => {
      toast.success('Holiday deleted successfully!');
    },
    onError: () => {
      toast.error('Failed to delete holiday.');
    },
  });
};

const changeYear = (year: number) => {
  selectedYear.value = year;
  router.get(route('admin.holidays.index'), { year }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const getHolidayStatusBadge = (holiday: Holiday): { variant: 'default' | 'destructive' | 'outline' | 'secondary', text: string } => {
  const today = new Date();
  const holidayDate = new Date(holiday.date);
  
  if (holidayDate < today) {
    return { variant: 'secondary', text: 'Past' };
  } else if (holidayDate.toDateString() === today.toDateString()) {
    return { variant: 'default', text: 'Today' };
  } else {
    return { variant: 'outline', text: 'Upcoming' };
  }
};
</script>

<template>
  <Head title="Holiday Management" />

  <AppLayout>
    <div class="p-6 space-y-6">
      <!-- Header -->
      <div class="flex justify-between items-center">
        <div>
          <HeadingSmall title="Holiday Management" class="mb-2" />
          <p class="text-gray-600">Manage company holidays and special dates</p>
        </div>
        <Button @click="openCreateDialog" class="gap-2">
          <Plus class="h-4 w-4" />
          Add Holiday
        </Button>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <Card>
          <CardContent class="p-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Total Holidays</p>
                <p class="text-2xl font-bold">{{ filteredHolidays.length }}</p>
              </div>
              <CalendarIcon class="h-8 w-8 text-blue-600" />
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Upcoming</p>
                <p class="text-2xl font-bold">{{ upcomingHolidays.length }}</p>
              </div>
              <Star class="h-8 w-8 text-yellow-600" />
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Recurring</p>
                <p class="text-2xl font-bold">{{ filteredHolidays.filter(h => h.is_recurring).length }}</p>
              </div>
              <RefreshCw class="h-8 w-8 text-green-600" />
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">This Month</p>
                <p class="text-2xl font-bold">{{ filteredHolidays.filter(h => new Date(h.date).getMonth() === new Date().getMonth()).length }}</p>
              </div>
              <Users class="h-8 w-8 text-purple-600" />
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Year Filter -->
      <Card>
        <CardHeader>
          <div class="flex items-center justify-between">
            <CardTitle class="flex items-center gap-2">
              <Filter class="h-5 w-5" />
              Filter by Year
            </CardTitle>
            <Select :model-value="selectedYear.toString()" @update:model-value="(value) => value && changeYear(parseInt(value.toString()))">
              <SelectTrigger class="w-32">
                <SelectValue placeholder="Year" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem v-for="year in years" :key="year" :value="year.toString()">
                  {{ year }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
        </CardHeader>
      </Card>

      <!-- Holidays List -->
      <Card>
        <CardHeader>
          <CardTitle>Holidays for {{ selectedYear }}</CardTitle>
        </CardHeader>
        <CardContent>
          <div v-if="filteredHolidays.length === 0" class="text-center py-8 text-gray-500">
            <CalendarIcon class="h-12 w-12 mx-auto mb-4 text-gray-300" />
            <p class="text-lg font-medium">No holidays found</p>
            <p class="text-sm">Add some holidays to get started</p>
          </div>

          <div v-else class="space-y-4">
            <div
              v-for="holiday in filteredHolidays"
              :key="holiday.id"
              class="flex items-center justify-between p-4 border rounded-lg hover:bg-gray-50 transition-colors"
            >
              <div class="flex items-center gap-4">
                <div
                  class="w-4 h-4 rounded-full"
                  :style="{ backgroundColor: holiday.color }"
                ></div>
                <div>
                  <h3 class="font-medium">{{ holiday.name }}</h3>
                  <p class="text-sm text-gray-600">{{ format(parseISO(holiday.date), 'MMMM dd, yyyy') }}</p>
                  <p v-if="holiday.description" class="text-sm text-gray-500 mt-1">{{ holiday.description }}</p>
                </div>
              </div>

              <div class="flex items-center gap-2">
                <Badge v-if="holiday.is_recurring" variant="secondary">
                  Yearly
                </Badge>
                <Badge :variant="getHolidayStatusBadge(holiday).variant">
                  {{ getHolidayStatusBadge(holiday).text }}
                </Badge>
                <Button
                  variant="ghost"
                  size="sm"
                  @click="openEditDialog(holiday)"
                  class="h-8 w-8 p-0"
                >
                  <Edit class="h-4 w-4" />
                </Button>
                <AlertDialog>
                  <AlertDialogTrigger as-child>
                    <Button variant="ghost" size="sm" class="h-8 w-8 p-0 text-red-600 hover:text-red-700">
                      <Trash2 class="h-4 w-4" />
                    </Button>
                  </AlertDialogTrigger>
                  <AlertDialogContent>
                    <AlertDialogHeader>
                      <AlertDialogTitle>Delete Holiday</AlertDialogTitle>
                      <AlertDialogDescription>
                        Are you sure you want to delete "{{ holiday.name }}"? This action cannot be undone.
                      </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                      <AlertDialogCancel>Cancel</AlertDialogCancel>
                      <AlertDialogAction @click="deleteHoliday(holiday)" class="bg-red-600 hover:bg-red-700">
                        Delete
                      </AlertDialogAction>
                    </AlertDialogFooter>
                  </AlertDialogContent>
                </AlertDialog>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Create Holiday Dialog -->
      <Dialog v-model:open="showCreateDialog">
        <DialogContent class="sm:max-w-md">
          <DialogHeader>
            <DialogTitle>Add New Holiday</DialogTitle>
            <DialogDescription>
              Create a new company holiday. Recurring holidays will appear every year.
            </DialogDescription>
          </DialogHeader>

          <div class="space-y-4">
            <div>
              <Label for="name">Holiday Name</Label>
              <Input
                id="name"
                v-model="form.name"
                placeholder="e.g., Christmas Day"
                class="mt-1"
              />
            </div>

            <div>
              <Label for="description">Description (Optional)</Label>
              <Textarea
                id="description"
                v-model="form.description"
                placeholder="Brief description of the holiday"
                rows="2"
                class="mt-1"
              />
            </div>

            <div>
              <Label for="date">Date</Label>
              <DatePicker
                v-model="form.date"
                class="mt-1"
              />
            </div>

            <div>
              <Label for="color">Color</Label>
              <div class="mt-2 flex gap-2 flex-wrap">
                <button
                  v-for="color in holidayColors"
                  :key="color.value"
                  type="button"
                  @click="form.color = color.value"
                  :class="[
                    'w-8 h-8 rounded-full border-2 transition-all',
                    color.class,
                    form.color === color.value ? 'border-gray-900 scale-110' : 'border-gray-200'
                  ]"
                  :title="color.label"
                />
              </div>
            </div>

            <div class="flex items-center space-x-2">
              <input
                id="recurring"
                type="checkbox"
                v-model="form.is_recurring"
                class="rounded border-gray-300"
              />
              <Label for="recurring">Recurring yearly holiday</Label>
            </div>
          </div>

          <DialogFooter>
            <Button variant="outline" @click="showCreateDialog = false">
              Cancel
            </Button>
            <Button @click="createHoliday" :disabled="!form.name">
              Create Holiday
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      <!-- Edit Holiday Dialog -->
      <Dialog v-model:open="showEditDialog">
        <DialogContent class="sm:max-w-md">
          <DialogHeader>
            <DialogTitle>Edit Holiday</DialogTitle>
            <DialogDescription>
              Update the holiday information.
            </DialogDescription>
          </DialogHeader>

          <div class="space-y-4">
            <div>
              <Label for="edit-name">Holiday Name</Label>
              <Input
                id="edit-name"
                v-model="form.name"
                placeholder="e.g., Christmas Day"
                class="mt-1"
              />
            </div>

            <div>
              <Label for="edit-description">Description (Optional)</Label>
              <Textarea
                id="edit-description"
                v-model="form.description"
                placeholder="Brief description of the holiday"
                rows="2"
                class="mt-1"
              />
            </div>

            <div>
              <Label for="edit-date">Date</Label>
              <DatePicker
                v-model="form.date"
                class="mt-1"
              />
            </div>

            <div>
              <Label for="edit-color">Color</Label>
              <div class="mt-2 flex gap-2 flex-wrap">
                <button
                  v-for="color in holidayColors"
                  :key="color.value"
                  type="button"
                  @click="form.color = color.value"
                  :class="[
                    'w-8 h-8 rounded-full border-2 transition-all',
                    color.class,
                    form.color === color.value ? 'border-gray-900 scale-110' : 'border-gray-200'
                  ]"
                  :title="color.label"
                />
              </div>
            </div>

            <div class="flex items-center space-x-2">
              <input
                id="edit-recurring"
                type="checkbox"
                v-model="form.is_recurring"
                class="rounded border-gray-300"
              />
              <Label for="edit-recurring">Recurring yearly holiday</Label>
            </div>
          </div>

          <DialogFooter>
            <Button variant="outline" @click="showEditDialog = false">
              Cancel
            </Button>
            <Button @click="updateHoliday" :disabled="!form.name">
              Update Holiday
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  </AppLayout>
</template>
