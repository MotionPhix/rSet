<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Holiday extends Model
{
    use HasFactory, HasUuid, BelongsToCompany;

    protected $fillable = [
        'uuid',
        'company_id',
        'name',
        'description',
        'date',
        'is_recurring',
        'recurrence_type',
        'color',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'is_recurring' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user who created this holiday.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to get active holidays only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get holidays for a specific year.
     */
    public function scopeForYear($query, int $year)
    {
        return $query->whereYear('date', $year)
                    ->orWhere(function ($q) use ($year) {
                        $q->where('is_recurring', true)
                          ->where('recurrence_type', 'yearly');
                    });
    }

    /**
     * Get all holiday instances for a given year (including recurring ones).
     */
    public static function getHolidaysForYear(int $year, ?int $companyId = null): array
    {
        $query = self::active();
        
        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        $holidays = $query->get();
        $holidayInstances = [];

        foreach ($holidays as $holiday) {
            if ($holiday->is_recurring && $holiday->recurrence_type === 'yearly') {
                // Generate recurring holiday for the requested year
                $holidayDate = $holiday->date->copy();
                $holidayDate->year($year);
                
                $holidayInstances[] = [
                    'id' => $holiday->uuid . '-' . $year,
                    'original_id' => $holiday->id,
                    'uuid' => $holiday->uuid,
                    'name' => $holiday->name,
                    'description' => $holiday->description,
                    'date' => $holidayDate->format('Y-m-d'),
                    'color' => $holiday->color,
                    'is_recurring' => true,
                    'recurrence_type' => 'yearly',
                    'created_by' => $holiday->created_by,
                    'creator' => $holiday->creator,
                ];
            } else {
                // Regular holiday - only include if it's in the requested year
                if ($holiday->date->year === $year) {
                    $holidayInstances[] = [
                        'id' => $holiday->uuid,
                        'original_id' => $holiday->id,
                        'uuid' => $holiday->uuid,
                        'name' => $holiday->name,
                        'description' => $holiday->description,
                        'date' => $holiday->date->format('Y-m-d'),
                        'color' => $holiday->color,
                        'is_recurring' => false,
                        'recurrence_type' => 'none',
                        'created_by' => $holiday->created_by,
                        'creator' => $holiday->creator,
                    ];
                }
            }
        }

        return $holidayInstances;
    }

    /**
     * Check if a given date conflicts with any holiday.
     */
    public static function conflictsWithHoliday(\DateTime $date, ?int $companyId = null): bool
    {
        $year = (int) $date->format('Y');
        $checkDate = $date->format('Y-m-d');
        
        $holidays = self::getHolidaysForYear($year, $companyId);
        
        foreach ($holidays as $holiday) {
            if ($holiday['date'] === $checkDate) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Scope to get holidays for a specific date range.
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Check if this holiday falls on a specific date.
     */
    public function isOnDate($date): bool
    {
        if (!$this->is_recurring) {
            return $this->date->format('Y-m-d') === $date->format('Y-m-d');
        }

        // For recurring holidays, check if month and day match
        if ($this->recurrence_type === 'yearly') {
            return $this->date->format('m-d') === $date->format('m-d');
        }

        return false;
    }

    /**
     * Get the display date for this holiday (considering recurrence).
     */
    public function getDisplayDateForYear($year): \Carbon\Carbon
    {
        if (!$this->is_recurring) {
            return $this->date;
        }

        if ($this->recurrence_type === 'yearly') {
            return $this->date->setYear($year);
        }

        return $this->date;
    }
}
