<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { toast } from 'vue-sonner';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Separator } from '@/components/ui/separator';
import {
  User,
  Mail,
  Calendar,
  Building2,
  Users,
  Shield,
  Upload,
  Camera
} from 'lucide-vue-next';
import { type SharedData, type User as UserType } from '@/types';

interface Props {
  mustVerifyEmail: boolean;
  status?: string;
}

defineProps<Props>();

const page = usePage<SharedData>();
const user = computed(() => page.props.auth.user as UserType);
const company = computed(() => page.props.auth.company);

const form = useForm({
  name: user.value.name,
  email: user.value.email,
});

const submit = () => {
  form.patch(route('employee.settings.profile.update'), {
    preserveScroll: true,
    onSuccess: () => {
      toast.success('Profile updated successfully');
    },
    onError: () => {
      toast.error('Failed to update profile');
    },
  });
};

const getInitials = (name: string) => {
  return name.split(' ').map(n => n[0]).join('').toUpperCase();
};

const getRoleBadge = (roles: any[]) => {
  if (!roles || roles.length === 0) return { variant: 'outline', label: 'Employee' };

  const role = roles[0].name;
  switch (role) {
    case 'admin': return { variant: 'default', label: 'Administrator' };
    case 'hr': return { variant: 'secondary', label: 'HR Manager' };
    case 'manager': return { variant: 'outline', label: 'Team Manager' };
    default: return { variant: 'outline', label: 'Employee' };
  }
};

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};
</script>

