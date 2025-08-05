<?php

namespace App\Enums;

enum LeaveType: string
{
    case ANNUAL = 'annual';
    case SICK = 'sick';
    case PERSONAL = 'personal';
    case EMERGENCY = 'emergency';
    case UNPAID = 'unpaid';
    case MATERNITY = 'maternity';
    case PATERNITY = 'paternity';
    case BEREAVEMENT = 'bereavement';
    case STUDY = 'study';
    case COMPASSIONATE = 'compassionate';

    /**
     * Get the display label for the leave type.
     */
    public function label(): string
    {
        return match($this) {
            self::ANNUAL => 'Annual Leave',
            self::SICK => 'Sick Leave',
            self::PERSONAL => 'Personal Leave',
            self::EMERGENCY => 'Emergency Leave',
            self::UNPAID => 'Unpaid Leave',
            self::MATERNITY => 'Maternity Leave',
            self::PATERNITY => 'Paternity Leave',
            self::BEREAVEMENT => 'Bereavement Leave',
            self::STUDY => 'Study Leave',
            self::COMPASSIONATE => 'Compassionate Leave',
        };
    }

    /**
     * Get the description for the leave type.
     */
    public function description(): string
    {
        return match($this) {
            self::ANNUAL => 'Yearly vacation leave for rest and personal time',
            self::SICK => 'Medical leave for illness or health appointments',
            self::PERSONAL => 'Personal time off for non-medical emergencies',
            self::EMERGENCY => 'Urgent family or personal emergencies',
            self::UNPAID => 'Unpaid time off',
            self::MATERNITY => 'Leave for childbirth and bonding with new child',
            self::PATERNITY => 'Leave for fathers after childbirth or adoption',
            self::BEREAVEMENT => 'Leave for mourning the death of a family member',
            self::STUDY => 'Leave for educational purposes and professional development',
            self::COMPASSIONATE => 'Leave for serious family illness or emergencies',
        };
    }

    /**
     * Get the default days allowed for this leave type.
     */
    public function defaultDaysAllowed(): int
    {
        return match($this) {
            self::ANNUAL => 25,
            self::SICK => 10,
            self::PERSONAL => 5,
            self::EMERGENCY => 3,
            self::UNPAID => 365, // No limit, but company policies apply
            self::MATERNITY => 84, // 12 weeks (Malawi standard)
            self::PATERNITY => 7,  // 1 week (Malawi standard)
            self::BEREAVEMENT => 5,
            self::STUDY => 10,
            self::COMPASSIONATE => 7,
        };
    }

    /**
     * Get the color for calendar display.
     */
    public function color(): string
    {
        return match($this) {
            self::ANNUAL => '#3b82f6',      // Blue
            self::SICK => '#ef4444',        // Red
            self::PERSONAL => '#8b5cf6',    // Purple
            self::EMERGENCY => '#f97316',   // Orange
            self::UNPAID => '#6b7280',      // Gray
            self::MATERNITY => '#ec4899',   // Pink
            self::PATERNITY => '#06b6d4',   // Cyan
            self::BEREAVEMENT => '#374151', // Dark Gray
            self::STUDY => '#10b981',       // Green
            self::COMPASSIONATE => '#f59e0b', // Amber
        };
    }

    /**
     * Get the background color for calendar display.
     */
    public function backgroundColor(): string
    {
        return match($this) {
            self::ANNUAL => '#dbeafe',      // Blue-100
            self::SICK => '#fee2e2',        // Red-100
            self::PERSONAL => '#ede9fe',    // Purple-100
            self::EMERGENCY => '#fed7aa',   // Orange-100
            self::UNPAID => '#f3f4f6',      // Gray-100
            self::MATERNITY => '#fce7f3',   // Pink-100
            self::PATERNITY => '#cffafe',   // Cyan-100
            self::BEREAVEMENT => '#f9fafb', // Gray-50
            self::STUDY => '#d1fae5',       // Green-100
            self::COMPASSIONATE => '#fef3c7', // Amber-100
        };
    }

    /**
     * Check if this leave type requires approval.
     */
    public function requiresApproval(): bool
    {
        return match($this) {
            self::SICK => false, // Immediate approval for sick leave
            default => true,
        };
    }

    /**
     * Check if this leave type requires documentation.
     */
    public function requiresDocumentation(): bool
    {
        return match($this) {
            self::SICK => true,
            self::MATERNITY => true,
            self::PATERNITY => true,
            self::BEREAVEMENT => true,
            self::STUDY => true,
            default => false,
        };
    }

    /**
     * Get all leave types as array.
     */
    public static function toArray(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }

    /**
     * Get all leave types with labels.
     */
    public static function options(): array
    {
        return array_map(function($case) {
            return [
                'value' => $case->value,
                'label' => $case->label(),
                'description' => $case->description(),
                'color' => $case->color(),
                'backgroundColor' => $case->backgroundColor(),
            ];
        }, self::cases());
    }

    /**
     * Create a LeaveType from string value.
     */
    public static function fromString(string $value): ?self
    {
        return self::tryFrom($value);
    }
}
