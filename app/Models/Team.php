<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Team extends Model
{
  use \App\Traits\HasUuid;

  protected $fillable = ['name', 'manager_id', 'company_id'];

  // Team has many users (employees)
  public function users(): HasMany
  {
    return $this->hasMany(User::class);
  }

  // Team has one manager (optional)
  public function manager(): BelongsTo
  {
    return $this->belongsTo(User::class, 'manager_id');
  }

  // Team has many leave requests (via users)
  public function leaveRequests(): HasManyThrough
  {
    return $this->hasManyThrough(LeaveRequest::class, User::class);
  }

  // Team belongs to a company
  public function company(): BelongsTo
  {
    return $this->belongsTo(Company::class);
  }

  // Scope to filter teams by company
  public function scopeForCompany($query, $companyId)
  {
    return $query->where('company_id', $companyId);
  }
}
