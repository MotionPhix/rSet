<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectTrigger, SelectValue, SelectContent, SelectItem } from '@/components/ui/select';
import { Calendar } from '@/components/ui/calendar';
import { useForm } from 'vee-validate';
import { toTypedSchema } from '@vee-validate/zod';
import * as z from 'zod';
import { router } from '@inertiajs/vue3';

const formSchema = toTypedSchema(
  z.object({
    type: z.enum(['vacation', 'sick', 'unpaid']),
    start_date: z.date(),
    end_date: z.date(),
    reason: z.string().optional(),
  })
);

const form = useForm({
  validationSchema: formSchema,
});

const onSubmit = form.handleSubmit((values) => {
  router.post(route('employee.leave.store'), values);
});
</script>

<template>
  <Card class="max-w-2xl mx-auto">
    <CardHeader>
      <CardTitle>Request Leave</CardTitle>
    </CardHeader>
    <CardContent>
      <Form @submit="onSubmit">
        <FormField v-slot="{ componentField }" name="type">
          <FormItem class="mb-4">
            <FormLabel>Leave Type</FormLabel>

            <Select v-bind="componentField">
              <FormControl>
                <SelectTrigger>
                  <SelectValue placeholder="Select leave type" />
                </SelectTrigger>
              </FormControl>

              <SelectContent>
                <SelectItem value="vacation">Vacation</SelectItem>
                <SelectItem value="sick">Sick Leave</SelectItem>
                <SelectItem value="unpaid">Unpaid Leave</SelectItem>
              </SelectContent>
            </Select>
            <FormMessage />
          </FormItem>
        </FormField>

        <FormField v-slot="{ field }" name="start_date">
          <FormItem class="mb-4">
            <FormLabel>Start Date</FormLabel>
            <FormControl>
              <Calendar v-model="field.value" />
            </FormControl>
            <FormMessage />
          </FormItem>
        </FormField>

        <Button type="submit" class="mt-4">Submit Request</Button>
      </Form>
    </CardContent>
  </Card>
</template>
