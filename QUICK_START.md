# 🚀 QUICK START GUIDE

## Run Seeding (One Command!)
```bash
cd "c:\Users\PC-2022-043\Documents\project\Mobile\Microservice Social App\Back-End"
php artisan migrate:fresh --seed
```

## ✅ What Gets Created
- **15 Users** - with unique emails, hashed passwords
- **45-75 Posts** - with 2-4 HD photos each, varied content
- **300-600 Likes** - distributed across posts (2-8 per post)
- **45-300 Reposts** - distributed across posts (1-4 per post)

---

## 📁 Files Updated

### Models (Added `HasFactory` trait)
- ✅ [app/Models/User.php](app/Models/User.php) - + relationships
- ✅ [app/Models/Post.php](app/Models/Post.php) - + HasFactory
- ✅ [app/Models/Likes.php](app/Models/Likes.php) - + HasFactory
- ✅ [app/Models/Repost.php](app/Models/Repost.php) - + HasFactory

### Factories (Created/Updated)
- ✅ [database/factories/UserFactory.php](database/factories/UserFactory.php) - Already existed (enhanced)
- ✅ [database/factories/PostFactory.php](database/factories/PostFactory.php) - **NEW** (HD photos only)
- ✅ [database/factories/LikesFactory.php](database/factories/LikesFactory.php) - **NEW**
- ✅ [database/factories/RepostFactory.php](database/factories/RepostFactory.php) - **NEW**

### Seeder
- ✅ [database/seeders/DatabaseSeeder.php](database/seeders/DatabaseSeeder.php) - **COMPLETE REWRITE** (proper hierarchy)

### Documentation
- 📖 [SEEDING_BEST_PRACTICES.md](SEEDING_BEST_PRACTICES.md) - Full documentation
- 📖 [DATABASE_SCHEMA.md](DATABASE_SCHEMA.md) - Schema reference
- 📖 [QUICK_START.md](QUICK_START.md) - This file

---

## 🎯 Key Features

### ✨ HD Photos Only
- **Source**: Unsplash (free, legal, reliable)
- **Quality**: 800x600px @ 80% compression
- **Count**: 2-4 photos per post
- **No Videos**: Clean, simple, focused
- **Real URLs**: Not placeholders!

### 🔗 Proper Relationships
```
User (1) ──→ (M) Posts
User (1) ──→ (M) Likes
User (1) ──→ (M) Reposts
Post (1) ──→ (M) Likes
Post (1) ──→ (M) Reposts
```

### 🛡️ Data Integrity
- Cascade deletes configured
- Foreign keys enforced
- Duplicate prevention (can't like same post twice)
- Unique email per user

### 📊 Realistic Data
- Faker-generated user names
- Multi-paragraph captions
- Varied categories & locations
- Proper timestamps

---

## 💾 Database Structure

### User Table
```
id | name | email | password | email_verified_at | remember_token
```

### Posts Table
```
id | user_id | media[JSON] | caption | tag_category[JSON] | tag_location | tagline
```

### Likes Table
```
id | user_id | post_id
```

### Reposts Table
```
id | user_id | post_id
```

---

## 🔍 Example Data

### User
```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "email_verified_at": "2026-04-27 10:30:00",
  "password": "$2y$12$...",
  "remember_token": "abc123"
}
```

### Post
```json
{
  "id": 1,
  "user_id": 1,
  "media": [
    {
      "type": "photo",
      "url": "https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&q=80",
      "quality": "HD",
      "width": 800,
      "height": 600
    },
    {
      "type": "photo",
      "url": "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=800&q=80",
      "quality": "HD",
      "width": 800,
      "height": 600
    }
  ],
  "caption": "Amazing day with friends! Lorem ipsum...",
  "tag_category": ["travel", "lifestyle", "photography"],
  "tag_location": "Jakarta, Indonesia",
  "tagline": "Bahagia"
}
```

### Like
```json
{
  "id": 1,
  "user_id": 2,
  "post_id": 1
}
```

### Repost
```json
{
  "id": 1,
  "user_id": 3,
  "post_id": 1
}
```

---

## 🧪 Testing

### Get all users
```bash
php artisan tinker
> User::all();
```

### Get user with posts
```bash
> User::with('posts')->find(1);
```

### Get post with likes
```bash
> Post::with('likes')->find(1);
```

### Count likes
```bash
> Post::find(1)->likes()->count();
```

### Check relationships
```bash
> $post = Post::find(1);
> $post->user->name;         // Creator
> $post->likes->count();     // Total likes
> $post->reposts->count();  // Total reposts
```

---

## 🎯 Best Practices Applied

✅ **Factory Pattern** - Reusable data generation  
✅ **HasFactory Trait** - Model integration  
✅ **Relationships** - Proper ORM setup  
✅ **Cascade Deletes** - Data integrity  
✅ **Duplicate Prevention** - Business logic  
✅ **Realistic Data** - Faker + Unsplash URLs  
✅ **Type Hinting** - Strict types  
✅ **Clear Logging** - Seeding feedback  
✅ **Documentation** - Comprehensive guides  
✅ **Scalability** - Ready for production  

---

## 📝 Troubleshooting

### Error: "Call to undefined method App\Models\Post::factory()"
**Solution**: Make sure `HasFactory` trait is added to Post model
```php
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use HasFactory;  // ✅ Add this
}
```

### Error: "Undefined method 'likes'"
**Solution**: Verify relationships are added to Post model
```php
public function likes() : HasMany{
    return $this->hasMany(Likes::class, 'post_id');
}
```

### Seeding takes too long
**Solution**: Reduce number of users in DatabaseSeeder
```php
$users = User::factory(5)->create();  // Instead of 15
```

### Photos not loading
**Solution**: Check internet connection (Unsplash URLs need CDN access)

---

## 🚀 Next Steps

1. **Run Seeding**
   ```bash
   php artisan migrate:fresh --seed
   ```

2. **Test with Tinker**
   ```bash
   php artisan tinker
   > Post::with('user', 'likes')->limit(5)->get();
   ```

3. **Create API Endpoints** (using resources)
   ```bash
   php artisan make:controller Api/PostController --resource
   ```

4. **Test with Postman/Insomnia**
   ```
   GET http://localhost:8000/api/posts
   GET http://localhost:8000/api/users/1/posts
   ```

---

## 📞 Support

For issues or questions, check:
1. [SEEDING_BEST_PRACTICES.md](SEEDING_BEST_PRACTICES.md) - Detailed explanation
2. [DATABASE_SCHEMA.md](DATABASE_SCHEMA.md) - Schema reference
3. Laravel Docs: https://laravel.com/docs/11/eloquent

---

**Last Updated**: April 27, 2026  
**Version**: 1.0  
**Status**: ✅ Ready to Use!
