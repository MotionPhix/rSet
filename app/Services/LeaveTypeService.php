<?php

namespace App\Services;

class LeaveTypeService
{
    /**
     * Get all available leave types.
     */
    public static function getTypes(): array
    {
        return [
            'annual' => 'Annual Leave',
            'sick' => 'Sick Leave',
            'personal' => 'Personal Leave',
            'emergency' => 'Emergency Leave',
            'unpaid' => 'Unpaid Leave',
        ];
    }

    /**
     * Get leave types for select options (with proper values).
     */
    public static function getSelectOptions(): array
    {
        return collect(self::getTypes())->map(function ($label, $value) {
            return [
                'value' => $value,
                'label' => $label,
            ];
        })->values()->toArray();
    }

    /**
     * Get detailed leave types with all properties for form validation.
     */
    public static function getDetailedOptions(): array
    {
        $detailedTypes = [
            'annual' => [
                'value' => 'annual',
                'label' => 'Annual Leave',
                'description' => 'Yearly vacation leave for rest and personal time',
                'days_allowed' => 25,
                'min_duration' => 1,
                'max_duration' => 21,
                'allow_custom_duration' => true,
                'gender' => null,
                'min_employment_months' => 3,
                'cooldown_days' => null,
                'max_usage_per_year' => null,
                'full_pay_days' => 25,
                'half_pay_days' => 0,
                'requires_approval' => true,
                'requires_documentation' => false,
                'documentation_type' => null,
            ],
            'sick' => [
                'value' => 'sick',
                'label' => 'Sick Leave',
                'description' => 'Medical leave for illness or health appointments',
                'days_allowed' => 10,
                'min_duration' => 1,
                'max_duration' => 10,
                'allow_custom_duration' => true,
                'gender' => null,
                'min_employment_months' => 1,
                'cooldown_days' => null,
                'max_usage_per_year' => null,
                'full_pay_days' => 10,
                'half_pay_days' => 0,
                'requires_approval' => false,
                'requires_documentation' => true,
                'documentation_type' => 'medical_certificate',
            ],
            'personal' => [
                'value' => 'personal',
                'label' => 'Personal Leave',
                'description' => 'Personal time off for non-medical emergencies',
                'days_allowed' => 5,
                'min_duration' => 1,
                'max_duration' => 5,
                'allow_custom_duration' => true,
                'gender' => null,
                'min_employment_months' => 6,
                'cooldown_days' => 30,
                'max_usage_per_year' => 2,
                'full_pay_days' => 3,
                'half_pay_days' => 2,
                'requires_approval' => true,
                'requires_documentation' => false,
                'documentation_type' => null,
            ],
            'emergency' => [
                'value' => 'emergency',
                'label' => 'Emergency Leave',
                'description' => 'Urgent family or personal emergencies',
                'days_allowed' => 3,
                'min_duration' => 1,
                'max_duration' => 3,
                'allow_custom_duration' => false,
                'gender' => null,
                'min_employment_months' => 1,
                'cooldown_days' => null,
                'max_usage_per_year' => 3,
                'full_pay_days' => 2,
                'half_pay_days' => 1,
                'requires_approval' => true,
                'requires_documentation' => true,
                'documentation_type' => 'emergency_proof',
            ],
            'unpaid' => [
                'value' => 'unpaid',
                'label' => 'Unpaid Leave',
                'description' => 'Extended leave without pay for personal reasons',
                'days_allowed' => 90,
                'min_duration' => 5,
                'max_duration' => 90,
                'allow_custom_duration' => true,
                'gender' => null,
                'min_employment_months' => 12,
                'cooldown_days' => 90,
                'max_usage_per_year' => 1,
                'full_pay_days' => 0,
                'half_pay_days' => 0,
                'requires_approval' => true,
                'requires_documentation' => true,
                'documentation_type' => 'justification_letter',
            ],
        ];

        return collect($detailedTypes)->values()->toArray();
    }

    /**
     * Get leave type keys only.
     */
    public static function getTypeKeys(): array
    {
        return array_keys(self::getTypes());
    }

    /**
     * Validate if a leave type is valid.
     */
    public static function isValidType(string $type): bool
    {
        return array_key_exists($type, self::getTypes());
    }

    /**
     * Get display name for a leave type.
     */
    public static function getDisplayName(string $type): string
    {
        return self::getTypes()[$type] ?? ucfirst($type) . ' Leave';
    }
}
