<?php

namespace App\Models;

use App\Traits\HasUuid;
use App\Traits\BelongsToCompany;
use App\Enums\LeaveType as LeaveTypeEnum;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeaveType extends Model
{
  use HasUuid, HasFactory, BelongsToCompany;

  protected $fillable = [
    'company_id',
    'name',
    'display_name',
    'description',
    'days_allowed',
    'min_duration',
    'max_duration',
    'allow_custom_duration',
    'gender',
    'min_employment_months',
    'cooldown_days',
    'max_usage_per_year',
    'full_pay_days',
    'half_pay_days',
    'requires_approval',
    'approvers',
    'requires_documentation',
    'documentation_type',
    'color',
    'background_color',
  ];

  protected $casts = [
    'allow_custom_duration' => 'boolean',
    'requires_approval' => 'boolean',
    'requires_documentation' => 'boolean',
    'approvers' => 'array',
    'name' => LeaveTypeEnum::class,
  ];

  /**
   * Get the enum instance for this leave type.
   */
  public function getEnumAttribute(): LeaveTypeEnum
  {
    return $this->name;
  }

  /**
   * Dynamic attribute for pay tier description.
   */
  public function payTierDescription(): Attribute
  {
    return Attribute::make(
      get: function () {
        if ($this->full_pay_days || $this->half_pay_days) {
          return sprintf(
            '%d days full pay + %d days half pay',
            $this->full_pay_days,
            $this->half_pay_days
          );
        }
        return 'Full pay';
      }
    );
  }

  // Scope for gender-specific leave types
  #[Scope]
  public function forGender(Builder $query, ?string $gender)
  {
    return $query->where('gender', $gender)->orWhereNull('gender');
  }

  // Leave type belongs to a company
  public function company()
  {
    return $this->belongsTo(Company::class);
  }

  // Scope to filter leave types by company
  public function scopeForCompany($query, $companyId)
  {
    return $query->where('company_id', $companyId);
  }

  // Check if leave type requires documentation
  public function requiresMedicalCertificate(): bool
  {
    return $this->requires_documentation &&
      $this->documentation_type === 'medical_certificate';
  }
}
