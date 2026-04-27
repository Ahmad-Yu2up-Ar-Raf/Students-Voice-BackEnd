<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\Repost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Repost>
 */
class RepostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'post_id' => Post::factory(),
        ];
    }

    /**
     * Associate repost with a specific user and post.
     */
    public function forUserAndPost(User $user, Post $post): static
    {
        return $this->state(fn(array $attributes) => [
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);
    }
}
