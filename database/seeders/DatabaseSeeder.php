<?php

namespace Database\Seeders;

use App\Models\Likes;
use App\Models\Post;
use App\Models\Repost;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Hierarchy Best Practice:
     * 1. Create 15 users
     * 2. Create 3-5 posts per user (45-75 total posts)
     * 3. Create likes (2-8 per post from random users, prevent duplicates)
     * 4. Create reposts (1-4 per post from random users, prevent duplicates)
     */
    public function run(): void
    {
        // Step 1: Create users with proper data
        $users = User::factory(15)->create();
        $this->command->info("✓ Created {$users->count()} users");

        // Step 2: Create posts for each user (3-5 posts per user)
        $allPosts = collect(); // Collect all created posts

        foreach ($users as $user) {
            $postCount = rand(3, 5);
            $userPosts = Post::factory($postCount)->forUser($user)->create();
            $allPosts = $allPosts->merge($userPosts);
        }

        $this->command->info("✓ Created {$allPosts->count()} posts");

        // Step 3: Create likes for each post
        $totalLikes = 0;

        foreach ($allPosts as $post) {
            $likeCount = rand(2, 8);
            $usersForLike = $users->random(min($likeCount, $users->count()))->unique('id');

            foreach ($usersForLike as $user) {
                // Prevent duplicate likes from same user on same post
                $likeExists = Likes::where('user_id', $user->id)
                    ->where('post_id', $post->id)
                    ->exists();

                if (!$likeExists) {
                    Likes::factory()
                        ->forUserAndPost($user, $post)
                        ->create();
                    $totalLikes++;
                }
            }
        }

        $this->command->info("✓ Created {$totalLikes} likes");

        // Step 4: Create reposts for each post
        $totalReposts = 0;

        foreach ($allPosts as $post) {
            $repostCount = rand(1, 4);
            $usersForRepost = $users->random(min($repostCount, $users->count()))->unique('id');

            foreach ($usersForRepost as $user) {
                // Prevent duplicate reposts from same user on same post
                $repostExists = Repost::where('user_id', $user->id)
                    ->where('post_id', $post->id)
                    ->exists();

                if (!$repostExists) {
                    Repost::factory()
                        ->forUserAndPost($user, $post)
                        ->create();
                    $totalReposts++;
                }
            }
        }

        $this->command->info("✓ Created {$totalReposts} reposts");

        // Summary
        $this->command->newLine();
        $this->command->info('═══════════════════════════════════════');
        $this->command->info('   Database Seeding Completed! ✓');
        $this->command->info('═══════════════════════════════════════');
        $this->command->line("📊 Users:       {$users->count()}");
        $this->command->line("📝 Posts:       {$allPosts->count()}");
        $this->command->line("❤️  Likes:       {$totalLikes}");
        $this->command->line("🔄 Reposts:     {$totalReposts}");
        $this->command->info('═══════════════════════════════════════');
    }
}
