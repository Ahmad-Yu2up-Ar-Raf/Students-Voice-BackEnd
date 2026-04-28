<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use App\TaglineType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * Best Practice:
     * - Multiple HD photos per post (1-4)
     * - Realistic image URLs from reliable sources
     * - Proper data type structure
     * - Varied content
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Mime types for photos
        $mimeTypes = [
            'image/jpeg',
            'image/png',
            'image/webp',
            'image/jpg',
        ];

        // Generate 2-4 photos per post
        $photoCount = $this->faker->numberBetween(2, 4);
        $media = [];

        for ($i = 0; $i < $photoCount; $i++) {
            $mimeType = $this->faker->randomElement($mimeTypes);
            $fileSize = $this->faker->numberBetween(50000, 500000); // 50KB - 500KB

            // Generate filename with local path structure
            $filename = 'photo_' . $this->faker->uuid() . '.' . $this->getExtensionFromMimeType($mimeType);
            $filePath = 'Post/images/' . $filename;

            $media[] = [
                'file_path' => $filePath, // Local file path instead of URL
                'name' => $filename,
                'type' => 'photo',
                'size' => $fileSize,
                'mimeType' => $mimeType,
            ];
        }

        // Get all tagline types
        $taglines = array_map(
            fn($case) => $case->value,
            TaglineType::cases()
        );

        // Category tags
        $categories = [
            'travel',
            'food',
            'tech',
            'lifestyle',
            'nature',
            'photography',
            'art',
            'music',
            'sports',
            'fashion',
            'fitness',
            'business',
            'education',
            'entertainment',
        ];

        return [
            'user_id' => User::factory(),
            'media' => $media, // Multiple HD photos with complete metadata
            'caption' => $this->faker->paragraph(
                $this->faker->numberBetween(2, 4)
            ),
            'tag_category' => $this->faker->randomElements(
                $categories,
                $this->faker->numberBetween(1, 3)
            ),
            'tag_location' => $this->faker->city() . ', ' . $this->faker->country(),
            'tagline' => $this->faker->randomElement($taglines),
        ];
    }

    /**
     * Get file extension from MIME type
     */
    private function getExtensionFromMimeType(string $mimeType): string
    {
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/jpg' => 'jpg',
        ];

        return $extensions[$mimeType] ?? 'jpg';
    }

    /**
     * Associate post with a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn(array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
}
