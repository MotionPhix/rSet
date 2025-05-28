<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { usePage } from '@inertiajs/vue3';
import type { BreadcrumbItem, LeaveRequest } from '@/types';

const { props } = usePage<{ leaveRequests: LeaveRequest[] }>();

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Leave Requests',
    href: '/admin/leave',
  },
];
</script>

<template>

  <div class="container mx-auto py-8">
    <h1 class="text-2xl font-bold text-foreground mb-6">Leave Requests</h1>

    <Table class="border rounded-lg bg-background">
      <TableHeader>
        <TableRow>
          <TableHead>Employee</TableHead>
          <TableHead>Type</TableHead>
          <TableHead>Dates</TableHead>
          <TableHead>Status</TableHead>
          <TableHead>Actions</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <TableRow v-for="request in props.leaveRequests"
                  :key="request.id">
          <TableCell>{{ request.user.name }}</TableCell>
          <TableCell>
            <Badge variant="outline">{{ request.type }}</Badge>
          </TableCell>
          <TableCell>
            {{ request.start_date }} to {{ request.end_date }}
          </TableCell>
          <TableCell>
            <Badge :variant="request.status === 'approved' ? 'default' :
              request.status === 'rejected' ? 'destructive' : 'secondary'">
              {{ request.status }}
            </Badge>
          </TableCell>
          <TableCell>
            <Button v-if="request.status === 'pending'"
                    size="sm"
                    @click="router.patch(route('admin.leave.update', { id: request.id }), { status: 'approved' })">
              Approve
            </Button>
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>
  </div>
</template>
