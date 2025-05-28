<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Form, FormField, FormItem, FormLabel, FormControl, FormMessage } from '@/components/ui/form';
import { Select, SelectTrigger, SelectValue, SelectContent, SelectItem } from '@/components/ui/select';
import { Calendar } from '@/components/ui/calendar';
import { Input } from '@/components/ui/input';
import { toTypedSchema } from '@vee-validate/zod';
import * as z from 'zod';

const form = useForm({
  leave_type_id: '',
  start_date: new Date(),
  end_date: new Date(),
  reason: '',
});

const formSchema = toTypedSchema(
  z.object({
    leave_type_id: z.string().min(1, 'Leave type is required'),
    start_date: z.date().min(new Date(), 'Start date must be in the future'),
    end_date: z.date().min(form.start_date, 'End date must be after start date'),
    reason: z.string().max(500).optional(),
  })
);

const submit = () => {
  form.post(route('employee.leave.store'));
};
</script>

<template>

  <Head title="Request Leave" />
  <AppLayout>
    <div class="max-w-2xl mx-auto">
      <h1 class="text-2xl font-bold mb-6">New Leave Request</h1>

      <Card>
        <CardContent class="pt-6">
          <Form :validation-schema="formSchema"
                @submit="submit">
            <div class="space-y-4">
              <FormField v-slot="{ componentField }"
                         name="leave_type_id">
                <FormItem>
                  <FormLabel>Leave Type</FormLabel>
                  <Select v-bind="componentField">
                    <FormControl>
                      <SelectTrigger>
                        <SelectValue placeholder="Select leave type" />
                      </SelectTrigger>
                    </FormControl>
                    <SelectContent>
                      <SelectItem v-for="type in $page.props.leaveTypes"
                                  :key="type.id"
                                  :value="type.id">
                        {{ type.name }} ({{ type.days_allowed }} days)
                      </SelectItem>
                    </SelectContent>
                  </Select>
                  <FormMessage />
                </FormItem>
              </FormField>

              <FormField v-slot="{ field }"
                         name="start_date">
                <FormItem>
                  <FormLabel>Start Date</FormLabel>
                  <FormControl>
                    <Calendar v-model="field.value" />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              </FormField>

              <FormField v-slot="{ field }"
                         name="end_date">
                <FormItem>
                  <FormLabel>End Date</FormLabel>
                  <FormControl>
                    <Calendar v-model="field.value" />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              </FormField>

              <FormField v-slot="{ field }"
                         name="reason">
                <FormItem>
                  <FormLabel>Reason (Optional)</FormLabel>
                  <FormControl>
                    <Input type="text"
                           v-bind="field" />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              </FormField>

              <Button type="submit"
                      :disabled="form.processing">
                Submit Request
              </Button>
            </div>
          </Form>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
