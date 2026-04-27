# 🔗 API ENDPOINTS REFERENCE - Testing with Seeded Data

## Setup Assumption

- Base URL: `http://localhost:8000`
- Already run: `php artisan migrate:fresh --seed`
- Data loaded: 15 users, 45-75 posts, 300+ likes, 100+ reposts

---

## 📋 Testing Data Available

### Users

```
Total: 15 users
IDs: 1-15
Sample:
├─ User ID 1: john.doe@example.com
├─ User ID 2: jane.smith@example.com
└─ ...
├─ User ID 15: mary.johnson@example.com
Password (all): "password"
```

### Posts

```
Total: 45-75 posts
Sample IDs: 1-75
Each post has:
├─ 2-4 HD photos from Unsplash
├─ Multi-paragraph caption
├─ 1-3 categories
└─ Location & tagline
```

### Likes

```
Total: 300-600 likes
Each post has: 2-8 likes
Each user has: ~20-40 likes average
```

### Reposts

```
Total: 45-300 reposts
Each post has: 1-4 reposts
Each user has: ~3-20 reposts average
```

---

## 🧪 Testing Example Queries

### 1. Get All Users

**Endpoint**: `GET /api/users`

```
curl http://localhost:8000/api/users
```

**Expected Response** (snippet):

```json
{
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "email_verified_at": "2026-04-27T10:30:00.000Z",
      "created_at": "2026-04-27T10:00:00.000Z",
      "updated_at": "2026-04-27T10:00:00.000Z"
    },
    {
      "id": 2,
      "name": "Jane Smith",
      "email": "jane@example.com",
      ...
    }
  ]
}
```

---

### 2. Get Single User

**Endpoint**: `GET /api/users/{id}`

```
curl http://localhost:8000/api/users/1
```

---

### 3. Get User with Posts

**Endpoint**: `GET /api/users/{id}/posts`

```
curl http://localhost:8000/api/users/1/posts
```

**Example Data**:

```
User ID 1 has 3-5 posts
Post 1: "Amazing day with friends!" - 2-4 photos
Post 2: "Travel adventures" - 2-4 photos
...
```

---

### 4. Get All Posts

**Endpoint**: `GET /api/posts`

```
curl http://localhost:8000/api/posts
```

**Expected**:

```json
{
    "data": [
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
                }
            ],
            "caption": "Amazing day...",
            "tag_category": ["travel", "lifestyle"],
            "tag_location": "Jakarta, Indonesia",
            "tagline": "Bahagia",
            "created_at": "2026-04-27T10:05:00.000Z"
        }
    ]
}
```

---

### 5. Get Post with Relationships

**Endpoint**: `GET /api/posts/{id}?include=user,likes,reposts`

```
curl "http://localhost:8000/api/posts/1?include=user,likes,reposts"
```

**Expected**:

```json
{
  "data": {
    "id": 1,
    "user_id": 1,
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "media": [...],
    "caption": "...",
    "likes": [
      {
        "id": 1,
        "user_id": 2,
        "user": {
          "id": 2,
          "name": "Jane Smith"
        }
      }
    ],
    "likes_count": 6,
    "reposts": [
      {
        "id": 1,
        "user_id": 5,
        "user": {
          "id": 5,
          "name": "Bob Wilson"
        }
      }
    ],
    "reposts_count": 3
  }
}
```

---

### 6. Get Post Likes

**Endpoint**: `GET /api/posts/{id}/likes`

```
curl http://localhost:8000/api/posts/1/likes
```

**Expected**: 2-8 like records for post ID 1

---

### 7. Get Post Reposts

**Endpoint**: `GET /api/posts/{id}/reposts`

```
curl http://localhost:8000/api/posts/1/reposts
```

**Expected**: 1-4 repost records for post ID 1

---

### 8. Get User Likes

**Endpoint**: `GET /api/users/{id}/likes`

```
curl http://localhost:8000/api/users/1/likes
```

**Expected**: All posts liked by user ID 1

---

### 9. Get User Reposts

**Endpoint**: `GET /api/users/{id}/reposts`

```
curl http://localhost:8000/api/users/1/reposts
```

