<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeamFactory extends Factory
{
    protected $model = Team::class;

    public function definition()
    {
        return [
            'id' => $this->faker->uuid(),
            'company_id' => Company::factory(),
            'name' => $this->faker->words(2, true) . ' Team',
            'description' => $this->faker->sentence(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
