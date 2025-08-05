<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\LeaveType;
use App\Enums\LeaveType as LeaveTypeEnum;
use Illuminate\Console\Command;

class SetupLeaveTypes extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'leave-types:setup {--company-id= : Specific company ID to setup}';

    /**
     * The console command description.
     */
    protected $description = 'Set up default leave types for companies based on the LeaveType enum';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Setting up leave types...');

        // Get companies to setup
        if ($companyId = $this->option('company-id')) {
            $companies = Company::where('id', $companyId)->get();
            if ($companies->isEmpty()) {
                $this->error("Company with ID {$companyId} not found.");
                return 1;
            }
        } else {
            $companies = Company::all();
        }

        if ($companies->isEmpty()) {
            $this->error('No companies found.');
            return 1;
        }

        $this->info("Found {$companies->count()} companies to setup.");

        foreach ($companies as $company) {
            $this->info("Setting up leave types for: {$company->name}");
            
            foreach (LeaveTypeEnum::cases() as $leaveTypeEnum) {
                $leaveType = LeaveType::firstOrCreate(
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

                if ($leaveType->wasRecentlyCreated) {
                    $this->line("  ✓ Created: {$leaveTypeEnum->label()}");
                } else {
                    $this->line("  - Exists: {$leaveTypeEnum->label()}");
                }
            }
        }

        $this->info('✅ Leave types setup completed!');
        return 0;
    }
}
