<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Services\CompanyService;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function __construct(
        private CompanyService $companyService
    ) {}

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample companies with realistic data
        $companies = [
            [
                'name' => 'TechCorp Solutions',
                'email' => 'admin@techcorp.com',
                'phone' => '+1-555-0123',
                'address' => '123 Tech Street, Silicon Valley, CA 94000, USA',
                'website' => 'https://techcorp.com',
                'timezone' => 'America/Los_Angeles',
                'currency' => 'USD',
                'max_employees' => 150,
                'subscription_plan' => 'premium',
                'subscription_expires_at' => now()->addYear(),
                'settings' => [
                    'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                    'working_hours_start' => '09:00',
                    'working_hours_end' => '17:00',
                    'leave_approval_workflow' => 'manager_then_hr',
                    'auto_approve_sick_leave' => false,
                    'require_medical_certificate_after_days' => 3,
                ],
            ],
            [
                'name' => 'Global Innovations Ltd',
                'email' => 'hr@globalinnovations.com',
                'phone' => '+44-20-7946-0958',
                'address' => '456 Innovation Ave, London, UK EC1A 1BB',
                'website' => 'https://globalinnovations.co.uk',
                'timezone' => 'Europe/London',
                'currency' => 'GBP',
                'max_employees' => 100,
                'subscription_plan' => 'standard',
                'subscription_expires_at' => now()->addMonths(6),
                'settings' => [
                    'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                    'working_hours_start' => '08:30',
                    'working_hours_end' => '17:30',
                    'leave_approval_workflow' => 'manager_only',
                    'auto_approve_sick_leave' => true,
                    'require_medical_certificate_after_days' => 5,
                ],
            ],
            [
                'name' => 'StartupHub Inc',
                'email' => 'team@startuphub.com',
                'phone' => '+1-555-0456',
                'address' => '789 Startup Blvd, Austin, TX 78701, USA',
                'website' => 'https://startuphub.com',
                'timezone' => 'America/Chicago',
                'currency' => 'USD',
                'max_employees' => 50,
                'subscription_plan' => 'basic',
                'subscription_expires_at' => now()->addMonths(3),
                'settings' => [
                    'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                    'working_hours_start' => '10:00',
                    'working_hours_end' => '18:00',
                    'leave_approval_workflow' => 'hr_only',
                    'auto_approve_sick_leave' => true,
                    'require_medical_certificate_after_days' => 2,
                ],
            ],
            [
                'name' => 'Creative Agency Pro',
                'email' => 'hello@creativeagency.com',
                'phone' => '+1-555-0789',
                'address' => '321 Creative Lane, New York, NY 10001, USA',
                'website' => 'https://creativeagency.com',
                'timezone' => 'America/New_York',
                'currency' => 'USD',
                'max_employees' => 75,
                'subscription_plan' => 'standard',
                'subscription_expires_at' => now()->addMonths(9),
                'settings' => [
                    'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                    'working_hours_start' => '09:30',
                    'working_hours_end' => '17:30',
                    'leave_approval_workflow' => 'manager_then_hr',
                    'auto_approve_sick_leave' => false,
                    'require_medical_certificate_after_days' => 3,
                ],
            ],
            [
                'name' => 'Manufacturing Corp',
                'email' => 'admin@manufacturing.com',
                'phone' => '+1-555-0321',
                'address' => '654 Industrial Way, Detroit, MI 48201, USA',
                'website' => 'https://manufacturing.com',
                'timezone' => 'America/Detroit',
                'currency' => 'USD',
                'max_employees' => 200,
                'subscription_plan' => 'premium',
                'subscription_expires_at' => now()->addYear(2),
                'settings' => [
                    'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'],
                    'working_hours_start' => '07:00',
                    'working_hours_end' => '15:00',
                    'leave_approval_workflow' => 'manager_then_hr',
                    'auto_approve_sick_leave' => false,
                    'require_medical_certificate_after_days' => 1,
                ],
            ],
        ];

        foreach ($companies as $companyData) {
            $this->companyService->createCompany($companyData);
        }

        $this->command->info('Sample companies created successfully!');
    }
}
