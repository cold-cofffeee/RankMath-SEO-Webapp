# 🚀 Automatic Path Detection System

## The Problem You Had

Before, you had to manually change paths in multiple files when moving the app:
- ❌ Update `index.php` navigation links
- ❌ Update `app.js` API paths
- ❌ Update `.htaccess` RewriteBase
- ❌ Update `Router.php` base path

**This was painful and error-prone!**

---

## ✅ The Solution: Auto-Detection

Now the app **automatically detects** where it's installed!

### How It Works

**1. PHP Auto-Detection** (`config/config.php`)
```php
// Automatically detects: /rankmath/ or /myapp/ or / (root)
$basePath = dirname($_SERVER['SCRIPT_NAME']);
```

**2. JavaScript Gets Config**
```javascript
// Injected automatically in page head
const API_BASE = window.APP_CONFIG.apiBase;  // Auto!
const BASE_PATH = window.APP_CONFIG.basePath; // Auto!
```

**3. Router Auto-Detects**
```php
// Router.php reads from $_SERVER automatically
$baseDir = dirname($_SERVER['SCRIPT_NAME']);
```

---

## 🎯 Works Everywhere Automatically

### ✅ Localhost Subdirectory
```
http://localhost/rankmath/          → Works!
http://localhost/myapp/             → Works!
http://localhost/seo-tool/          → Works!
```

### ✅ Localhost Root
```
http://localhost/                   → Works!
```

### ✅ Live Server Subdirectory
```
https://example.com/tools/rankmath/ → Works!
https://mysite.com/seo/             → Works!
```

### ✅ Live Server Root
```
https://rankmath-app.com/           → Works!
```

### ✅ Development Server
```
http://192.168.1.100/rankmath/      → Works!
```

---

## 🔧 What You Need to Do

### Option 1: Keep in Subdirectory
Just update `.htaccess` RewriteBase to match your folder:
```apache
RewriteBase /rankmath/         # For http://localhost/rankmath/
RewriteBase /myapp/            # For http://localhost/myapp/
RewriteBase /                  # For http://localhost/ (root)
```

**That's it!** Everything else is automatic.

### Option 2: Move to Root
1. Move all files from `/rankmath/` to your server root
2. Update `.htaccess`:
   ```apache
   RewriteBase /
   ```
3. Done! App auto-detects everything else.

---

## 📁 Files That Auto-Detect

✅ **config/config.php** - Detects base path  
✅ **index.php** - Uses PHP constants  
✅ **app.js** - Reads from window.APP_CONFIG  
✅ **Router.php** - Detects from $_SERVER  
✅ **api.php** - Uses Router's auto-detection  

---

## 🎉 Benefits

1. **Zero Manual Configuration** - Works out of the box
2. **Move Anywhere** - Just update .htaccess RewriteBase
3. **Multiple Environments** - Same code works everywhere
4. **No Hardcoded Paths** - Everything is dynamic
5. **Deploy Ready** - Push to live server without changes

---

## 🧪 Test It

**Current Setup:**
```
http://localhost/rankmath/
```

**Check the auto-detected config:**
Open browser console and type:
```javascript
console.log(window.APP_CONFIG);
```

You'll see:
```javascript
{
  basePath: "/rankmath",
  baseUrl: "http://localhost/rankmath",
  apiBase: "/rankmath/api.php"
}
```

**Move to different folder:**
1. Copy files to `/rankmath/webapp/`
2. Update `.htaccess`: `RewriteBase /rankmath/webapp/`
3. Reload page
4. Check console again - paths auto-update!

---

## 🚀 Deploy to Live Server

### Step 1: Upload Files
Upload everything to your server (e.g., `/public_html/`)

### Step 2: Update .htaccess
If files are in root:
```apache
RewriteBase /
```

If files are in subdirectory (e.g., `/public_html/seo/`):
```apache
RewriteBase /seo/
```

### Step 3: Done!
That's literally it. No PHP changes, no JS changes, nothing else!

---

## 💡 Pro Tip

You can even have multiple installations:
```
http://localhost/rankmath-dev/      → Works!
http://localhost/rankmath-staging/  → Works!
http://localhost/rankmath-live/     → Works!
```

Just update RewriteBase in each .htaccess and they all work independently!

---

## Current URLs (Auto-Detected)

Your app now works at:
- http://localhost/rankmath/
- http://localhost/rankmath/seo-analysis
- http://localhost/rankmath/analytics
- http://localhost/rankmath/404-monitor
- (etc...)

**Move it anywhere, update .htaccess, and it just works!** 🎊
