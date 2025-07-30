<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\User;
use Illuminate\Console\Command;

class ShowSampleCredentials extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:show-credentials';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show sample login credentials for testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔑 Sample Login Credentials');
        $this->info('==========================');
        $this->newLine();

        // Super Admin
        $superAdmin = User::whereHas('roles', function($query) {
            $query->where('name', 'super-admin');
        })->first();

        if ($superAdmin) {
            $this->info('🌟 Super Administrator (System Owner):');
            $this->line("   Email: {$superAdmin->email}");
            $this->line("   Password: password");
            $this->newLine();
        }

        // Company credentials
        $companies = Company::with(['users' => function($query) {
            $query->with('roles');
        }])->get();

        foreach ($companies as $company) {
            $this->info("🏢 {$company->name}:");

            // Admin
            $admin = $company->users()->whereHas('roles', function($query) {
                $query->where('name', 'admin');
            })->first();

            if ($admin) {
                $this->line("   Admin: {$admin->email} / password");
            }

            // HR
            $hr = $company->users()->whereHas('roles', function($query) {
                $query->where('name', 'hr');
            })->first();

            if ($hr) {
                $this->line("   HR: {$hr->email} / password");
            }

            // Manager
            $manager = $company->users()->whereHas('roles', function($query) {
                $query->where('name', 'manager');
            })->first();

            if ($manager) {
                $this->line("   Manager: {$manager->email} / password");
            }

            // Employee
            $employee = $company->users()->whereHas('roles', function($query) {
                $query->where('name', 'employee');
            })->first();

            if ($employee) {
                $this->line("   Employee: {$employee->email} / password");
            }

            $this->newLine();
        }

        // Statistics
        $this->info('📊 Database Statistics:');
        $this->line('   Companies: ' . Company::count());
        $this->line('   Users: ' . User::count());
        $this->line('   Teams: ' . \App\Models\Team::count());
        $this->line('   Leave Types: ' . \App\Models\LeaveType::count());
        $this->line('   Leave Requests: ' . \App\Models\LeaveRequest::count());
        $this->newLine();

        $this->info('✅ All passwords are: password');
        $this->info('🌐 You can now test the multi-tenancy system!');
    }
}
