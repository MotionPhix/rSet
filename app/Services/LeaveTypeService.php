<?php

namespace App\Services;

use App\Enums\LeaveType;
use App\Models\LeaveType as LeaveTypeModel;

class LeaveTypeService
{
    /**
     * Get all available leave types from enum.
     */
    public static function getTypes(): array
    {
        $types = [];
        foreach (LeaveType::cases() as $case) {
            $types[$case->value] = $case->label();
        }
        return $types;
    }

    /**
     * Get leave types for select options.
     */
    public static function getSelectOptions(): array
    {
        return LeaveType::options();
    }

    /**
     * Get detailed leave types with all properties for form validation.
     */
    public static function getDetailedOptions(): array
    {
        $detailedTypes = [];
        
        foreach (LeaveType::cases() as $leaveType) {
            $detailedTypes[$leaveType->value] = [
                'value' => $leaveType->value,
                'label' => $leaveType->label(),
                'description' => $leaveType->description(),
                'days_allowed' => $leaveType->defaultDaysAllowed(),
                'min_duration' => 1,
                'max_duration' => $leaveType->defaultDaysAllowed(),
                'allow_custom_duration' => true,
                'gender' => match($leaveType) {
                    LeaveType::MATERNITY => 'female',
                    LeaveType::PATERNITY => 'male',
                    default => null,
                },
                'min_employment_months' => match($leaveType) {
                    LeaveType::ANNUAL => 3,
                    LeaveType::MATERNITY, LeaveType::PATERNITY => 6,
                    LeaveType::STUDY => 12,
                    default => 0,
                },
                'cooldown_days' => match($leaveType) {
                    LeaveType::PERSONAL => 30,
                    LeaveType::EMERGENCY => 60,
                    LeaveType::STUDY => 365,
                    default => null,
                },
                'max_usage_per_year' => match($leaveType) {
                    LeaveType::PERSONAL => 2,
                    LeaveType::EMERGENCY => 3,
                    LeaveType::MATERNITY, LeaveType::PATERNITY => 1,
                    LeaveType::BEREAVEMENT => 2,
                    LeaveType::STUDY => 1,
                    default => null,
                },
                'full_pay_days' => match($leaveType) {
                    LeaveType::ANNUAL => $leaveType->defaultDaysAllowed(),
                    LeaveType::SICK => 7,
                    LeaveType::PERSONAL => 3,
                    LeaveType::MATERNITY => 84,
                    LeaveType::PATERNITY => 7,
                    LeaveType::BEREAVEMENT => 5,
                    LeaveType::EMERGENCY => 3,
                    LeaveType::STUDY => 10,
                    LeaveType::COMPASSIONATE => 5,
                    default => 0,
                },
                'half_pay_days' => match($leaveType) {
                    LeaveType::SICK => 3,
                    LeaveType::PERSONAL => 2,
                    LeaveType::COMPASSIONATE => 2,
                    default => 0,
                },
                'requires_approval' => $leaveType->requiresApproval(),
                'requires_documentation' => $leaveType->requiresDocumentation(),
                'documentation_type' => match($leaveType) {
                    LeaveType::SICK => 'medical_certificate',
                    LeaveType::MATERNITY => 'medical_certificate',
                    LeaveType::PATERNITY => 'birth_certificate',
                    LeaveType::BEREAVEMENT => 'death_certificate',
                    LeaveType::STUDY => 'enrollment_letter',
                    default => null,
                },
                'color' => $leaveType->color(),
                'backgroundColor' => $leaveType->backgroundColor(),
            ];
        }
        
        return $detailedTypes;
    }

    /**
     * Get leave type keys only.
     */
    public static function getTypeKeys(): array
    {
        return LeaveType::toArray();
    }

    /**
     * Validate if a leave type is valid.
     */
    public static function isValidType(string $type): bool
    {
        return LeaveType::fromString($type) !== null;
    }

    /**
     * Get leave type from company database.
     */
    public static function getCompanyLeaveTypes(int $companyId): array
    {
        return LeaveTypeModel::where('company_id', $companyId)
            ->get()
            ->mapWithKeys(function ($leaveType) {
                return [$leaveType->name->value => $leaveType->display_name];
            })
            ->toArray();
    }

    /**
     * Get display name for a leave type.
     */
    public static function getDisplayName(string $type): string
    {
        return self::getTypes()[$type] ?? ucfirst($type) . ' Leave';
    }
}
