<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\LeaveTypeService;

class LeaveRequestResource extends JsonResource
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
            'type' => $this->type,
            'type_display' => $this->getTypeDisplayName(),
            'start_date' => $this->start_date->format('M j, Y'),
            'start_date_raw' => $this->start_date->format('Y-m-d'),
            'end_date' => $this->end_date->format('M j, Y'),
            'end_date_raw' => $this->end_date->format('Y-m-d'),
            'days' => $this->days,
            'status' => $this->status,
            'status_display' => ucfirst($this->status),
            'reason' => $this->reason,
            'approver_name' => $this->whenLoaded('approver', function () {
                return $this->approver?->name;
            }),
            'submitted_at' => $this->created_at->format('M j, Y'),
            'submitted_at_raw' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('M j, Y'),
            'updated_at_raw' => $this->updated_at->format('Y-m-d H:i:s'),
            'can_edit' => $this->status === 'pending',
            'can_cancel' => in_array($this->status, ['pending', 'approved']) && $this->start_date->isFuture(),
            'is_past' => $this->start_date->isPast(),
            'is_future' => $this->start_date->isFuture(),
            'is_current' => $this->start_date->isPast() && $this->end_date->isFuture(),
        ];
    }

    /**
     * Get the display name for the leave type.
     */
    private function getTypeDisplayName(): string
    {
        return LeaveTypeService::getDisplayName($this->type);
    }
}
