<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'address',
        'website',
        'logo',
        'timezone',
        'currency',
        'date_format',
        'time_format',
        'is_active',
        'subscription_plan',
        'subscription_expires_at',
        'max_employees',
        'settings'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'subscription_expires_at' => 'datetime',
        'settings' => 'array',
        'deleted_at' => 'datetime',
    ];

    protected $attributes = [
        'is_active' => true,
        'timezone' => 'UTC',
        'currency' => 'USD',
        'date_format' => 'Y-m-d',
        'time_format' => 'H:i',
        'max_employees' => 50,
        'settings' => '{}',
    ];

    // Company has many users
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    // Company has many teams
    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    // Company has many leave types
    public function leaveTypes(): HasMany
    {
        return $this->hasMany(LeaveType::class);
    }

    // Company has many leave requests through users
    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    // Scope for active companies
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Check if company subscription is active
    public function hasActiveSubscription(): bool
    {
        return $this->subscription_expires_at === null ||
               $this->subscription_expires_at->isFuture();
    }

    // Get company by slug
    public function scopeBySlug($query, $slug)
    {
        return $query->where('slug', $slug);
    }
}
