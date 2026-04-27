# 📚 Seeding & Factory Best Practices - Social App API

## Overview
Dokumentasi lengkap tentang struktur data dummy, factory, dan seeding strategy untuk Laravel API.

---

## 🏗️ Architecture Hierarchy

```
Users (15)
    ├── Posts (3-5 per user = 45-75 total)
    │   ├── Likes (2-8 per post)
    │   └── Reposts (1-4 per post)
```

---

## 1️⃣ User Model & Factory

### UserFactory (`database/factories/UserFactory.php`)
```php
✓ Menggunakan Faker untuk generate data realistik
✓ Email unique dengan `.safeEmail()`
✓ Password hashed otomatis (HasFactory akan handle)
✓ Email verified at di-set ke now()
✓ Remember token random string 10 chars
```

**Data yang dihasilkan:**
- Name: Faker generated (realistic names)
- Email: faker@example.com (unique)
- Password: "password" (hashed)
- Email verified: Timestamp otomatis

---

## 2️⃣ Post Model & Factory

### PostFactory (`database/factories/PostFactory.php`)

#### Media Structure (BEST PRACTICE)
```php
'media' => [
    [
        'type' => 'photo',           // Tipe: photo only (no video)
        'url' => 'https://images.unsplash.com/...?w=800&q=80',  // HD 800px, 80% quality
        'quality' => 'HD',            // Metadata kualitas
        'width' => 800,               // Dimensi
        'height' => 600,              // Dimensi
    ],
    [
        'type' => 'photo',
        'url' => 'https://images.unsplash.com/...?w=800&q=80',
        'quality' => 'HD',
        'width' => 800,
        'height' => 600,
    ]
]
```

**Mengapa HD URLs dari Unsplash?**
- ✅ Free, legal, high-quality images
- ✅ Reliable CDN (tidak akan broken)
- ✅ Real URLs (bukan placeholder)
- ✅ Beragam kategori (nature, tech, food, dll)
- ✅ Format: `?w=800&q=80` = responsive & optimized

**Multiple Photos per Post:**
- 2-4 foto per post untuk realism
- `$photoCount = rand(2, 4);` - variation
- Loop untuk generate array of photos

**Caption & Tags:**
```php
'caption' => $this->faker->paragraph(2-4)  // Multi-line realistic caption
'tag_category' => randomElements([...])    // 1-3 kategori unik
'tag_location' => 'City, Country'          // Format standard
'tagline' => TaglineType enum value        // Dari TaglineType class
```

---

## 3️⃣ Likes Model & Factory

### LikesFactory (`database/factories/LikesFactory.php`)

**Structure:**
```php
'user_id' => User::factory(),     // Foreign key
'post_id' => Post::factory();     // Foreign key
```

**Helper Method:**
```php
public function forUserAndPost(User $user, Post $post): static
{
    return $this->state(fn(array $attributes) => [
        'user_id' => $user->id,
        'post_id' => $post->id,
    ]);
}
```

**Kenapa penting?**
- Memudahkan seeder untuk assign specific user & post
- Mencegah duplicate likes
- Type-safe dengan explicit parameters

---

## 4️⃣ Reposts Model & Factory

### RepostFactory (`database/factories/RepostFactory.php`)

**Same pattern seperti LikesFactory:**
```php
'user_id' => User::factory();
'post_id' => Post::factory();
```

---

## 5️⃣ DatabaseSeeder - ORCHESTRATION

### Seeding Strategy (4 Steps)

#### Step 1: Create Users
```php
$users = User::factory(15)->create();
// Result: 15 users dengan unique emails
```

#### Step 2: Create Posts
```php
$allPosts = collect();

foreach ($users as $user) {
    $postCount = rand(3, 5);
    $userPosts = Post::factory($postCount)->forUser($user)->create();
    $allPosts = $allPosts->merge($userPosts);
}
// Result: 45-75 posts, setiap user owns 3-5 posts
```

#### Step 3: Create Likes
```php
foreach ($allPosts as $post) {
    $likeCount = rand(2, 8);
    $usersForLike = $users->random(min($likeCount, $users->count()))->unique('id');
    
    foreach ($usersForLike as $user) {
        // PREVENT DUPLICATE: Check if like already exists
        $likeExists = Likes::where('user_id', $user->id)
            ->where('post_id', $post->id)
            ->exists();
        
        if (!$likeExists) {
            Likes::factory()->forUserAndPost($user, $post)->create();
        }
    }
}
// Result: 2-8 likes per post, no duplicates
```

