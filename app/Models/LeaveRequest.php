<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
  use \App\Traits\HasUuid;

  protected $fillable = ['user_id', 'approver_id', 'start_date', 'end_date', 'type', 'reason', 'status', 'company_id'];

  // Employee who requested the leave
  public function user() {
    return $this->belongsTo(User::class);
  }

  // Manager/HR who approved the leave
  public function approver() {
    return $this->belongsTo(User::class, 'approver_id');
  }

  // Leave request belongs to a company
  public function company() {
    return $this->belongsTo(Company::class);
  }

  // Get leave requests for a specific team
  public function scopeForTeam($query, $teamId) {
    return $query->whereHas('user', function ($q) use ($teamId) {
      $q->where('team_id', $teamId);
    });
  }

  // Get leave requests for a specific company
  public function scopeForCompany($query, $companyId) {
    return $query->where('company_id', $companyId);
  }
}
