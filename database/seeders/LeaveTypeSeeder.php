<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\LeaveType;
use App\Enums\LeaveType as LeaveTypeEnum;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all companies and seed default leave types for each
        $companies = Company::all();

        foreach ($companies as $company) {
            foreach (LeaveTypeEnum::cases() as $leaveTypeEnum) {
                LeaveType::firstOrCreate(
                    [
                        'company_id' => $company->id,
                        'name' => $leaveTypeEnum->value,
                    ],
                    [
                        'display_name' => $leaveTypeEnum->label(),
                        'description' => $leaveTypeEnum->description(),
                        'days_allowed' => $leaveTypeEnum->defaultDaysAllowed(),
                        'min_duration' => 1,
                        'max_duration' => $leaveTypeEnum->defaultDaysAllowed(),
                        'allow_custom_duration' => true,
                        'gender' => in_array($leaveTypeEnum, [LeaveTypeEnum::MATERNITY]) ? 'female' : 
                                   (in_array($leaveTypeEnum, [LeaveTypeEnum::PATERNITY]) ? 'male' : null),
                        'min_employment_months' => match($leaveTypeEnum) {
                            LeaveTypeEnum::ANNUAL => 3,
                            LeaveTypeEnum::MATERNITY, LeaveTypeEnum::PATERNITY => 6,
                            LeaveTypeEnum::STUDY => 12,
                            default => 0,
                        },
                        'cooldown_days' => match($leaveTypeEnum) {
                            LeaveTypeEnum::PERSONAL => 30,
                            LeaveTypeEnum::EMERGENCY => 60,
                            LeaveTypeEnum::STUDY => 365,
                            default => 0,
                        },
                        'max_usage_per_year' => match($leaveTypeEnum) {
                            LeaveTypeEnum::PERSONAL => 2,
                            LeaveTypeEnum::EMERGENCY => 3,
                            LeaveTypeEnum::MATERNITY, LeaveTypeEnum::PATERNITY => 1,
                            LeaveTypeEnum::BEREAVEMENT => 2,
                            LeaveTypeEnum::STUDY => 1,
                            default => null,
                        },
                        'requires_approval' => $leaveTypeEnum->requiresApproval(),
                        'approvers' => null,
                        'full_pay_days' => match($leaveTypeEnum) {
                            LeaveTypeEnum::ANNUAL => $leaveTypeEnum->defaultDaysAllowed(),
                            LeaveTypeEnum::SICK => 7,
                            LeaveTypeEnum::PERSONAL => 3,
                            LeaveTypeEnum::MATERNITY => 84,
                            LeaveTypeEnum::PATERNITY => 7,
                            LeaveTypeEnum::BEREAVEMENT => 5,
                            LeaveTypeEnum::EMERGENCY => 3,
                            LeaveTypeEnum::STUDY => 10,
                            LeaveTypeEnum::COMPASSIONATE => 5,
                            default => 0,
                        },
                        'half_pay_days' => match($leaveTypeEnum) {
                            LeaveTypeEnum::SICK => 3,
                            LeaveTypeEnum::PERSONAL => 2,
                            LeaveTypeEnum::COMPASSIONATE => 2,
                            default => 0,
                        },
                        'requires_documentation' => $leaveTypeEnum->requiresDocumentation(),
                        'documentation_type' => match($leaveTypeEnum) {
                            LeaveTypeEnum::SICK => 'medical_certificate',
                            LeaveTypeEnum::MATERNITY => 'medical_certificate',
                            LeaveTypeEnum::PATERNITY => 'birth_certificate',
                            LeaveTypeEnum::BEREAVEMENT => 'death_certificate',
                            LeaveTypeEnum::STUDY => 'enrollment_letter',
                            default => null,
                        },
                        'color' => $leaveTypeEnum->color(),
                        'background_color' => $leaveTypeEnum->backgroundColor(),
                    ]
                );
            }
        }
    }
}
