<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition()
    {
        return [
            'title' => $this->faker->word,
            'description' => $this->faker->sentence,
            'status' => $this->faker->word,
            'created_by' => User::factory(), // Cria um usuário automaticamente
            'start_date' => now(),
            'end_date' => now()->addDays(1),
            'working' => $this->faker->boolean,
            'time_spent' => $this->faker->randomFloat(2, 0, 10),
            'workspace_id' => Workspace::factory(),
        ];
    }
}
