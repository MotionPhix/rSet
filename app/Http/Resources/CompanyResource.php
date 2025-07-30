<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'slug' => $this->slug,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'website' => $this->website,
            'logo' => $this->logo,
            'timezone' => $this->timezone,
            'currency' => $this->currency,
            'date_format' => $this->date_format,
            'time_format' => $this->time_format,
            'is_active' => $this->is_active,
            'subscription_plan' => $this->subscription_plan,
            'subscription_expires_at' => $this->subscription_expires_at?->format('Y-m-d H:i:s'),
            'max_employees' => $this->max_employees,
            'settings' => $this->settings,
            'has_active_subscription' => $this->hasActiveSubscription(),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),

            // Conditional relationships
            'users_count' => $this->when($this->relationLoaded('users'), fn() => $this->users->count()),
            'teams_count' => $this->when($this->relationLoaded('teams'), fn() => $this->teams->count()),
            'leave_types_count' => $this->when($this->relationLoaded('leaveTypes'), fn() => $this->leaveTypes->count()),
            'leave_requests_count' => $this->when($this->relationLoaded('leaveRequests'), fn() => $this->leaveRequests->count()),
        ];
    }
}
