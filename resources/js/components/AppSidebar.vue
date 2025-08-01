<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarHeader,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem
} from '@/components/ui/sidebar';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import {
  CalendarIcon,
  FileCheckIcon,
  HomeIcon,
  LayoutGrid,
  BarChart3,
  Building2,
  Settings,
  Users,
  UserCog,
  Shield,
  Database,
  Calendar
} from 'lucide-vue-next';
import { computed } from 'vue';
import {Separator} from '@/components/ui/separator';

const page = usePage<SharedData>();

// Get user roles and abilities for navigation filtering
const userRoles = computed(() => page.props.auth.user?.roles?.map(role => role.name) || []);
const userAbilities = computed(() => page.props.auth.abilities || {});

// Role-based checks
const isAdmin = computed(() => userRoles.value.includes('admin'));
const isHR = computed(() => userRoles.value.includes('hr'));
const isManager = computed(() => userRoles.value.includes('manager'));
const isSuperAdmin = computed(() => userAbilities.value.manageSystemSettings);
const isEmployee = computed(() => userRoles.value.includes('employee'));

// Main navigation items (ability-aware)
const mainNavItems = computed((): NavItem[] => {
  const items: NavItem[] = [];

  // Dashboard - role-based routing
  items.push({
    title: 'Dashboard',
    href: (isAdmin.value || isHR.value) ? route('admin.dashboard') : route('dashboard'),
    icon: HomeIcon
  });

  // Leave Requests - available to all users who can create leave requests
  if (userAbilities.value.createLeaveRequest || userAbilities.value.viewOwnLeaveRequest) {
    items.push({
      title: 'Leave Requests',
      href: route('leave-requests.index'),
      icon: CalendarIcon
    });
  }

  /*// Calendar - available to all authenticated users
  items.push({
    title: 'Calendar',
    href: route('calendar.index'),
    icon: Calendar
  });*/

  // Team Leave Requests - only for managers
  if (isManager.value && userAbilities.value.viewTeamLeaveRequests) {
    items.push({
      title: 'Team Requests',
      href: route('team.leave-requests.index'),
      icon: Users
    });
  }

  // Admin Leave Management - for admins/HR
  if (userAbilities.value.viewAllLeaveRequests) {
    items.push({
      title: 'All Leave Requests',
      href: route('admin.leave-requests.index'),
      icon: FileCheckIcon
    });
  }

  // Reports - role-based routing
  if (userAbilities.value.viewReports) {
    items.push({
      title: 'Reports',
      href: (isAdmin.value || isHR.value) ? route('admin.reports.index') : route('employee.reports.index'),
      icon: BarChart3
    });
  } else {
    // Personal reports for employees who can't access admin reports
    items.push({
      title: 'My Reports',
      href: route('employee.reports.index'),
      icon: BarChart3
    });
  }

  // Analytics - admin only
  if (userAbilities.value.viewAnalytics) {
    items.push({
      title: 'Analytics',
      href: route('admin.analytics.index'),
      icon: LayoutGrid
    });
  }

  return items;
});

// Management navigation items (for admins/HR)
const managementNavItems = computed((): NavItem[] => {
  if (!isAdmin.value && !isHR.value && !isSuperAdmin.value) return [];

  const items: NavItem[] = [];

  // Company Management
  if (userAbilities.value.viewCompanyProfile) {
    items.push({
      title: 'Company',
      href: route('admin.settings.company'),
      icon: Building2
    });
  }

  // User Management
  if (userAbilities.value.viewUsers) {
    items.push({
      title: 'Users',
      href: route('admin.settings.users'),
      icon: UserCog
    });
  }

  // Team Management
  if (userAbilities.value.viewTeams) {
    items.push({
      title: 'Teams',
      href: route('admin.settings.teams'),
      icon: Users
    });
  }

  // Leave Types
  if (userAbilities.value.viewLeaveTypes) {
    items.push({
      title: 'Leave Types',
      href: route('admin.settings.leave-types'),
      icon: FileCheckIcon
    });
  }

  // Roles & Permissions
  if (userAbilities.value.assignRoles) {
    items.push({
      title: 'Roles & Permissions',
      href: route('admin.settings.roles'),
      icon: Shield
    });
  }

  return items;
});

// System navigation items (for super admin)
const systemNavItems = computed((): NavItem[] => {
  if (!isSuperAdmin.value) return [];

  const items: NavItem[] = [];

  items.push({
    title: 'Dashboard',
    href: route('super-admin.dashboard'),
    icon: LayoutGrid
  });

  if (userAbilities.value.manageAllCompanies) {
    items.push({
      title: 'Companies',
      href: route('super-admin.companies.index'),
      icon: Building2
    });
  }

  items.push({
    title: 'Subscriptions',
    href: route('super-admin.subscriptions.index'),
    icon: Database
  });

  items.push({
    title: 'Analytics',
    href: route('super-admin.analytics.index'),
    icon: BarChart3
  });

  items.push({
    title: 'System Settings',
    href: route('super-admin.system.settings'),
    icon: Settings
  });

  if (userAbilities.value.viewSystemLogs) {
    items.push({
      title: 'System Logs',
      href: route('super-admin.system.logs'),
      icon: FileCheckIcon
    });
  }

  return items;
});

// Footer navigation items
const footerNavItems = computed((): NavItem[] => [
  {
    title: 'Calendar',
    href: isAdmin.value 
      ? route('admin.calendar.index') // Admin calendar route
      : route('calendar.index'), // Regular employees calendar route
    icon: CalendarIcon
  }
]);

// Combine all nav items for the main navigation
const allNavItems = computed(() => [
  ...mainNavItems.value,
  ...(managementNavItems.value.length > 0 ? [{ title: '---' }] : []),
  ...managementNavItems.value,
  ...(systemNavItems.value.length > 0 ? [{ title: '---' }] : []),
  ...systemNavItems.value,
]);
</script>

<template>
  <Sidebar>
    <SidebarHeader>
      <SidebarMenu>
        <SidebarMenuItem>
          <SidebarMenuButton size="lg" as-child>
            <Link :href="(isAdmin || isHR) ? route('admin.dashboard') : route('dashboard')">
              <div class="flex aspect-square size-8 items-center justify-center rounded-lg bg-sidebar-primary text-sidebar-primary-foreground">
                <Building2 class="size-4" />
              </div>
              <div class="grid flex-1 text-left text-sm leading-tight">
                <span class="truncate font-semibold">
                  {{ $page.props.auth.company?.name || 'LeaveHub' }}
                </span>
                <span class="truncate text-xs">
                  {{ isSuperAdmin ? 'Super Admin' : isAdmin ? 'Admin' : isHR ? 'HR' : isManager ? 'Manager' : 'Employee' }}
                </span>
              </div>
            </Link>
          </SidebarMenuButton>
        </SidebarMenuItem>
      </SidebarMenu>
    </SidebarHeader>

    <Separator class="my-2" />

    <SidebarContent>
      <NavMain :items="allNavItems" />
    </SidebarContent>

    <SidebarFooter>
      <NavFooter :items="footerNavItems" />
      <Separator />
      <NavUser />
    </SidebarFooter>
  </Sidebar>
</template>