<template>
  <Head title="Profile Settings" />

  <SettingsLayout>
    <div class="space-y-6 p-6">
      <!-- Header -->
      <HeadingSmall
        title="Profile Settings"
        description="Manage your personal information and account details"
      />

      <!-- Profile Overview -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <User class="h-5 w-5" />
            Profile Overview
          </CardTitle>
          <CardDescription>
            Your current profile information and account status
          </CardDescription>
        </CardHeader>
        <CardContent class="space-y-6">
          <!-- Profile Summary -->
          <div class="flex items-start space-x-4">
            <div class="relative">
              <Avatar class="h-20 w-20">
                <AvatarImage :src="user.avatar" />
                <AvatarFallback class="text-lg">{{ getInitials(user.name) }}</AvatarFallback>
              </Avatar>
              <Button
                size="sm"
                variant="outline"
                class="absolute -bottom-2 -right-2 h-8 w-8 rounded-full p-0"
              >
                <Camera class="h-4 w-4" />
                <span class="sr-only">Change avatar</span>
              </Button>
            </div>
            <div class="space-y-2 flex-1">
              <div>
                <h3 class="text-lg font-semibold">{{ user.name }}</h3>
                <p class="text-muted-foreground">{{ user.email }}</p>
              </div>
              <div class="flex items-center gap-2">
                <Badge :variant="getRoleBadge(user.roles).variant">
                  {{ getRoleBadge(user.roles).label }}
                </Badge>
                <Badge v-if="user.team" variant="outline">
                  <Users class="h-3 w-3 mr-1" />
                  {{ user.team.name }}
                </Badge>
              </div>
            </div>
          </div>

          <Separator />

          <!-- Account Information -->
          <div class="grid gap-4 md:grid-cols-2">
            <div class="space-y-2">
              <div class="text-sm text-muted-foreground">Company</div>
              <div class="flex items-center gap-2">
                <Building2 class="h-4 w-4 text-muted-foreground" />
                <span>{{ company?.name || 'Not assigned' }}</span>
              </div>
            </div>
            <div class="space-y-2">
              <div class="text-sm text-muted-foreground">Employee ID</div>
              <div class="text-sm font-mono">EMP-{{ user.id.toString().padStart(4, '0') }}</div>
            </div>
            <div class="space-y-2">
              <div class="text-sm text-muted-foreground">Joined</div>
              <div class="flex items-center gap-2">
                <Calendar class="h-4 w-4 text-muted-foreground" />
                <span>{{ formatDate(user.created_at) }}</span>
              </div>
            </div>
            <div class="space-y-2">
              <div class="text-sm text-muted-foreground">Email Status</div>
              <Badge :variant="user.email_verified_at ? 'default' : 'destructive'">
                {{ user.email_verified_at ? 'Verified' : 'Unverified' }}
              </Badge>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Edit Profile Form -->
      <form @submit.prevent="submit">
        <Card>
          <CardHeader>
            <CardTitle>Personal Information</CardTitle>
            <CardDescription>
              Update your personal details and contact information
            </CardDescription>
          </CardHeader>
          <CardContent class="space-y-6">
            <!-- Basic Information -->
            <div class="grid gap-4 md:grid-cols-2">
              <div class="space-y-2">
                <Label for="name">Full Name</Label>
                <div class="relative">
                  <User class="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
                  <Input
                    id="name"
                    v-model="form.name"
                    class="pl-10"
                    placeholder="Enter your full name"
                    required
                  />
                </div>
                <InputError class="mt-2" :message="form.errors.name" />
              </div>

              <div class="space-y-2">
                <Label for="email">Email Address</Label>
                <div class="relative">
                  <Mail class="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
                  <Input
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="pl-10"
                    placeholder="Enter your email"
                    required
                  />
                </div>
                <InputError class="mt-2" :message="form.errors.email" />
              </div>
            </div>

            <!-- Email Verification Notice -->
            <div v-if="mustVerifyEmail && user.email_verified_at === null" class="rounded-lg border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-800 dark:bg-yellow-900/20">
              <div class="flex items-center gap-2 text-yellow-800 dark:text-yellow-200">
                <Mail class="h-4 w-4" />
                <div class="text-sm">
                  <p class="font-medium">Email Verification Required</p>
                  <p class="mt-1">
                    Your email address is unverified. Please check your inbox for a verification link.
                  </p>
                  <Link
                    :href="route('verification.send')"
                    method="post"
                    as="button"
                    class="mt-2 text-sm underline hover:no-underline"
                  >
                    Resend verification email
                  </Link>
                </div>
              </div>
            </div>

            <!-- Success Message -->
            <div v-show="status === 'profile-updated'" class="rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-900/20">
              <div class="flex items-center gap-2 text-green-800 dark:text-green-200">
                <Shield class="h-4 w-4" />
                <span class="text-sm font-medium">Profile updated successfully!</span>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Action Buttons -->
        <div class="flex justify-end gap-2 mt-6">
          <Button
            type="submit"
            :disabled="form.processing"
          >
            {{ form.processing ? 'Saving...' : 'Save Changes' }}
          </Button>
        </div>
      </form>

      <!-- Account Management -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Shield class="h-5 w-5" />
            Account Management
          </CardTitle>
          <CardDescription>
            Manage your account security and preferences
          </CardDescription>
        </CardHeader>
        <CardContent class="space-y-4">
          <div class="grid gap-4">
            <div class="flex items-center justify-between p-4 border rounded-lg">
              <div class="space-y-1">
                <div class="font-medium">Password</div>
                <div class="text-sm text-muted-foreground">
                  Update your password to keep your account secure
                </div>
              </div>
              <Button variant="outline" as-child>
                <Link :href="route('employee.settings.password')">
                  Change Password
                </Link>
              </Button>
            </div>

            <div class="flex items-center justify-between p-4 border rounded-lg">
              <div class="space-y-1">
                <div class="font-medium">Appearance</div>
                <div class="text-sm text-muted-foreground">
                  Customize your app theme and display preferences
                </div>
              </div>
              <Button variant="outline" as-child>
                <Link :href="route('employee.settings.appearance')">
                  Customize
                </Link>
              </Button>
            </div>

            <div class="flex items-center justify-between p-4 border rounded-lg">
              <div class="space-y-1">
                <div class="font-medium">My Reports</div>
                <div class="text-sm text-muted-foreground">
                  View your personal leave reports and statistics
                </div>
              </div>
              <Button variant="outline" as-child>
                <Link :href="route('employee.reports.index')">
                  View Reports
                </Link>
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </SettingsLayout>
</template>