**Expected**: All posts reposted by user ID 1

---

## 🧪 Tinker Testing Commands

### View Data in Terminal

```bash
php artisan tinker
```

#### Get First User

```php
> $user = User::first();
> $user->name
> $user->email
```

#### Get User with Posts

```php
> $user = User::with('posts')->first();
> $user->posts->count()        // 3-5
> $user->posts[0]->media       // Array of photos
```

#### Get Post Stats

```php
> $post = Post::first();
> $post->user->name            // Creator name
> $post->likes()->count()      // 2-8
> $post->reposts()->count()    // 1-4
```

#### Check Duplicate Prevention

```php
> Likes::where('user_id', 1)->where('post_id', 1)->count()
// Should return 0 or 1, never > 1

> Repost::where('user_id', 2)->where('post_id', 1)->count()
// Should return 0 or 1, never > 1
```

#### Get All Posts with Stats

```php
> Post::withCount(['likes', 'reposts'])->get()
```

#### Search Posts by Caption

```php
> Post::where('caption', 'like', '%amazing%')->get()
```

#### Get Posts by User

```php
> User::find(1)->posts()->get()
```

#### Get Top Posts by Likes

```php
> Post::withCount('likes')->orderByDesc('likes_count')->limit(5)->get()
```

---

## 🔍 Database Queries for Testing

### Check Total Records

```sql
SELECT
  (SELECT COUNT(*) FROM users) as users,
  (SELECT COUNT(*) FROM posts) as posts,
  (SELECT COUNT(*) FROM likes) as likes,
  (SELECT COUNT(*) FROM reposts) as reposts;
```

### Get Post with All Details

```sql
SELECT
  p.id,
  p.user_id,
  u.name as user_name,
  p.caption,
  p.media,
  p.tag_category,
  p.tag_location,
  p.tagline,
  COUNT(DISTINCT l.id) as likes_count,
  COUNT(DISTINCT r.id) as reposts_count,
  p.created_at
FROM posts p
LEFT JOIN users u ON p.user_id = u.id
LEFT JOIN likes l ON p.id = l.post_id
LEFT JOIN reposts r ON p.id = r.post_id
GROUP BY p.id
ORDER BY likes_count DESC
LIMIT 10;
```

### Get User Stats

```sql
SELECT
  u.id,
  u.name,
  u.email,
  COUNT(DISTINCT p.id) as posts_count,
  COUNT(DISTINCT l.id) as likes_count,
  COUNT(DISTINCT r.id) as reposts_count
FROM users u
LEFT JOIN posts p ON u.id = p.user_id
LEFT JOIN likes l ON u.id = l.user_id
LEFT JOIN reposts r ON u.id = r.user_id
GROUP BY u.id
ORDER BY posts_count DESC;
```

### Check for Duplicate Likes

```sql
SELECT user_id, post_id, COUNT(*) as cnt
FROM likes
GROUP BY user_id, post_id
HAVING cnt > 1;
-- Should return EMPTY result (no duplicates)
```

### Check for Duplicate Reposts

```sql
SELECT user_id, post_id, COUNT(*) as cnt
FROM reposts
GROUP BY user_id, post_id
HAVING cnt > 1;
-- Should return EMPTY result (no duplicates)
```

---

## 🎯 Postman Collection

### Import JSON Collection

