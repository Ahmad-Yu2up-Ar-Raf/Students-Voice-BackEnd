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
        // HD Photo URLs dari Unsplash (free, high-quality)
        $hdPhotoUrls = [
            'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&q=80', // Mountains
            'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=800&q=80', // Portrait
            'https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?w=800&q=80', // Beach
            'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&q=80', // Nature
            'https://images.unsplash.com/photo-1511379938547-c1f69b13d835?w=800&q=80', // Coffee
            'https://images.unsplash.com/photo-1495521821757-a1efb6729352?w=800&q=80', // Travel
            'https://images.unsplash.com/photo-1505142468610-359e7d316be0?w=800&q=80', // Sunset
            'https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?w=800&q=80', // Ocean
            'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=800&q=80', // Flowers
            'https://images.unsplash.com/photo-1517457373614-b7152f800fd1?w=800&q=80', // Architecture
            'https://images.unsplash.com/photo-1459749411175-04bf5292ceea?w=800&q=80', // Food
            'https://images.unsplash.com/photo-1506126613408-eca07ce68773?w=800&q=80', // Forest
            'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&q=80', // Landscape
            'https://images.unsplash.com/photo-1511632765486-a01980e01a18?w=800&q=80', // City
            'https://images.unsplash.com/photo-1500375592092-40eb2168555a?w=800&q=80', // Tech
            'https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?w=800&q=80', // Water
            'https://images.unsplash.com/photo-1487180144351-b8472da7d491?w=800&q=80', // Street
            'https://images.unsplash.com/photo-1501228996063-fb8b3cd3d2a0?w=800&q=80', // Sports
            'https://images.unsplash.com/photo-1493514789560-586f3a43f5f9?w=800&q=80', // Concert
            'https://images.unsplash.com/photo-1495803768635-c628146faae0?w=800&q=80', // Lifestyle
        ];

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
            $photoUrl = $this->faker->randomElement($hdPhotoUrls);
            $mimeType = $this->faker->randomElement($mimeTypes);
            $fileSize = $this->faker->numberBetween(50000, 500000); // 50KB - 500KB

            // Extract filename from URL or generate one
            $filename = 'photo_' . $this->faker->uuid() . '.' . $this->getExtensionFromMimeType($mimeType);

            $media[] = [
                'uri' => $photoUrl,
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
