# 📷 Media JSON Schema Documentation

## Updated Media Structure (NEW!)

### Complete Media Object Schema

```typescript
interface MediaObject {
    uri: string; // Full URL to the media file
    name: string; // Original filename
    type?: string; // Media type (e.g., 'photo', 'video')
    size?: number; // File size in bytes
    mimeType?: string; // MIME type (e.g., 'image/jpeg')
}
```

---

## 📋 Field Descriptions

| Field      | Type   | Required | Description                      | Example                                            |
| ---------- | ------ | -------- | -------------------------------- | -------------------------------------------------- |
| `uri`      | string | ✅ YES   | Full URL to media file           | `https://images.unsplash.com/photo-xxx?w=800&q=80` |
| `name`     | string | ✅ YES   | Original filename with extension | `photo_a1b2c3d4.jpg`                               |
| `type`     | string | ❌ NO    | Media classification             | `photo`                                            |
| `size`     | number | ❌ NO    | File size in bytes               | `125000`                                           |
| `mimeType` | string | ❌ NO    | MIME type of file                | `image/jpeg`                                       |

---

## 💾 Example Media JSON (Full Post)

### Single Media Object

```json
{
    "uri": "https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&q=80",
    "name": "photo_a1b2c3d4-e5f6-47g8-h9i0.jpg",
    "type": "photo",
    "size": 125000,
    "mimeType": "image/jpeg"
}
```

### Complete Post with Media Array (2-4 photos)

```json
{
    "id": 1,
    "user_id": 1,
    "media": [
        {
            "uri": "https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&q=80",
            "name": "photo_uuid1.jpg",
            "type": "photo",
            "size": 125000,
            "mimeType": "image/jpeg"
        },
        {
            "uri": "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=800&q=80",
            "name": "photo_uuid2.png",
            "type": "photo",
            "size": 234000,
            "mimeType": "image/png"
        },
        {
            "uri": "https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?w=800&q=80",
            "name": "photo_uuid3.webp",
            "type": "photo",
            "size": 98000,
            "mimeType": "image/webp"
        }
    ],
    "caption": "Amazing day with friends and beautiful scenery...",
    "tag_category": ["travel", "lifestyle", "nature"],
    "tag_location": "Jakarta, Indonesia",
    "tagline": "Bahagia",
    "created_at": "2026-04-27T10:00:00Z",
    "updated_at": "2026-04-27T10:00:00Z"
}
```

---

## 📤 API Request Example

### POST /api/posts (Create Post)

**Request Body:**

```json
{
    "media": [
        {
            "uri": "https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&q=80",
            "name": "mountain_view.jpg",
            "type": "photo",
            "size": 125000,
            "mimeType": "image/jpeg"
        },
        {
            "uri": "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=800&q=80",
            "name": "portrait_shot.png",
            "type": "photo",
            "size": 234000,
            "mimeType": "image/png"
        }
    ],
    "caption": "Beautiful moments captured today!",
    "tag_category": ["photography", "nature"],
    "tag_location": "Bandung, Indonesia",
    "tagline": "Bahagia"
}
```

**Response (201 Created):**

```json
{
    "data": {
        "id": 1,
        "user_id": 1,
        "media": [
            {
                "uri": "https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&q=80",
                "name": "mountain_view.jpg",
                "type": "photo",
                "size": 125000,
                "mimeType": "image/jpeg"
            },
            {
                "uri": "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=800&q=80",
                "name": "portrait_shot.png",
                "type": "photo",
                "size": 234000,
                "mimeType": "image/png"
            }
        ],
        "caption": "Beautiful moments captured today!",
        "tag_category": ["photography", "nature"],
        "tag_location": "Bandung, Indonesia",
        "tagline": "Bahagia",
        "created_at": "2026-04-27T10:30:00Z",
        "updated_at": "2026-04-27T10:30:00Z"
    },
    "success": true,
    "message": "Post created successfully!"
}
```

---

## 🎯 MIME Types Reference

### Supported MIME Types

| Type | Extensions      | Example         |
| ---- | --------------- | --------------- |
| JPEG | `.jpg`, `.jpeg` | `image/jpeg`    |
| PNG  | `.png`          | `image/png`     |
| WebP | `.webp`         | `image/webp`    |
| GIF  | `.gif`          | `image/gif`     |
| SVG  | `.svg`          | `image/svg+xml` |

