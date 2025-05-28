<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeaveType extends Model
{
  use HasUuid;
  use HasFactory;

  protected $fillable = [
    'name',
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
    'documentation_type'
  ];

  protected $casts = [
    'allow_custom_duration' => 'boolean',
    'requires_approval' => 'boolean',
    'requires_documentation' => 'boolean',
    'approvers' => 'array'
  ];

  // Dynamic attribute for pay tier description
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

  // Check if leave type requires documentation
  public function requiresMedicalCertificate(): bool
  {
    return $this->requires_documentation &&
      $this->documentation_type === 'medical_certificate';
  }
}
