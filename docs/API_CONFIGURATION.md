# API Configuration Guide

## Overview

This application uses external APIs for enhanced functionality. This guide explains how to obtain and configure API credentials.

---

## Required APIs

### 1. Google Gemini API (For AI Content Generation)

**Purpose:** Powers the Content AI module for generating titles, descriptions, and content.

**Cost:** Free tier available (1,500 requests/day)

#### How to Get API Key:

1. **Go to Google AI Studio**
   - Visit: https://makersuite.google.com/app/apikey
   - Sign in with your Google account

2. **Create API Key**
   - Click "Get API key" or "Create API key in new project"
   - Choose "Create API key in new project" or select existing project
   - Copy the generated API key (starts with `AIza...`)

3. **Add to Configuration**
   ```php
   'gemini' => [
       'api_key' => 'AIzaSy...YOUR_KEY_HERE',
   ]
   ```

4. **Verify Setup**
   - Test at: `/rankmath/content-ai`
   - Generate content with any keyword
   - Check response includes: `"powered_by": "Google Gemini AI"`

**Limits:**
- Free: 15 requests/min, 1,500 requests/day
- Paid: 360 requests/min

**Documentation:** https://ai.google.dev/docs

---

### 2. Google Search Console API (For Keyword Research)

**Purpose:** Provides real search analytics data (impressions, clicks, positions, CTR).

**Cost:** Free

#### How to Get Credentials:

##### Step 1: Create Google Cloud Project

1. **Go to Google Cloud Console**
   - Visit: https://console.cloud.google.com/
   - Sign in with your Google account

2. **Create New Project**
   - Click "Select a project" → "New Project"
   - Name: "RankMath SEO Webapp"
   - Click "Create"

##### Step 2: Enable Search Console API

1. **Navigate to APIs & Services**
   - From menu → "APIs & Services" → "Library"

2. **Enable Search Console API**
   - Search for "Google Search Console API"
   - Click on it
   - Click "Enable"

##### Step 3: Create OAuth 2.0 Credentials

1. **Configure OAuth Consent Screen**
   - Go to "APIs & Services" → "OAuth consent screen"
   - Choose "External" (for public use) or "Internal" (for organization only)
   - Fill in required fields:
     - App name: "RankMath SEO Webapp"
     - User support email: your email
     - Developer contact: your email
   - Click "Save and Continue"
   - Add scopes: `https://www.googleapis.com/auth/webmasters.readonly`
   - Click "Save and Continue"
   - Add test users (your email)
   - Click "Save and Continue"

2. **Create OAuth Client ID**
   - Go to "APIs & Services" → "Credentials"
   - Click "+ CREATE CREDENTIALS" → "OAuth client ID"
   - Application type: "Web application"
   - Name: "RankMath Webapp"
   - Authorized redirect URIs:
     - Local: `http://localhost/rankmath/api.php/api/google/oauth-callback`
     - Production: `https://yourdomain.com/api.php/api/google/oauth-callback`
   - Click "Create"

3. **Copy Credentials**
   - Copy the "Client ID" (looks like: `123456789-abcdefg.apps.googleusercontent.com`)
   - Copy the "Client Secret" (looks like: `GOCSPX-abcd1234...`)

##### Step 4: Add to Configuration

```php
'google_search_console' => [
    'client_id' => '123456789-abcdefg.apps.googleusercontent.com',
    'client_secret' => 'GOCSPX-abcd1234...',
    'redirect_uri' => 'http://localhost/rankmath/api.php/api/google/oauth-callback',
]
```

##### Step 5: Connect Your Website

1. Go to: https://search.google.com/search-console
2. Add your website property
3. Verify ownership (multiple methods available)

**Documentation:** https://developers.google.com/webmaster-tools/

---

### 3. SEMrush API (Optional - For Advanced Keyword Metrics)

**Purpose:** Provides keyword difficulty scores and competitive analysis.

**Cost:** Paid plans only (starting at $119.95/month)

#### How to Get API Key:

1. **Sign up for SEMrush**
   - Visit: https://www.semrush.com/
   - Choose a paid plan that includes API access

2. **Access API Dashboard**
   - Log in to SEMrush
   - Go to "Projects" → "API"
   - Or visit: https://www.semrush.com/api-analytics/

3. **Get API Key**
   - Copy your API key from the dashboard

4. **Add to Configuration**
   ```php
   'semrush' => [
       'api_key' => 'your_semrush_api_key_here',
   ]
   ```

**Note:** SEMrush is optional. The app works without it using Google Search Console data.

**Documentation:** https://www.semrush.com/api-documentation/

---

## Configuration File Setup

### Location
```
config/api-keys.php
```

### Complete Example

```php
<?php
return [
    // Required: Google Gemini API
    'gemini' => [
        'api_key' => 'AIzaSyAPKvJr5Vgio2vxTV6GSQS2eB0gxVGJnsk',
        'model' => 'gemini-2.0-flash-exp',
        'endpoint' => 'https://generativelanguage.googleapis.com/v1beta/models',
    ],
    
    // Optional: Google Search Console
    'google_search_console' => [
        'client_id' => '123456789-abc.apps.googleusercontent.com',
        'client_secret' => 'GOCSPX-xyz123...',
        'redirect_uri' => 'http://localhost/rankmath/api.php/api/google/oauth-callback',
    ],
    
    // Optional: SEMrush
    'semrush' => [
        'api_key' => 'your_semrush_key',
    ],
];
```