#### Step 4: Create Reposts
```php
// Same pattern seperti likes
// Result: 1-4 reposts per post, no duplicates
```

---

## ✨ BEST PRACTICES DITERAPKAN

### 1. Model Relationships
```php
// User.php
public function posts(): HasMany { ... }
public function likes(): HasMany { ... }
public function reposts(): HasMany { ... }

// Post.php
public function user(): BelongsTo { ... }
public function likes(): HasMany { ... }
public function reposts(): HasMany { ... }

// Likes.php & Repost.php
public function user(): BelongsTo { ... }
public function post(): BelongsTo { ... }
```
✅ Lengkap, type-hinted, bidirectional

### 2. HasFactory Traits
```php
class Post extends Model
{
    use HasFactory;  // ✅ REQUIRED untuk factory bekerja
}
```
✅ Semua models punya trait ini

### 3. Data Type Validation
- `media` → `array` (JSON)
- `caption` → `string` (nullable)
- `tag_category` → `array` (nullable)
- `tag_location` → `string` (nullable)
- `tagline` → Enum cast to `TaglineType`

### 4. Prevent Duplicates
```php
$exists = Model::where('column', $value)->exists();
if (!$exists) {
    // Create new
}
```
✅ Ensures data integrity

### 5. Realistic Data
- HD images dari Unsplash (not placeholder)
- Multiple media per post (not just 1)
- Varied categories & locations
- Proper timestamps & relationships

### 6. Error Handling
- `->unique('id')` untuk prevent duplicate users dalam single selection
- `min($likeCount, $users->count())` untuk safety
- Duplicate checking sebelum insert

### 7. Output Logging
```php
$this->command->info("✓ Created X items");
```
✅ Clear, actionable feedback

---

## 🚀 Cara Menggunakan

### Run Seeding
```bash
php artisan migrate:fresh --seed
```

### Expected Output
```
✓ Created 15 users
✓ Created 52 posts
✓ Created 298 likes
✓ Created 152 reposts
═══════════════════════════════════════
   Database Seeding Completed! ✓
═══════════════════════════════════════
📊 Users:       15
📝 Posts:       52
❤️  Likes:       298
🔄 Reposts:     152
═══════════════════════════════════════
```

---

## 📊 Data Statistics

### Per Run
| Entity | Count | Range |
|--------|-------|-------|
| Users | 15 | Fixed |
| Posts | 45-75 | 3-5 per user |
| Likes | ~300-600 | 2-8 per post |
| Reposts | ~45-300 | 1-4 per post |

### Total Relationships
- 15 users created
- ~60 posts created
- ~450 likes created
- ~150 reposts created
- **Total: 675 records** ✅

---

## 📷 Media Quality Spec

### Photo URLs
- **Source**: Unsplash (free, legal)
- **Dimensions**: 800x600px
- **Quality**: 80% compression (`?q=80`)
- **Types**: Nature, Tech, Food, Lifestyle, Sports, Fashion, etc.
- **Count per Post**: 2-4 photos
- **Format**: JSON array with metadata

### Example Media Object
```json
{
  "type": "photo",
  "url": "https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&q=80",
  "quality": "HD",
  "width": 800,
  "height": 600
}
```

---

## 🔧 Customization

### Untuk Menambah Jumlah Data:
```php
// DatabaseSeeder.php
$users = User::factory(50)->create();  // Instead of 15
```

### Untuk Ubah Photo URLs:
```php
// PostFactory.php
$hdPhotoUrls = [
    'your-custom-url-1',
    'your-custom-url-2',
    ...
];
```

### Untuk Ubah Post Count per User:
```php
$postCount = rand(5, 10);  // Instead of 3-5
```

---

## ✅ Quality Checklist

- [x] Semua models punya `HasFactory` trait
- [x] Factories follow Laravel conventions
- [x] Seeder orchestration proper hierarchy
- [x] Duplicate prevention logic implemented
- [x] Real HD images dari reliable source
- [x] Multiple media per post
- [x] Proper data type validation
- [x] Relationships bidirectional
- [x] Output logging clear & helpful
- [x] No hardcoded IDs

---

## 📝 Notes

- Password default: `"password"` (untuk testing)
- Email bisa login dengan semua accounts
- Media URL real dan accessible
- Timestamps auto-generated oleh Laravel
- Soft deletes: tidak digunakan (optional)

---

**Created**: April 27, 2026  
**Version**: 1.0  
**Status**: Production Ready ✅
