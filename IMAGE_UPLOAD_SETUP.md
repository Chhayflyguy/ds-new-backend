# Image Upload Configuration for 3k×4k Images

## Current Settings
- **Laravel Validation**: 30MB (30720KB) - ✅ Updated
- **PHP upload_max_filesize**: 2M - ⚠️ Needs to be increased
- **PHP post_max_size**: 8M - ⚠️ Needs to be increased

## Required PHP Configuration

To handle 3k×4k (3000×4000) images, you need to increase PHP limits:

### Option 1: php.ini (Recommended for production)
```ini
upload_max_filesize = 32M
post_max_size = 32M
memory_limit = 256M
```

### Option 2: .htaccess (If using Apache)
```apache
php_value upload_max_filesize 32M
php_value post_max_size 32M
php_value memory_limit 256M
```

### Option 3: Runtime (For testing only)
Add to `public/index.php` or create a middleware:
```php
ini_set('upload_max_filesize', '32M');
ini_set('post_max_size', '32M');
ini_set('memory_limit', '256M');
```

## Image Support
- **Maximum Resolution**: 3000×4000 pixels (3k×4k)
- **Maximum File Size**: 30MB
- **Supported Formats**: JPEG, PNG, GIF, WebP, BMP, SVG, ICO, TIFF, HEIC, HEIF

## Notes
- Large images (3k×4k) can be 5-15MB depending on format and compression
- The system logs image dimensions for monitoring
- All uploads are validated and logged for debugging