---

## Security Best Practices

### 1. Never Commit API Keys to Git

Add to `.gitignore`:
```
config/api-keys.php
.env
```

### 2. Use Environment-Specific Configs

Create separate files for each environment:
- `api-keys.local.php` - Development
- `api-keys.staging.php` - Staging
- `api-keys.production.php` - Production

### 3. Restrict API Key Access

**For Gemini API:**
- Go to Google AI Studio → API keys
- Click on your key → "Restrict Key"
- Add HTTP referrer restrictions
- Add API restrictions (only Generative Language API)

**For Google Cloud:**
- Use OAuth 2.0 (already implemented)
- Set authorized domains
- Add IP restrictions if needed

### 4. Monitor API Usage

**Gemini API:**
- Check usage: https://makersuite.google.com/app/apikey

**Google Cloud:**
- Monitor at: https://console.cloud.google.com/apis/dashboard

### 5. Rotate Keys Regularly

- Change API keys every 90 days
- Immediately rotate if exposed
- Keep backups of old keys for 24 hours during transition

---

## Deployment Checklist

### Before Deploying to Production:

- [ ] Get production API keys (not dev keys)
- [ ] Update `redirect_uri` to production URL
- [ ] Configure production domain in Google Cloud Console
- [ ] Add production domain to OAuth consent screen
- [ ] Verify `.gitignore` excludes `api-keys.php`
- [ ] Test API connections from production server
- [ ] Set up API monitoring/alerting
- [ ] Document API credentials in secure password manager

### Production Setup Steps:

1. **Upload Application**
   ```bash
   git clone <repository>
   cd rankmath
   composer install  # if using Composer
   ```

2. **Create API Config Manually**
   ```bash
   # DO NOT upload via Git!
   nano config/api-keys.php
   # Add production keys
   # Save and exit
   ```

3. **Set Proper Permissions**
   ```bash
   chmod 600 config/api-keys.php
   chown www-data:www-data config/api-keys.php
   ```

4. **Update Google OAuth**
   - Add production URL to authorized redirect URIs
   - Update `redirect_uri` in config

5. **Test All APIs**
   - Content AI generation
   - Keyword research
   - Data sync with Search Console

---

## Troubleshooting

### Gemini API Issues

**Error: "Invalid API Key"**
- Verify key is correct in `api-keys.php`
- Check key is enabled in Google AI Studio
- Ensure no extra spaces in key

**Error: "Quota Exceeded"**
- Free tier: 1,500 requests/day
- Wait for reset (midnight Pacific Time)
- Or upgrade to paid tier

### Google Search Console Issues

**Error: "Invalid Client ID"**
- Verify credentials match Google Cloud Console
- Check redirect URI exactly matches

**Error: "Access Denied"**
- Complete OAuth consent screen setup
- Add yourself as test user
- Verify in Search Console

**Error: "Redirect URI Mismatch"**
- Update in Google Cloud Console → Credentials
- Must exactly match `redirect_uri` in config

### General Issues

**APIs Not Working**
- Check file exists: `config/api-keys.php`
- Verify file is readable by PHP
- Check error logs: `error_log` or server logs
- Test with curl from command line

---

## Testing API Configuration

### Test Gemini API

```bash
curl "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent?key=YOUR_API_KEY" \
  -H 'Content-Type: application/json' \
  -X POST \
  -d '{"contents":[{"parts":[{"text":"Test"}]}]}'
```

Expected: JSON response with generated text

### Test in Application

1. Go to: `/rankmath/content-ai`
2. Enter keyword: "test"
3. Generate content
4. Check response for: `"powered_by": "Google Gemini AI"`

---

## Cost Estimation

### Typical Usage (per month):

| Feature | API | Requests | Cost |
|---------|-----|----------|------|
| Content AI | Gemini | ~500/month | **FREE** |
| Keyword Research | Google SC | Unlimited | **FREE** |
| Advanced Metrics | SEMrush | Unlimited | **$119.95+** |

**Total Minimum Cost:** $0 (if not using SEMrush)

---

## Support & Resources

### Official Documentation:
- Gemini API: https://ai.google.dev/docs
- Google Search Console: https://developers.google.com/webmaster-tools/
- Google Cloud: https://cloud.google.com/docs

### Community:
- Stack Overflow: [google-search-console-api]
- Google Cloud Community: https://www.googlecloudcommunity.com/

### Rate Limits:
- Gemini Free: 15 req/min, 1,500 req/day
- Google Search Console: 1,200 requests/minute
- SEMrush: Varies by plan

---

## Quick Start Summary

1. **Get Gemini API Key** (5 minutes)
   - Visit: https://makersuite.google.com/app/apikey
   - Create key → Copy

2. **Add to Config** (1 minute)
   - Edit: `config/api-keys.php`
   - Paste key → Save

3. **Test** (1 minute)
   - Open: `/rankmath/content-ai`
   - Generate content
   - ✅ Done!

**For Search Console (optional):**
- Takes 15-30 minutes to set up OAuth
- Follow "Google Search Console API" section above
- Required only for enhanced keyword research

---

## Questions?

For issues or questions:
1. Check error logs first
2. Review troubleshooting section
3. Verify API credentials
4. Check API quotas/limits
5. Consult official documentation

**Last Updated:** January 11, 2026
