<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Head, useForm } from '@inertiajs/vue3';
import { Building2Icon, GlobeIcon, MailIcon, PhoneIcon } from 'lucide-vue-next';

const form = useForm({
    name: '',
    email: '',
    phone: '',
    address: '',
    website: '',
    timezone: 'UTC',
    currency: 'USD',
});

const timezones = [
    { value: 'UTC', label: 'UTC' },
    { value: 'America/New_York', label: 'Eastern Time (ET)' },
    { value: 'America/Chicago', label: 'Central Time (CT)' },
    { value: 'America/Denver', label: 'Mountain Time (MT)' },
    { value: 'America/Los_Angeles', label: 'Pacific Time (PT)' },
    { value: 'Europe/London', label: 'London (GMT)' },
    { value: 'Europe/Paris', label: 'Paris (CET)' },
    { value: 'Asia/Tokyo', label: 'Tokyo (JST)' },
    { value: 'Asia/Shanghai', label: 'Shanghai (CST)' },
    { value: 'Australia/Sydney', label: 'Sydney (AEST)' },
];

const currencies = [
    { value: 'USD', label: 'US Dollar (USD)' },
    { value: 'EUR', label: 'Euro (EUR)' },
    { value: 'GBP', label: 'British Pound (GBP)' },
    { value: 'CAD', label: 'Canadian Dollar (CAD)' },
    { value: 'AUD', label: 'Australian Dollar (AUD)' },
    { value: 'JPY', label: 'Japanese Yen (JPY)' },
    { value: 'CNY', label: 'Chinese Yuan (CNY)' },
];

const submit = () => {
    form.post(route('company.store'));
};
</script>

<template>
    <Head title="Company Setup" />

    <div class="min-h-screen bg-background flex items-center justify-center p-4">
        <Card class="w-full max-w-2xl">
            <CardHeader class="text-center">
                <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary text-primary-foreground">
                    <Building2Icon class="h-6 w-6" />
                </div>
                <CardTitle class="text-2xl">Welcome to LeaveHub!</CardTitle>
                <CardDescription>
                    Let's set up your company profile to get started with managing leave requests.
                </CardDescription>
            </CardHeader>

            <CardContent>
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Company Name -->
                    <div class="space-y-2">
                        <Label for="name">Company Name *</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            type="text"
                            placeholder="Enter your company name"
                            required
                            :class="{ 'border-destructive': form.errors.name }"
                        />
                        <p v-if="form.errors.name" class="text-sm text-destructive">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- Contact Information -->
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="email">Company Email</Label>
                            <div class="relative">
                                <MailIcon class="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                                <Input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    placeholder="company@example.com"
                                    class="pl-10"
                                    :class="{ 'border-destructive': form.errors.email }"
                                />
                            </div>
                            <p v-if="form.errors.email" class="text-sm text-destructive">
                                {{ form.errors.email }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="phone">Phone Number</Label>
                            <div class="relative">
                                <PhoneIcon class="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                                <Input
                                    id="phone"
                                    v-model="form.phone"
                                    type="tel"
                                    placeholder="+1 (555) 123-4567"
                                    class="pl-10"
                                    :class="{ 'border-destructive': form.errors.phone }"
                                />
                            </div>
                            <p v-if="form.errors.phone" class="text-sm text-destructive">
                                {{ form.errors.phone }}
                            </p>
                        </div>
                    </div>

                    <!-- Website -->
                    <div class="space-y-2">
                        <Label for="website">Website</Label>
                        <div class="relative">
                            <GlobeIcon class="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                            <Input
                                id="website"
                                v-model="form.website"
                                type="url"
                                placeholder="https://www.example.com"
                                class="pl-10"
                                :class="{ 'border-destructive': form.errors.website }"
                            />
                        </div>
                        <p v-if="form.errors.website" class="text-sm text-destructive">
                            {{ form.errors.website }}
                        </p>
                    </div>

                    <!-- Address -->
                    <div class="space-y-2">
                        <Label for="address">Address</Label>
                        <Textarea
                            id="address"
                            v-model="form.address"
                            placeholder="Enter your company address"
                            rows="3"
                            :class="{ 'border-destructive': form.errors.address }"
                        />
                        <p v-if="form.errors.address" class="text-sm text-destructive">
                            {{ form.errors.address }}
                        </p>
                    </div>

                    <!-- Timezone and Currency -->
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="timezone">Timezone</Label>
                            <Select v-model="form.timezone">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select timezone" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="timezone in timezones"
                                        :key="timezone.value"
                                        :value="timezone.value"
                                    >
                                        {{ timezone.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.timezone" class="text-sm text-destructive">
                                {{ form.errors.timezone }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="currency">Currency</Label>
                            <Select v-model="form.currency">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select currency" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="currency in currencies"
                                        :key="currency.value"
                                        :value="currency.value"
                                    >
                                        {{ currency.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.currency" class="text-sm text-destructive">
                                {{ form.errors.currency }}
                            </p>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end space-x-4">
                        <Button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full md:w-auto"
                        >
                            {{ form.processing ? 'Creating Company...' : 'Create Company' }}
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
