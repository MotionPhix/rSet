<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class User extends Authenticatable implements HasMedia
{
  /** @use HasFactory<\Database\Factories\UserFactory> */
  use HasFactory, Notifiable, HasRoles, HasUuid, InteractsWithMedia;

  /**
   * The attributes that are mass assignable.
   *
   * @var list<string>
   */
  protected $fillable = ['name', 'email', 'password', 'team_id', 'company_id'];

  /**
   * The attributes that should be appended to the model's array form.
   *
   * @var list<string>
   */
  protected $appends = ['avatar', 'avatar_thumb'];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var list<string>
   */
  protected $hidden = [
    'password',
    'remember_token',
  ];

  /**
   * Get the attributes that should be cast.
   *
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [
      'email_verified_at' => 'datetime',
      'password' => 'hashed',
    ];
  }

  // A user (employee) has many leave requests
  public function leaveRequests(): HasMany
  {
    return $this->hasMany(LeaveRequest::class);
  }

  // A user (manager/hr) approves many leave requests
  public function approvedRequests(): HasMany
  {
    return $this->hasMany(LeaveRequest::class, 'approver_id');
  }

  // Optional: Team relationship
  public function team(): BelongsTo
  {
    return $this->belongsTo(Team::class);
  }

  // If a user manages a team (optional)
  public function managedTeam(): HasOne
  {
    return $this->hasOne(Team::class, 'manager_id');
  }

  // User belongs to a company
  public function company(): BelongsTo
  {
    return $this->belongsTo(Company::class);
  }

  // Scope to filter users by company
  public function scopeForCompany($query, $companyId)
  {
    return $query->where('company_id', $companyId);
  }

  /**
   * Register media collections for the user model
   */
  public function registerMediaCollections(): void
  {
    $this->addMediaCollection('avatar')
      ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
      ->singleFile();
  }

  /**
   * Register media conversions for the user model
   */
  public function registerMediaConversions(?Media $media = null): void
  {
    $this->addMediaConversion('thumb')
      ->width(150)
      ->height(150)
      ->sharpen(10)
      ->performOnCollections('avatar');

    $this->addMediaConversion('preview')
      ->width(500)
      ->height(500)
      ->sharpen(10)
      ->performOnCollections('avatar');
  }

  /**
   * Get the user's avatar URL
   */
  public function getAvatarAttribute(): ?string
  {
    return $this->getFirstMediaUrl('avatar');
  }

  /**
   * Get the user's avatar thumbnail URL
   */
  public function getAvatarThumbAttribute(): ?string
  {
    return $this->getFirstMediaUrl('avatar', 'thumb');
  }
}
