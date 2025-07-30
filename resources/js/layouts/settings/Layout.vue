<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { Badge } from '@/components/ui/badge';
import { ScrollArea } from '@/components/ui/scroll-area';
import {
  Sheet,
  SheetContent,
  SheetDescription,
  SheetHeader,
  SheetTitle,
  SheetTrigger,
} from '@/components/ui/sheet';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
  User,
  Lock,
  Palette,
  Building2,
  Users,
  UserCog,
  Shield,
  FileText,
  Settings,
  Menu,
  ChevronRight,
  BarChart3,
  Database,
  Globe
} from 'lucide-vue-next';

const page = usePage<SharedData>();
const userAbilities = computed(() => page.props.auth.abilities || {});
const userRoles = computed(() => page.props.auth.user?.roles?.map(role => role.name) || []);

// Determine user context and appropriate routes
const isAdmin = computed(() => userRoles.value.includes('admin') || userRoles.value.includes('hr'));
const isSuperAdmin = computed(() => userAbilities.value.manageSystemSettings);
const baseSettingsRoute = computed(() => {
  if (isSuperAdmin.value) return 'settings';
  if (isAdmin.value) return 'admin.settings';
  return 'employee.settings';
});

// Personal settings items (always visible)
const personalNavItems = computed(() => [
  {
    title: 'Profile',
    href: isSuperAdmin.value ? route('settings.profile') :
          isAdmin.value ? route('settings.profile') :
          route('employee.settings.profile'),
    icon: User,
  },
  {
    title: 'Password',
    href: isSuperAdmin.value ? route('settings.password') :
          isAdmin.value ? route('settings.password') :
          route('employee.settings.password'),
    icon: Lock,
  },
  {
    title: 'Appearance',
    href: isSuperAdmin.value ? route('settings.appearance') :
          isAdmin.value ? route('settings.appearance') :
          route('employee.settings.appearance'),
    icon: Palette,
  },
]);

// Organization settings (visible to admins only)
const organizationNavItems = computed(() => {
  if (!isAdmin.value && !isSuperAdmin.value) return [];

  const items: NavItem[] = [];

  if (userAbilities.value.viewCompanyProfile) {
    items.push({
      title: 'Company Profile',
      href: route('admin.settings.company'),
      icon: Building2,
    });
  }

  if (userAbilities.value.viewTeams) {
    items.push({
      title: 'Teams',
      href: route('admin.settings.teams'),
      icon: Users,
    });
  }

  if (userAbilities.value.viewLeaveTypes) {
    items.push({
      title: 'Leave Types',
      href: route('admin.settings.leave-types'),
      icon: FileText,
    });
  }

  return items;
});

// User management settings (visible to admins only)
const userManagementNavItems = computed(() => {
  if (!isAdmin.value && !isSuperAdmin.value) return [];

  const items: NavItem[] = [];

  if (userAbilities.value.viewUsers) {
    items.push({
      title: 'Users',
      href: route('admin.settings.users'),
      icon: UserCog,
    });
  }

  if (userAbilities.value.assignRoles) {
    items.push({
      title: 'Roles & Permissions',
      href: route('admin.settings.roles'),
      icon: Shield,
    });
  }

  return items;
});

// System settings (visible to super admin only)
const systemNavItems = computed(() => {
  if (!isSuperAdmin.value) return [];

  const items: NavItem[] = [];

  items.push({
    title: 'System Settings',
    href: route('settings.system'),
    icon: Settings,
  });

  if (userAbilities.value.manageAllCompanies) {
    items.push({
      title: 'Companies',
      href: route('settings.companies'),
      icon: Database,
    });
  }

  if (userAbilities.value.viewSystemLogs) {
    items.push({
      title: 'System Logs',
      href: route('settings.logs'),
      icon: BarChart3,
    });
  }

  return items;
});

// All nav items for mobile view
const allNavItems = computed(() => [
  ...personalNavItems.value,
  ...organizationNavItems.value,
  ...userManagementNavItems.value,
  ...systemNavItems.value,
]);

const currentPath = page.props.ziggy?.location ? new URL(page.props.ziggy.location).pathname : '';

// Get current section for breadcrumb with improved logic
const currentSection = computed(() => {
  if (currentPath.includes('/admin/settings') || currentPath.includes('/settings/company') || currentPath.includes('/settings/teams') || currentPath.includes('/settings/leave-types')) {
    return 'Organization';
  } else if (currentPath.includes('/settings/users') || currentPath.includes('/settings/roles')) {
    return 'User Management';
  } else if (currentPath.includes('/settings/system') || currentPath.includes('/settings/companies') || currentPath.includes('/settings/logs')) {
    return 'System';
  }
  return 'Personal';
});

// Check if user has any management permissions
const hasManagementAccess = computed(() =>
  organizationNavItems.value.length > 0 ||
  userManagementNavItems.value.length > 0 ||
  systemNavItems.value.length > 0
);

// Get user's primary role for display
const primaryRole = computed(() => {
  const roles = userRoles.value;
  if (roles.includes('super-admin')) return 'Super Admin';
  if (roles.includes('admin')) return 'Admin';
  if (roles.includes('hr')) return 'HR';
  if (roles.includes('manager')) return 'Manager';
  return 'Employee';
});

