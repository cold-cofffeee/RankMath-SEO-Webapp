# 🤖 Setting Up Real AI Features

## Google Gemini API Setup (For Content AI)

### Step 1: Get Gemini API Key

1. Go to [Google AI Studio](https://makersuite.google.com/app/apikey)
2. Click "Get API Key" or "Create API Key"
3. Copy your API key (starts with `AIza...`)

### Step 2: Configure API Key Securely

1. Open: `c:\xampp\htdocs\rankmath\config\api-keys.php`
2. Add your Gemini API key:

```php
return [
    'gemini' => [
        'api_key' => 'AIzaSy...YOUR_KEY_HERE',  // ⚠️ Add your real key
        'model' => 'gemini-2.0-flash-exp',
        'endpoint' => 'https://generativelanguage.googleapis.com/v1beta/models',
    ],
];
```

3. Save the file

### Step 3: Test Content AI

1. Go to: `http://localhost/rankmath/content-ai`
2. Enter a keyword: "digital marketing"
3. Select content type: "Paragraph"
4. Click "Generate Content"
5. **You'll now see REAL AI-generated content!** ✨

---

## What Happens:

### ✅ With API Key Configured:
- **Real AI Generation** using Google Gemini 2.0
- Unique, creative content every time
- Professional, contextual writing
- Response includes: `"powered_by": "Google Gemini AI"`

### ⚠️ Without API Key:
- Falls back to template-based generation
- Uses predefined templates
- Response includes: `"powered_by": "Templates"`

---

## Content Types Supported

All content types now use real AI prompts:

| Type | What It Generates |
|------|-------------------|
| **Title** | SEO-optimized titles (under 60 chars) |
| **Paragraph** | Full informative paragraph (150-200 words) |
| **Meta Description** | SEO meta description (120-160 chars) |
| **Heading** | H2/H3 headings |
| **Conclusion** | Conclusion paragraph with CTA |

---

## Keyword Research - Now Uses Real Data!

### What Changed:

**Before:**
```php
'search_volume' => rand(100, 10000)  // ❌ Fake random numbers
```

**Now:**
```php
'search_volume' => $realData['impressions']  // ✅ Real from database
```

### How It Works:

1. **Searches your analytics database** for keyword data
2. **Returns actual impressions, clicks, position, CTR**
3. **Finds related keywords** from your real analytics
4. **Shows data source** (Real Data or No Data)

### Example Real Response:

```json
{
  "keyword": "seo optimization",
  "search_volume": 1567029,      // ✅ Real from your database
  "clicks": 112964,               // ✅ Real clicks
  "position": 3.9,                // ✅ Real average position
  "ctr": 7.2,                     // ✅ Real CTR
  "data_source": "Analytics Database (Real Data)",
  "related_keywords": [
    "seo optimization tips",     // ✅ From your real analytics
    "on page seo optimization"
  ]
}
```

---

## Security Features

### ✅ API Key Protection:
- Stored in `config/api-keys.php` (outside web root when deployed)
- **Never sent to frontend** (all API calls from backend)
- Should be added to `.gitignore` (already configured)
- Not exposed in responses or logs

### ✅ Error Handling:
- Falls back to templates if API fails
- Logs errors without exposing sensitive info
- 30-second timeout prevents hanging

---

## Cost & Limits

### Google Gemini API:
- **Free Tier:** 15 requests per minute
- **Free Tier:** 1,500 requests per day
- **Paid Tier:** 360 requests per minute

### Your Setup:
- Each content generation = 1 API call
- Database stores `credits_used: 1` for real AI
- Template fallback uses `credits_used: 0`

---

## Testing Examples

### Test 1: Title Generation
```
Keyword: "artificial intelligence"
Type: Title
Result: "Artificial Intelligence: The Ultimate Guide to AI in 2026"
```

### Test 2: Meta Description
```
Keyword: "content marketing"
Type: Meta Description
Result: "Discover content marketing strategies that drive results. Learn expert tips, 
best practices, and proven tactics. Start your journey today!"
```

### Test 3: Keyword Research
```
GET /api/content-ai/research?keyword=seo
Result: Real impressions, clicks, position from your analytics database
```

---

## Troubleshooting

### Issue: Still seeing template content
**Solution:** Check if API key is correctly set in `config/api-keys.php`

### Issue: "API Error"
**Solutions:**
1. Check API key is valid
2. Verify internet connection
3. Check Gemini API quota (free tier limits)

### Issue: No keyword data
**Solution:** Add keywords to analytics first by:
1. Running SEO analyses
2. Tracking pages
3. Waiting for data to accumulate (or use sample data)

---

## Production Deployment

### When Moving to Live Server:

1. **Copy `config/api-keys.php` manually** (not in Git)
2. Add production API key
3. Verify `.gitignore` includes `api-keys.php`
4. Test API connectivity from server

### Environment-Specific Config:

For multiple environments, you can create:
- `api-keys.local.php` (local development)
- `api-keys.staging.php` (staging server)
- `api-keys.production.php` (live server)

And load the appropriate one based on environment.

---

## 🎉 You're All Set!

Your Content AI now uses **real Google Gemini AI** for professional content generation, and Keyword Research uses **real analytics data** from your database!

**No more fake data!** 🚀
