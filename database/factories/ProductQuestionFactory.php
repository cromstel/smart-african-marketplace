<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductQuestion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductQuestionFactory extends Factory
{
    protected $model = ProductQuestion::class;

    public function definition(): array
    {
        // product_id is deliberately omitted: the repository has no
        // ProductFactory, and picking a random product here silently attached
        // questions to an arbitrary vendor's product and returned NULL on an
        // empty products table. Always supply it via forProduct() or the
        // create([...]) attributes so misuse fails fast instead of landing on
        // the wrong tenant.
        return [
            'user_id' => null,
            'asker_name' => fake()->name(),
            'asker_email' => fake()->safeEmail(),
            'question' => fake()->sentence(),
            'status' => 'pending',
            'is_public' => false,
            'answer' => null,
            'answered_by' => null,
            'answered_at' => null,
            'moderated_by' => null,
            'moderated_at' => null,
            'moderation_note' => null,
        ];
    }

    public function forProduct(Product|int $product): static
    {
        $productId = $product instanceof Product ? $product->id : $product;

        return $this->state(fn (array $attributes) => [
            'product_id' => $productId,
        ]);
    }

    public function forUser(User|int $user): static
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $this->state(fn (array $attributes) => [
            'user_id' => $userId,
        ]);
    }

    public function answered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'answered',
            'answer' => fake()->sentence(),
            'answered_at' => now(),
        ]);
    }

    public function publiclyVisible(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'answered',
            'is_public' => true,
            'answer' => fake()->sentence(),
            'answered_at' => now(),
        ]);
    }

    public function hidden(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'hidden',
            'is_public' => false,
        ]);
    }
}