### File Size Ranges

| Type          | Min Size | Max Size | Notes                |
| ------------- | -------- | -------- | -------------------- |
| **Thumbnail** | 10KB     | 50KB     | Low resolution       |
| **Standard**  | 50KB     | 250KB    | Normal quality       |
| **HD**        | 200KB    | 500KB    | High resolution      |
| **Ultra HD**  | 500KB    | 1MB      | Very high resolution |

---

## 🔍 Database Storage

### posts table - media column

```sql
-- Column type: JSON
-- Storage: Compressed JSON array
-- Example data:
[
  {
    "uri": "https://...",
    "name": "photo_uuid.jpg",
    "type": "photo",
    "size": 125000,
    "mimeType": "image/jpeg"
  }
]
```

---

## ✅ Validation Rules (PostController)

### Server-side Validation

```php
$request->validate([
    'media' => 'array|required',
    'media.*.uri' => 'string|required',           // Must be valid URL
    'media.*.name' => 'string|required',          // Must have filename
    'media.*.type' => 'string|nullable',          // Optional
    'media.*.size' => 'integer|nullable',         // Optional, in bytes
    'media.*.mimeType' => 'string|nullable',      // Optional, standard MIME
]);
```

### Client-side Validation (Frontend)

```javascript
// Check URI is valid URL
const isValidUrl = (uri) => {
    try {
        new URL(uri);
        return true;
    } catch (error) {
        return false;
    }
};

// Check file size
const isValidSize = (size) => {
    const maxSize = 1000000; // 1MB
    return size > 0 && size <= maxSize;
};

// Check MIME type
const validMimeTypes = ["image/jpeg", "image/png", "image/webp", "image/gif"];
const isValidMimeType = (mimeType) => validMimeTypes.includes(mimeType);
```

---

## 🎨 Real World Examples

### Photography Post

```json
{
    "media": [
        {
            "uri": "https://images.unsplash.com/photo-1506126613408-eca07ce68773?w=800&q=80",
            "name": "forest_landscape_01.jpg",
            "type": "photo",
            "size": 182000,
            "mimeType": "image/jpeg"
        },
        {
            "uri": "https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?w=800&q=80",
            "name": "sunset_over_ocean.png",
            "type": "photo",
            "size": 245000,
            "mimeType": "image/png"
        }
    ],
    "caption": "Nature photography showcase - captured during golden hour",
    "tag_category": ["photography", "nature", "landscape"],
    "tag_location": "Bali, Indonesia",
    "tagline": "Bahagia"
}
```

### Food Post

```json
{
    "media": [
        {
            "uri": "https://images.unsplash.com/photo-1459749411175-04bf5292ceea?w=800&q=80",
            "name": "gourmet_dish_001.webp",
            "type": "photo",
            "size": 98000,
            "mimeType": "image/webp"
        },
        {
            "uri": "https://images.unsplash.com/photo-1511379938547-c1f69b13d835?w=800&q=80",
            "name": "coffee_setup_001.jpg",
            "type": "photo",
            "size": 156000,
            "mimeType": "image/jpeg"
        }
    ],
    "caption": "Delicious meal and morning coffee at favorite cafe",
    "tag_category": ["food", "coffee", "lifestyle"],
    "tag_location": "Jakarta, Indonesia",
    "tagline": "Bahagia"
}
```

### Travel Post

```json
{
    "media": [
        {
            "uri": "https://images.unsplash.com/photo-1495521821757-a1efb6729352?w=800&q=80",
            "name": "travel_destination_01.jpg",
            "type": "photo",
            "size": 212000,
            "mimeType": "image/jpeg"
        },
        {
            "uri": "https://images.unsplash.com/photo-1487180144351-b8472da7d491?w=800&q=80",
            "name": "street_view_001.png",
            "type": "photo",
            "size": 178000,
            "mimeType": "image/png"
        },
        {
            "uri": "https://images.unsplash.com/photo-1517457373614-b7152f800fd1?w=800&q=80",
            "name": "architecture_shot.webp",
            "type": "photo",
            "size": 134000,
            "mimeType": "image/webp"
        }
    ],
    "caption": "Exploring new city - amazing architecture and culture!",
    "tag_category": ["travel", "adventure", "photography"],
    "tag_location": "Amsterdam, Netherlands",
    "tagline": "Bersemangat"
}
```

