<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { toast } from 'vue-sonner';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Alert, AlertDescription } from '@/components/ui/alert';
import {
  Lock,
  Shield,
  Eye,
  EyeOff,
  CheckCircle2,
  AlertCircle
} from 'lucide-vue-next';

const form = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
});

const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

const updatePassword = () => {
  form.put(route('employee.settings.password.update'), {
    preserveScroll: true,
    onSuccess: () => {
      toast.success('Password updated successfully');
      form.reset();
    },
    onError: () => {
      toast.error('Failed to update password');
    },
  });
};

// Password strength checker
const passwordStrength = computed(() => {
  const password = form.password;
  if (!password) return { score: 0, label: '', color: '' };

  let score = 0;
  const checks = {
    length: password.length >= 8,
    uppercase: /[A-Z]/.test(password),
    lowercase: /[a-z]/.test(password),
    numbers: /\d/.test(password),
    special: /[!@#$%^&*(),.?":{}|<>]/.test(password),
  };

  score = Object.values(checks).filter(Boolean).length;

  const levels = [
    { score: 0, label: '', color: '' },
    { score: 1, label: 'Very Weak', color: 'text-red-500' },
    { score: 2, label: 'Weak', color: 'text-orange-500' },
    { score: 3, label: 'Fair', color: 'text-yellow-500' },
    { score: 4, label: 'Good', color: 'text-blue-500' },
    { score: 5, label: 'Strong', color: 'text-green-500' },
  ];

  return { ...levels[score], checks };
});
</script>

<template>
  <Head title="Password Settings" />

  <SettingsLayout>
    <div class="space-y-6 p-6">
      <!-- Header -->
      <HeadingSmall
        title="Password Settings"
        description="Update your password to keep your account secure"
      />

      <!-- Security Notice -->
      <Alert>
        <Shield class="h-4 w-4" />
        <AlertDescription>
          Use a strong password with at least 8 characters, including uppercase and lowercase letters, numbers, and special characters.
        </AlertDescription>
      </Alert>

      <!-- Password Update Form -->
      <form @submit.prevent="updatePassword">
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <Lock class="h-5 w-5" />
              Change Password
            </CardTitle>
            <CardDescription>
              Enter your current password and choose a new secure password
            </CardDescription>
          </CardHeader>
          <CardContent class="space-y-6">
            <!-- Current Password -->
            <div class="space-y-2">
              <Label for="current_password">Current Password</Label>
              <div class="relative">
                <Lock class="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
                <Input
                  id="current_password"
                  v-model="form.current_password"
                  :type="showCurrentPassword ? 'text' : 'password'"
                  class="pl-10 pr-10"
                  placeholder="Enter your current password"
                  required
                />
                <Button
                  type="button"
                  variant="ghost"
                  size="sm"
                  class="absolute right-1 top-1 h-7 w-7 p-0"
                  @click="showCurrentPassword = !showCurrentPassword"
                >
                  <Eye v-if="showCurrentPassword" class="h-4 w-4" />
                  <EyeOff v-else class="h-4 w-4" />
                  <span class="sr-only">Toggle password visibility</span>
                </Button>
              </div>
              <InputError :message="form.errors.current_password" />
            </div>

            <!-- New Password -->
            <div class="space-y-2">
              <Label for="password">New Password</Label>
              <div class="relative">
                <Lock class="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
                <Input
                  id="password"
                  v-model="form.password"
                  :type="showNewPassword ? 'text' : 'password'"
                  class="pl-10 pr-10"
                  placeholder="Enter your new password"
                  required
                />
                <Button
                  type="button"
                  variant="ghost"
                  size="sm"
                  class="absolute right-1 top-1 h-7 w-7 p-0"
                  @click="showNewPassword = !showNewPassword"
                >
                  <Eye v-if="showNewPassword" class="h-4 w-4" />
                  <EyeOff v-else class="h-4 w-4" />
                  <span class="sr-only">Toggle password visibility</span>
                </Button>
              </div>

              <!-- Password Strength Indicator -->
              <div v-if="form.password" class="space-y-2">
                <div class="flex items-center gap-2">
                  <span class="text-sm text-muted-foreground">Strength:</span>
                  <span class="text-sm font-medium" :class="passwordStrength.color">
                    {{ passwordStrength.label }}
                  </span>
                </div>

                <!-- Password Requirements -->
                <div class="space-y-1 text-xs">
                  <div class="flex items-center gap-2" :class="passwordStrength.checks.length ? 'text-green-600' : 'text-muted-foreground'">
                    <CheckCircle2 v-if="passwordStrength.checks.length" class="h-3 w-3" />
                    <AlertCircle v-else class="h-3 w-3" />
                    <span>At least 8 characters</span>
                  </div>
                  <div class="flex items-center gap-2" :class="passwordStrength.checks.uppercase ? 'text-green-600' : 'text-muted-foreground'">
                    <CheckCircle2 v-if="passwordStrength.checks.uppercase" class="h-3 w-3" />
                    <AlertCircle v-else class="h-3 w-3" />
                    <span>Uppercase letter</span>
                  </div>
                  <div class="flex items-center gap-2" :class="passwordStrength.checks.lowercase ? 'text-green-600' : 'text-muted-foreground'">
                    <CheckCircle2 v-if="passwordStrength.checks.lowercase" class="h-3 w-3" />
                    <AlertCircle v-else class="h-3 w-3" />
                    <span>Lowercase letter</span>
                  </div>
                  <div class="flex items-center gap-2" :class="passwordStrength.checks.numbers ? 'text-green-600' : 'text-muted-foreground'">
                    <CheckCircle2 v-if="passwordStrength.checks.numbers" class="h-3 w-3" />
                    <AlertCircle v-else class="h-3 w-3" />
                    <span>Number</span>
                  </div>
                  <div class="flex items-center gap-2" :class="passwordStrength.checks.special ? 'text-green-600' : 'text-muted-foreground'">
                    <CheckCircle2 v-if="passwordStrength.checks.special" class="h-3 w-3" />
                    <AlertCircle v-else class="h-3 w-3" />
                    <span>Special character</span>
                  </div>
                </div>
              </div>

              <InputError :message="form.errors.password" />
            </div>

            <!-- Confirm Password -->
            <div class="space-y-2">
              <Label for="password_confirmation">Confirm New Password</Label>
              <div class="relative">
                <Lock class="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
                <Input
                  id="password_confirmation"
                  v-model="form.password_confirmation"
                  :type="showConfirmPassword ? 'text' : 'password'"
                  class="pl-10 pr-10"
                  placeholder="Confirm your new password"
                  required
                />
                <Button
                  type="button"
                  variant="ghost"
                  size="sm"
                  class="absolute right-1 top-1 h-7 w-7 p-0"
                  @click="showConfirmPassword = !showConfirmPassword"
                >
                  <Eye v-if="showConfirmPassword" class="h-4 w-4" />
                  <EyeOff v-else class="h-4 w-4" />
                  <span class="sr-only">Toggle password visibility</span>
                </Button>
              </div>
              <InputError :message="form.errors.password_confirmation" />
            </div>
          </CardContent>
        </Card>

        <!-- Action Buttons -->
        <div class="flex justify-end gap-2 mt-6">
          <Button
            type="submit"
            :disabled="form.processing"
          >
            {{ form.processing ? 'Updating...' : 'Update Password' }}
          </Button>
        </div>
      </form>

      <!-- Security Tips -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Shield class="h-5 w-5" />
            Security Tips
          </CardTitle>
          <CardDescription>
            Best practices for keeping your account secure
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="space-y-4">
            <div class="flex items-start gap-3">
              <CheckCircle2 class="h-5 w-5 text-green-500 mt-0.5 flex-shrink-0" />
              <div>
                <div class="font-medium">Use a unique password</div>
                <div class="text-sm text-muted-foreground">
                  Don't reuse passwords from other accounts or services
                </div>
              </div>
            </div>

            <div class="flex items-start gap-3">
              <CheckCircle2 class="h-5 w-5 text-green-500 mt-0.5 flex-shrink-0" />
              <div>
                <div class="font-medium">Enable two-factor authentication</div>
                <div class="text-sm text-muted-foreground">
                  Add an extra layer of security to your account (coming soon)
                </div>
              </div>
            </div>

            <div class="flex items-start gap-3">
              <CheckCircle2 class="h-5 w-5 text-green-500 mt-0.5 flex-shrink-0" />
              <div>
                <div class="font-medium">Keep your password private</div>
                <div class="text-sm text-muted-foreground">
                  Never share your password with others or write it down
                </div>
              </div>
            </div>

            <div class="flex items-start gap-3">
              <CheckCircle2 class="h-5 w-5 text-green-500 mt-0.5 flex-shrink-0" />
              <div>
                <div class="font-medium">Update regularly</div>
                <div class="text-sm text-muted-foreground">
                  Change your password periodically for better security
                </div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </SettingsLayout>
</template>
