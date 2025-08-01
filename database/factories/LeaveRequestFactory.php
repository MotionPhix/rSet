<?php

namespace Database\Factories;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class LeaveRequestFactory extends Factory
{
    protected $model = LeaveRequest::class;

    public function definition()
    {
        $startDate = $this->faker->dateTimeBetween('+1 week', '+1 month');
        $endDate = Carbon::parse($startDate)->addDays($this->faker->numberBetween(1, 5));

        return [
            'id' => $this->faker->uuid(),
            'user_id' => User::factory(),
            'leave_type' => $this->faker->randomElement(['annual', 'sick', 'personal', 'maternity', 'emergency']),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'reason' => $this->faker->sentence(),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected']),
            'approved_by' => null,
            'approved_at' => null,
            'documentation_path' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function approved()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'approved',
                'approved_by' => User::factory(),
                'approved_at' => now(),
            ];
        });
    }

    public function pending()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'pending',
                'approved_by' => null,
                'approved_at' => null,
            ];
        });
    }

    public function rejected()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'rejected',
                'approved_by' => User::factory(),
                'approved_at' => now(),
            ];
        });
    }
}
