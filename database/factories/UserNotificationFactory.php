<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserNotificationFactory extends Factory
{
    protected $model = UserNotification::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(),
            'message' => $this->faker->paragraph(),
            'type' => 'booking',
            'action_url' => null,
            'is_read' => false,
        ];
    }

    public function read()
    {
        return $this->state([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }
}