// Get appropriate description based on user role
const settingsDescription = computed(() => {
  if (isSuperAdmin.value) return 'Manage system-wide settings, companies, and configurations';
  if (isAdmin.value) return 'Manage your company settings, users, and team configurations';
  return 'Manage your personal profile and preferences';
});
</script>

<template>
  <div class="min-h-screen bg-background">
    <div class="container mx-auto px-4 py-6 lg:px-6">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <Heading title="Settings" :description="settingsDescription" />
            <div class="mt-2 flex items-center gap-2">
              <Badge variant="secondary" class="text-xs">
                {{ currentSection }}
              </Badge>
              <Badge
                :variant="isSuperAdmin ? 'destructive' : isAdmin ? 'default' : 'outline'"
                class="text-xs"
              >
                {{ primaryRole }}
              </Badge>
            </div>
          </div>

          <!-- Mobile menu trigger -->
          <div class="lg:hidden">
            <Sheet>
              <SheetTrigger as-child>
                <Button variant="outline" size="sm">
                  <Menu class="h-4 w-4" />
                  <span class="sr-only">Open settings menu</span>
                </Button>
              </SheetTrigger>
              <SheetContent side="left" class="w-80">
                <SheetHeader>
                  <SheetTitle>Settings</SheetTitle>
                  <SheetDescription>
                    Navigate through your settings
                  </SheetDescription>
                </SheetHeader>
                <ScrollArea class="h-[calc(100vh-8rem)] mt-6">
                  <nav class="space-y-1">
                    <Link
                      v-for="item in allNavItems"
                      :key="item.href"
                      :href="item.href"
                      class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground"
                      :class="{
                        'bg-accent text-accent-foreground': currentPath === item.href || currentPath.startsWith(item.href)
                      }"
                    >
                      <component :is="item.icon" class="h-4 w-4" />
                      {{ item.title }}
                    </Link>
                  </nav>
                </ScrollArea>
              </SheetContent>
            </Sheet>
          </div>
        </div>
      </div>

      <!-- Main content -->
      <div class="flex flex-col gap-6 lg:flex-row lg:gap-8">
        <!-- Desktop sidebar -->
        <aside class="hidden w-64 flex-shrink-0 lg:block">
          <div class="sticky top-6 space-y-6">
            <!-- Personal Settings -->
            <div>
              <h3 class="mb-3 text-sm font-semibold text-muted-foreground">Personal</h3>
              <nav class="space-y-1">
                <Link
                  v-for="item in personalNavItems"
                  :key="item.href"
                  :href="item.href"
                  class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground"
                  :class="{
                    'bg-accent text-accent-foreground': currentPath === item.href || currentPath.startsWith(item.href)
                  }"
                >
                  <component :is="item.icon" class="h-4 w-4" />
                  {{ item.title }}
                </Link>
              </nav>
            </div>

            <!-- Organization Settings -->
            <div v-if="organizationNavItems.length > 0">
              <Separator class="mb-3" />
              <h3 class="mb-3 text-sm font-semibold text-muted-foreground">Organization</h3>
              <nav class="space-y-1">
                <Link
                  v-for="item in organizationNavItems"
                  :key="item.href"
                  :href="item.href"
                  class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground"
                  :class="{
                    'bg-accent text-accent-foreground': currentPath === item.href || currentPath.startsWith(item.href)
                  }"
                >
                  <component :is="item.icon" class="h-4 w-4" />
                  {{ item.title }}
                </Link>
              </nav>
            </div>

            <!-- User Management -->
            <div v-if="userManagementNavItems.length > 0">
              <Separator class="mb-3" />
              <h3 class="mb-3 text-sm font-semibold text-muted-foreground">User Management</h3>
              <nav class="space-y-1">
                <Link
                  v-for="item in userManagementNavItems"
                  :key="item.href"
                  :href="item.href"
                  class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground"
                  :class="{
                    'bg-accent text-accent-foreground': currentPath === item.href || currentPath.startsWith(item.href)
                  }"
                >
                  <component :is="item.icon" class="h-4 w-4" />
                  {{ item.title }}
                </Link>
              </nav>
            </div>

            <!-- System Settings -->
            <div v-if="systemNavItems.length > 0">
              <Separator class="mb-3" />
              <h3 class="mb-3 text-sm font-semibold text-muted-foreground">System</h3>
              <nav class="space-y-1">
                <Link
                  v-for="item in systemNavItems"
                  :key="item.href"
                  :href="item.href"
                  class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground"
                  :class="{
                    'bg-accent text-accent-foreground': currentPath === item.href || currentPath.startsWith(item.href)
                  }"
                >
                  <component :is="item.icon" class="h-4 w-4" />
                  {{ item.title }}
                </Link>
              </nav>
            </div>
          </div>
        </aside>

        <!-- Main content area -->
        <main class="flex-1 overflow-hidden">
          <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <slot />
          </div>
        </main>
      </div>
    </div>
  </div>
</template>
