import type { PageProps } from '@inertiajs/core';
import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export interface Auth {
  user: User | null;
  company: Company | null;
  abilities: object;
}

export interface BreadcrumbItem {
  title: string;
  href: string;
}

export interface NavItem {
  title: string;
  href: string;
  icon?: LucideIcon;
  isActive?: boolean;
}

export interface SharedData extends PageProps {
  name: string;
  quote: { message: string; author: string };
  auth: Auth;
  ziggy: Config & { location: string };
  sidebarOpen: boolean;
}

export interface User {
  id: number;
  name: string;
  email: string;
  avatar?: string;
  company_id: number | null;
  team_id: number | null;
  roles: Role[];
  permissions: string[];
  team: Team | null;
}

export interface Company {
  id: number;
  name: string;
  slug: string;
  email: string | null;
  phone: string | null;
  address: string | null;
  website: string | null;
  timezone: string;
  currency: string;
  max_employees: number;
  subscription_plan: string | null;
  subscription_expires_at: string | null;
  is_active: boolean;
}

export interface Role {
  id: number;
  name: string;
  permissions: string[];
}

export interface Permission {
  id: number;
  name: string;
  guard_name: string;
  created_at: string;
  updated_at: string;
}

export interface Team {
  id: number;
  name: string;
  manager_id: number | null;
}

export interface LeaveRequest {
  id: number;
  uuid: string;
  user_id: number;
  company_id: number;
  approver_id: number | null;
  start_date: string;
  end_date: string;
  type: string;
  reason: string;
  status: 'pending' | 'approved' | 'rejected' | 'cancelled';
  created_at: string;
  updated_at: string;
  user?: User;
  approver?: User;
}

export interface LeaveType {
  id: number;
  uuid: string;
  name: string;
  description: string | null;
  company_id: number;
  days_allowed: number;
  min_duration: number;
  max_duration: number;
  allow_custom_duration: boolean;
  gender: string | null;
  min_employment_months: number | null;
  cooldown_days: number | null;
  max_usage_per_year: number | null;
  full_pay_days: number;
  half_pay_days: number;
  requires_approval: boolean;
  approvers: string[] | null;
  requires_documentation: boolean;
  documentation_type: string | null;
  created_at: string;
  updated_at: string;
}

export type BreadcrumbItemType = BreadcrumbItem;
