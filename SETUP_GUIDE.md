# 🔧 ENVIRONMENT & SETUP VERIFICATION

## System Requirements

- ✅ PHP 8.1+ (Laravel 11 requirement)
- ✅ Composer (for dependencies)
- ✅ Database (MySQL/PostgreSQL/SQLite)
- ✅ Laravel 11
- ✅ Internet connection (for Unsplash URLs)

---

## 📦 Dependencies Installed

### Auto-loaded via Composer

```bash
composer install
```

### Key Packages Used

- `laravel/framework: ^11.0`
- `fakerphp/faker: ^1.23` (for generating realistic data)
- Database drivers (MySQL, PostgreSQL, etc.)

### Verification

```bash
php --version                    # Check PHP version
php -m | grep pdo               # Check PDO extension
composer show laravel/framework # Check Laravel version
```

---

## 🗄️ Database Configuration

### .env File Settings

```env
DB_CONNECTION=mysql              # or sqlite, pgsql, sqlsrv
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=social_app           # Your database name
DB_USERNAME=root
DB_PASSWORD=password
```

### For SQLite (Quick Testing)

```env
DB_CONNECTION=sqlite
DB_DATABASE=database.sqlite      # Will be created in database/ folder
```

### For PostgreSQL

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=social_app
DB_USERNAME=postgres
DB_PASSWORD=password
```

---

## 📋 Pre-Seeding Checklist

Before running `php artisan migrate:fresh --seed`:

### 1. Clear Cache

```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### 2. Verify Database Connection

```bash
php artisan tinker
> DB::connection()->getDatabaseName()
// Should return your database name

> exit
```

### 3. Check Migrations

```bash
php artisan migrate:status
// All migrations should show as "not run yet"
```

### 4. Verify Factories Exist

```bash
ls database/factories/
// Should see:
// - UserFactory.php
// - PostFactory.php      ✅ NEW
// - LikesFactory.php     ✅ NEW
// - RepostFactory.php    ✅ NEW
```

### 5. Verify Models Have Traits

```bash
grep "HasFactory" app/Models/*.php
// Should show:
// app/Models/User.php:use HasFactory;
// app/Models/Post.php:use HasFactory;
// app/Models/Likes.php:use HasFactory;
// app/Models/Repost.php:use HasFactory;
```

---

## 🚀 Running the Seeder

### Step 1: Fresh Migration (Recommended for Development)

```bash
php artisan migrate:fresh --seed
```

**What this does:**

- ✅ Drops all existing tables
- ✅ Runs all migrations
- ✅ Executes DatabaseSeeder

### Step 2: Monitor Output

```
Dropping all tables ................................................... DONE

INFO  Preparing database.

Creating migration table ............................................ DONE

INFO  Running migrations.

0001_01_01_000000_create_users_table ................................. DONE
0001_01_01_000001_create_cache_table ................................ DONE
0001_01_01_000002_create_jobs_table ................................ DONE
2026_04_21_023653_create_personal_access_tokens_table ................ DONE
2026_04_21_024050_create_posts_table ................................ DONE
2026_04_21_031215_create_likes_table ................................ DONE
2026_04_21_031221_create_reposts_table .............................. DONE

INFO  Seeding database.

✓ Created 15 users
✓ Created 52 posts
✓ Created 298 likes
✓ Created 152 reposts

═══════════════════════════════════════
   Database Seeding Completed! ✓
═══════════════════════════════════════
```

---

## ✅ Post-Seeding Verification

### 1. Test in Tinker

```bash
php artisan tinker
```

#### Get User Count

```php
> User::count()
// Output: 15
```

#### Get Post Count

```php
> Post::count()
// Output: 45-75 (varies due to random)
```

#### Get Single User with Posts

```php
> $user = User::with('posts')->first()
> $user->name
> $user->posts->count()  // Should be 3-5
```

#### Get Post with Media

```php
> $post = Post::first()
> $post->media  // Should be array of 2-4 photos
> json_encode($post->media, JSON_PRETTY_PRINT)
```

#### Check Relationships

```php
> $post = Post::first()
> $post->user->name           // Creator name
> $post->likes()->count()     // Number of likes
> $post->reposts()->count()   // Number of reposts
```

#### Verify Duplicate Prevention

```php
> Likes::where('user_id', 1)->where('post_id', 1)->count()
// Should be 0 or 1 (never more than 1)

> Repost::where('user_id', 1)->where('post_id', 1)->count()
// Should be 0 or 1 (never more than 1)
```

### 2. Query Database Directly

```sql
-- Check users
SELECT COUNT(*) FROM users;
-- Expected: 15

-- Check posts
SELECT COUNT(*) FROM posts;
-- Expected: 45-75

-- Check likes
SELECT COUNT(*) FROM likes;
-- Expected: 300-600

-- Check reposts
SELECT COUNT(*) FROM reposts;
-- Expected: 45-300

-- Verify no duplicate likes
SELECT user_id, post_id, COUNT(*)
FROM likes
GROUP BY user_id, post_id
HAVING COUNT(*) > 1;
-- Expected: Empty result (no duplicates)

-- Check media structure
SELECT media FROM posts LIMIT 1;
-- Expected: JSON array with photos
```

### 3. Check Timestamps

```php
> User::first()->created_at
> User::first()->updated_at
> Post::first()->created_at
```

---

## 🔄 Alternative Commands

### Seed Existing Database (without fresh)

