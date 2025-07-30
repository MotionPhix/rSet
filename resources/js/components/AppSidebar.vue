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
  UsersIcon,
  BarChart3,
  Building2,
  Settings,
  HelpCircle,
} from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage<SharedData>();

console.log('AppSidebar mounted', page.props.auth.user?.roles);

// Get user roles for navigation filtering
const userRoles = computed(() => page.props.auth.user?.roles?.map(role => role.name) || []);
const isAdmin = computed(() => userRoles.value.includes('admin'));
const isHR = computed(() => userRoles.value.includes('hr'));
const isManager = computed(() => userRoles.value.includes('manager'));
const isSuperAdmin = computed(() => userRoles.value.includes('super-admin'));

// Main navigation items (role-aware)
const mainNavItems = computed((): NavItem[] => [
  {
    title: 'Dashboard',
    href: isAdmin.value ? route('admin.dashboard') : route('dashboard'),
    icon: HomeIcon
  },
  {
    title: 'Leave Requests',
    href: route('leave-requests.index'),
    icon: CalendarIcon
  },
  {
    title: 'Leave Types',
    href: route('leave-types.index'),
    icon: FileCheckIcon
  }
]);

// Admin/Management navigation items
const adminNavItems = computed((): NavItem[] => {
  const items: NavItem[] = [];

  if (isAdmin.value || isHR.value) {
    items.push(
      {
        title: 'User Management',
        href: route('admin.users.index'),
        icon: UsersIcon
      },
      {
        title: 'Team Management',
        href: route('admin.teams.index'),
        icon: Building2
      },
      {
        title: 'Leave Types Admin',
        href: route('admin.leave-types.index'),
        icon: FileCheckIcon
      },
      {
        title: 'Reports',
        href: route('admin.reports.index'),
        icon: BarChart3
      }
    );
  }

  if (isAdmin.value) {
    items.push(
      {
        title: 'Company Profile',
        href: route('admin.company.profile'),
        icon: Building2
      },
      {
        title: 'Company Employees',
        href: route('admin.company.employees'),
        icon: UsersIcon
      },
      {
        title: 'Analytics',
        href: route('admin.analytics.index'),
        icon: LayoutGrid
      }
    );
  }

  return items;
});

// Footer navigation items (useful system links)
const footerNavItems = computed((): NavItem[] => [
  {
    title: 'Profile Settings',
    href: route('profile.edit'),
    icon: Settings
  },
  {
    title: 'Help & Support',
    href: '#', // This could link to a help page or documentation
    icon: HelpCircle
  }
]);

// Dashboard link for header (role-aware)
const dashboardRoute = computed(() =>
  isAdmin.value ? route('admin.dashboard') : route('dashboard')
);
</script>

<template>
  <Sidebar collapsible="icon" variant="inset">
    <SidebarHeader>
      <SidebarMenu>
        <SidebarMenuItem>
          <SidebarMenuButton size="lg" as-child>
            <Link :href="dashboardRoute">
              <div class="grid flex-1 text-left text-sm leading-tight">
                <span class="truncate font-semibold">
                  {{ page.props.auth.company?.name || 'LeaveHub' }}
                </span>

                <span class="truncate text-xs">
                  {{ page.props.auth.user?.name }}
                </span>
              </div>
            </Link>
          </SidebarMenuButton>
        </SidebarMenuItem>
      </SidebarMenu>
    </SidebarHeader>

    <SidebarContent>
      <!-- Main Navigation -->
      <NavMain :items="mainNavItems" />

      <!-- Admin/Management Navigation -->
      <NavMain
        v-if="adminNavItems.length > 0"
        :items="adminNavItems"
        title="Management"
      />

      <!-- Super Admin Navigation (if needed) -->
      <NavMain
        v-if="isSuperAdmin"
        :items="[
          {
            title: 'System Analytics',
            href: '#', // Could be a system-wide analytics page
            icon: BarChart3
          },
          {
            title: 'All Companies',
            href: '#', // Could be a page to manage all companies
            icon: Building2
          }
        ]"
        title="System Admin"
      />
    </SidebarContent>

    <SidebarFooter>
      <NavFooter :items="footerNavItems" />
      <NavUser />
    </SidebarFooter>
  </Sidebar>
  <slot />
</template>