---

## 🔄 Update Post Example

### PUT /api/posts/{id}

**Request Body:**

```json
{
    "media": [
        {
            "uri": "https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&q=80",
            "name": "updated_photo_01.jpg",
            "type": "photo",
            "size": 145000,
            "mimeType": "image/jpeg"
        }
    ],
    "caption": "Updated caption with new information",
    "tag_category": ["updated", "new"],
    "tag_location": "New Location, Country",
    "tagline": "Senang"
}
```

---

## 📱 Mobile/Frontend Integration

### JavaScript/TypeScript

```typescript
interface MediaObject {
    uri: string;
    name: string;
    type?: string;
    size?: number;
    mimeType?: string;
}

interface CreatePostRequest {
    media: MediaObject[];
    caption: string;
    tag_category: string[];
    tag_location: string;
    tagline: string;
}

// Creating post
const createPost = async (data: CreatePostRequest) => {
    const response = await fetch("http://localhost:8000/api/posts", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify(data),
    });

    return response.json();
};

// Usage
const postData: CreatePostRequest = {
    media: [
        {
            uri: "https://images.unsplash.com/...",
            name: "photo.jpg",
            type: "photo",
            size: 125000,
            mimeType: "image/jpeg",
        },
    ],
    caption: "My post",
    tag_category: ["travel"],
    tag_location: "Jakarta",
    tagline: "Bahagia",
};

await createPost(postData);
```

---

## 🔐 Security Notes

### URI Validation

- ✅ Must be HTTPS (not HTTP)
- ✅ Must be from trusted domains (Unsplash, CDN)
- ✅ Validate URL format

### File Size Validation

- ✅ Maximum 1MB recommended
- ✅ Minimum 1KB
- ✅ Check against declared size

### MIME Type Validation

- ✅ Only allow image MIME types
- ✅ Reject suspicious types
- ✅ Validate against file extension

---

## ✨ Best Practices

### 1. Always Include Required Fields

```json
{
    "uri": "required!",
    "name": "required!",
    "type": "optional but recommended",
    "size": "optional but recommended",
    "mimeType": "optional but recommended"
}
```

### 2. Use Descriptive Filenames

```
✅ GOOD: photo_landscape_sunset_2026.jpg
❌ BAD: photo123.jpg
```

### 3. Include Full URL

```
✅ GOOD: https://images.unsplash.com/photo-xxx?w=800&q=80
❌ BAD: /photo-xxx
```

### 4. Provide File Metadata

```
✅ GOOD: Complete with size, mimeType
❌ BAD: Only URI and name
```

---

## 📊 Data Generation (Seeding)

### PostFactory - Media Generation

```php
// Generates 2-4 photos per post with complete metadata
$media[] = [
    'uri' => 'https://images.unsplash.com/...',
    'name' => 'photo_' . $this->faker->uuid() . '.jpg',
    'type' => 'photo',
    'size' => $this->faker->numberBetween(50000, 500000),
    'mimeType' => 'image/jpeg',
];
```

### Sample Generated Data

```json
{
    "uri": "https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&q=80",
    "name": "photo_a1b2c3d4-e5f6-47g8-h9i0.jpg",
    "type": "photo",
    "size": 185234,
    "mimeType": "image/jpeg"
}
```

---

## 📝 Migration/Database Notes

### Column Type

```sql
ALTER TABLE posts ADD media JSON NOT NULL;
```

### Index for Performance

```sql
-- If querying by MIME type or file size
CREATE INDEX idx_media_mimetype ON posts((media->'$.mimeType'));
```

### Backup Considerations

```
- JSON data stored compressedably
- Size: ~500 bytes per media object
- Array of 2-4 = ~1-2KB per post
```

---

**Last Updated**: April 27, 2026  
**Schema Version**: 2.0 (Updated)  
**Status**: ✅ Current & Valid