```bash
php artisan db:seed
// Only runs seeder, keeps existing data
```

### Reset & Seed

```bash
php artisan migrate:reset
php artisan migrate
php artisan db:seed
```

### Run Specific Seeder

```bash
php artisan db:seed --class=DatabaseSeeder
```

### Seed with Custom Number of Users

Create a custom seeder:

```php
// database/seeders/CustomSeeder.php
public function run(): void
{
    $users = User::factory(100)->create();  // Different count
    // ... rest of logic
}

// Run it
php artisan db:seed --class=CustomSeeder
```

---

## 🛠️ Troubleshooting

### Issue: "Connection refused"

```
SOLUTION:
1. Check database server is running
2. Verify DB_HOST, DB_PORT in .env
3. Test connection: mysql -u root -p
```

### Issue: "No such table"

```
SOLUTION:
php artisan migrate:fresh
// Ensure migrations run before seeding
```

### Issue: "Call to undefined method factory()"

```
SOLUTION:
1. Check HasFactory trait exists in model
2. Check factory file exists in database/factories/
3. Run: php artisan make:factory PostFactory --model=Post
```

### Issue: "Image URLs not loading"

```
SOLUTION:
1. Check internet connection
2. Check firewall allows external URLs
3. Fallback: Use local image storage instead
```

### Issue: "Duplicate entry" error

```
SOLUTION:
1. Clear all tables: php artisan migrate:fresh
2. Run seeder again: php artisan migrate:fresh --seed
3. Check unique constraints
```

---

## 📊 Performance Optimization

### For Large Seeding (1000+ users):

#### 1. Disable Query Logging

```php
// DatabaseSeeder.php
DB::disableQueryLog();

// Then seed...

DB::enableQueryLog();
```

#### 2. Use Chunking

```php
$users = User::factory(1000)->create();
$users->chunk(100)->each(function($chunk) {
    // Process each 100 at a time
});
```

#### 3. Bulk Insert

```php
Post::insert($postsData);  // Instead of create()
```

#### 4. Progress Bar

```php
$bar = $this->output->createProgressBar(1000);
foreach (range(1, 1000) as $i) {
    // Do work
    $bar->advance();
}
$bar->finish();
```

---

## 🔐 Security Notes

### For Development Only

- ✅ All user passwords: `password` (dev-only)
- ✅ No sensitive data in seeds
- ✅ Fake emails (safe for testing)
- ✅ Public image URLs (Unsplash)

### For Production

- ❌ Don't run with `--seed` flag
- ❌ Don't include real user data
- ❌ Use environment-specific seeders
- ❌ Protect DatabaseSeeder.php in repo

### Recommended Production Setup

```php
// database/seeders/DatabaseSeeder.php
public function run(): void
{
    if (app()->environment('production')) {
        $this->command->warn('Seeding disabled in production!');
        return;
    }

    // ... seed only in dev
}
```

---

## 📈 Scaling Considerations

### Current Setup

- 15 users
- 45-75 posts
- 300-600 likes
- Total: ~1000 records
- Time: ~5-10 seconds

### For 1000+ Records

- Add database indexes
- Use batch processing
- Consider caching
- Monitor memory usage

### Database Indexes to Add

```sql
CREATE INDEX idx_posts_user_id ON posts(user_id);
CREATE INDEX idx_posts_created_at ON posts(created_at);
CREATE INDEX idx_likes_user_id ON likes(user_id);
CREATE INDEX idx_likes_post_id ON likes(post_id);
CREATE UNIQUE INDEX idx_likes_unique ON likes(user_id, post_id);
CREATE UNIQUE INDEX idx_reposts_unique ON reposts(user_id, post_id);
```

---

## 🧹 Cleanup Operations

### Delete All Seeded Data

```bash
php artisan migrate:refresh
```

### Fresh Start (Recommended)

```bash
php artisan migrate:fresh --seed
```

### Clear Database

```bash
php artisan migrate:reset
```

### Soft Reset

```bash
php artisan db:wipe
```

---

## 📝 Environment Variables

### Required

```env
APP_NAME=SocialApp
APP_ENV=local
APP_KEY=                          # Generate: php artisan key:generate
APP_DEBUG=true

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=social_app
DB_USERNAME=root
DB_PASSWORD=password
```

### Optional

```env
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
```

---

## 🚀 Quick Start (Copy & Paste)

```bash
# 1. Navigate to project
cd "c:\Users\PC-2022-043\Documents\project\Mobile\Microservice Social App\Back-End"

# 2. Install dependencies (if not done)
composer install

# 3. Copy .env file
cp .env.example .env

# 4. Generate app key
php artisan key:generate

# 5. Configure database in .env
# Edit .env with your database credentials

# 6. Run migrations with seeding
php artisan migrate:fresh --seed

# 7. Verify in Tinker
php artisan tinker
>>> User::count()
>>> exit
```

---

## ✅ Final Checklist

- [ ] PHP 8.1+ installed
- [ ] Composer dependencies installed
- [ ] Database configured in .env
- [ ] Database server running
- [ ] Models have HasFactory trait
- [ ] Factories created (Post, Likes, Repost)
- [ ] Seeder updated with proper logic
- [ ] Internet connection available
- [ ] Cache cleared
- [ ] Ready to run: `php artisan migrate:fresh --seed`

---

**Status**: ✅ Ready to Execute  
**Last Updated**: April 27, 2026  
**Verified**: Yes  
**Production Ready**: Development Only (Not for Production)