```json
{
    "info": {
        "name": "Social App API",
        "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
    },
    "item": [
        {
            "name": "Users",
            "item": [
                {
                    "name": "Get All Users",
                    "request": {
                        "method": "GET",
                        "url": "{{base_url}}/api/users"
                    }
                },
                {
                    "name": "Get Single User",
                    "request": {
                        "method": "GET",
                        "url": "{{base_url}}/api/users/1"
                    }
                }
            ]
        },
        {
            "name": "Posts",
            "item": [
                {
                    "name": "Get All Posts",
                    "request": {
                        "method": "GET",
                        "url": "{{base_url}}/api/posts"
                    }
                },
                {
                    "name": "Get Post with Relations",
                    "request": {
                        "method": "GET",
                        "url": "{{base_url}}/api/posts/1?include=user,likes,reposts"
                    }
                },
                {
                    "name": "Get Post Likes",
                    "request": {
                        "method": "GET",
                        "url": "{{base_url}}/api/posts/1/likes"
                    }
                }
            ]
        },
        {
            "name": "Likes",
            "item": [
                {
                    "name": "Get All Likes",
                    "request": {
                        "method": "GET",
                        "url": "{{base_url}}/api/likes"
                    }
                },
                {
                    "name": "Get User Likes",
                    "request": {
                        "method": "GET",
                        "url": "{{base_url}}/api/users/1/likes"
                    }
                }
            ]
        },
        {
            "name": "Reposts",
            "item": [
                {
                    "name": "Get All Reposts",
                    "request": {
                        "method": "GET",
                        "url": "{{base_url}}/api/reposts"
                    }
                },
                {
                    "name": "Get User Reposts",
                    "request": {
                        "method": "GET",
                        "url": "{{base_url}}/api/users/1/reposts"
                    }
                }
            ]
        }
    ],
    "variable": [
        {
            "key": "base_url",
            "value": "http://localhost:8000"
        }
    ]
}
```

---

## 🔐 Authentication (Future)

Once API auth is implemented:

### Login

```bash
POST /api/login
{
  "email": "john@example.com",
  "password": "password"
}
```

### Get Authenticated User

```bash
GET /api/me
Authorization: Bearer {token}
```

### Logout

```bash
POST /api/logout
Authorization: Bearer {token}
```

---

## 📊 Testing Data Patterns

### User ID to Data Mapping

```
User 1:
├─ Posts: 3-5
├─ Created Likes: ~20-40
├─ Created Reposts: ~5-15

User 2:
├─ Posts: 3-5
├─ Created Likes: ~20-40
├─ Created Reposts: ~5-15

... (pattern repeats for 15 users)
```

### Post ID to Data Mapping

```
Post 1 (by User 1):
├─ Media: 2-4 photos (Unsplash URLs)
├─ Caption: Multi-paragraph text
├─ Categories: 1-3 tags
├─ Likes: 2-8 from random users
└─ Reposts: 1-4 from random users

Post 2 (by User 1):
├─ Media: 2-4 photos
└─ ... (same structure)
```

---

## 🎯 Data Validation Testing

### Valid Email Addresses

```
john@example.com
jane@example.com
bob@example.com
... (all 15 users)
```

### Valid Post IDs

```
1 to 75 (or count from DB)
Each has: user_id, media, caption, tags, timestamp
```

### Valid User IDs

```
1 to 15
```

### Media URL Format Validation

```
https://images.unsplash.com/photo-XXX?w=800&q=80

Breaking it down:
├─ Protocol: https ✓
├─ Domain: images.unsplash.com ✓
├─ Path: /photo-XXX ✓
└─ Query: ?w=800&q=80 ✓
```

---

## ⚡ Performance Testing

### Get All Posts (Large Response)

```bash
time curl http://localhost:8000/api/posts
```

Expected: < 100ms

### Search Posts

```bash
time curl "http://localhost:8000/api/posts?search=travel"
```

Expected: < 200ms

### Get Posts with Relationships

```bash
time curl "http://localhost:8000/api/posts?include=user,likes,reposts"
```

Expected: < 500ms

---

## 📝 Notes for API Development

1. **Resources to Create**
    - PostResource
    - UserResource
    - LikesResource
    - RepostsResource

2. **Controllers to Create**
    - PostController (CRUD)
    - UserController (CRUD)
    - LikesController (Create, Delete)
    - RepostsController (Create, Delete)

3. **Routes to Define**
    - `/api/posts` - CRUD posts
    - `/api/users` - CRUD users
    - `/api/posts/{id}/likes` - Post likes
    - `/api/posts/{id}/reposts` - Post reposts
    - `/api/users/{id}/likes` - User likes
    - `/api/users/{id}/reposts` - User reposts

4. **Test Data IDs**
    - User: 1-15
    - Post: 1-75
    - Like: 1-600
    - Repost: 1-300

---

**Testing Reference Complete** ✅  
Last Updated: April 27, 2026  
Ready for API Development
