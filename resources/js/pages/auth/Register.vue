<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle, Building2Icon, UserIcon } from 'lucide-vue-next';

const form = useForm({
    // Personal Information
    name: '',
    email: '',
    password: '',
    password_confirmation: '',

    // Company Information
    company_name: '',
    company_email: '',
    company_phone: '',
    company_address: '',
    company_website: '',
    company_timezone: 'UTC',
    company_currency: 'USD',
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
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthBase title="Create your company account" description="Register as a company admin to start managing leave requests">
        <Head title="Register" />

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <!-- Personal Information Section -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <UserIcon class="h-5 w-5 text-muted-foreground" />
                        <h3 class="text-lg font-semibold">Personal Information</h3>
                    </div>

                    <div class="grid gap-4">
                        <div class="grid gap-2">
                            <Label for="name">Full Name *</Label>
                            <Input
                                id="name"
                                type="text"
                                required
                                autofocus
                                :tabindex="1"
                                autocomplete="name"
                                v-model="form.name"
                                placeholder="Your full name"
                            />
                            <InputError :message="form.errors.name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="email">Email Address *</Label>
                            <Input
                                id="email"
                                type="email"
                                required
                                :tabindex="2"
                                autocomplete="email"
                                v-model="form.email"
                                placeholder="your.email@example.com"
                            />
                            <InputError :message="form.errors.email" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="password">Password *</Label>
                            <Input
                                id="password"
                                type="password"
                                required
                                :tabindex="3"
                                autocomplete="new-password"
                                v-model="form.password"
                                placeholder="Create a strong password"
                            />
                            <InputError :message="form.errors.password" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="password_confirmation">Confirm Password *</Label>
                            <Input
                                id="password_confirmation"
                                type="password"
                                required
                                :tabindex="4"
                                autocomplete="new-password"
                                v-model="form.password_confirmation"
                                placeholder="Confirm your password"
                            />
                            <InputError :message="form.errors.password_confirmation" />
                        </div>
                    </div>
                </div>

                <Separator />

                <!-- Company Information Section -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <Building2Icon class="h-5 w-5 text-muted-foreground" />
                        <h3 class="text-lg font-semibold">Company Information</h3>
                    </div>

                    <div class="grid gap-4">
                        <div class="grid gap-2">
                            <Label for="company_name">Company Name *</Label>
                            <Input
                                id="company_name"
                                type="text"
                                required
                                :tabindex="5"
                                v-model="form.company_name"
                                placeholder="Your company name"
                            />
                            <InputError :message="form.errors.company_name" />
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="company_email">Company Email</Label>
                                <Input
                                    id="company_email"
                                    type="email"
                                    :tabindex="6"
                                    v-model="form.company_email"
                                    placeholder="company@example.com"
                                />
                                <InputError :message="form.errors.company_email" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="company_phone">Company Phone</Label>
                                <Input
                                    id="company_phone"
                                    type="tel"
                                    :tabindex="7"
                                    v-model="form.company_phone"
                                    placeholder="+1 (555) 123-4567"
                                />
                                <InputError :message="form.errors.company_phone" />
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label for="company_website">Company Website</Label>
                            <Input
                                id="company_website"
                                type="url"
                                :tabindex="8"
                                v-model="form.company_website"
                                placeholder="https://www.yourcompany.com"
                            />
                            <InputError :message="form.errors.company_website" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="company_address">Company Address</Label>
                            <Textarea
                                id="company_address"
                                :tabindex="9"
                                v-model="form.company_address"
                                placeholder="Enter your company address"
                                rows="3"
                            />
                            <InputError :message="form.errors.company_address" />
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="company_timezone">Timezone</Label>
                                <Select v-model="form.company_timezone">
                                    <SelectTrigger :tabindex="10">
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
                                <InputError :message="form.errors.company_timezone" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="company_currency">Currency</Label>
                                <Select v-model="form.company_currency">
                                    <SelectTrigger :tabindex="11">
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
                                <InputError :message="form.errors.company_currency" />
                            </div>
                        </div>
                    </div>
                </div>

                <Button type="submit" class="mt-4 w-full" :tabindex="12" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin mr-2" />
                    Create Company Account
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Already have an account?
                <TextLink :href="route('login')" class="underline underline-offset-4" :tabindex="13">
                    Log in
                </TextLink>
            </div>
        </form>
    </AuthBase>
</template>
