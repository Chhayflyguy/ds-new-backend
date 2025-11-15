# Update PHP Configuration for 5MB Image Uploads

## Current Status
- **upload_max_filesize**: 2M (needs to be 32M for 5MB+ images)
- **post_max_size**: 8M (needs to be 32M)

## Option 1: Update System php.ini (Recommended)

Edit the file: `/opt/homebrew/etc/php/8.4/php.ini`

Find and change these lines:
```ini
upload_max_filesize = 32M
post_max_size = 32M
memory_limit = 256M
```

Then restart your web server or PHP-FPM.

## Option 2: Use Custom Start Script (For Development)

Use the provided script to start the server with custom settings:
```bash
./start-server.sh
```

Or manually:
```bash
php -d upload_max_filesize=32M -d post_max_size=32M artisan serve
```

## Option 3: Quick Manual Edit

Run these commands (requires sudo):
```bash
sudo sed -i '' 's/^upload_max_filesize = 2M/upload_max_filesize = 32M/' /opt/homebrew/etc/php/8.4/php.ini
sudo sed -i '' 's/^post_max_size = 8M/post_max_size = 32M/' /opt/homebrew/etc/php/8.4/php.ini
```

Then restart your server.

## Verify Changes

After updating, verify with:
```bash
php -r "echo 'upload_max_filesize: ' . ini_get('upload_max_filesize') . PHP_EOL; echo 'post_max_size: ' . ini_get('post_max_size') . PHP_EOL;"
```

You should see:
```
upload_max_filesize: 32M
post_max_size: 32M
```

## Note
- `.htaccess` changes only work with Apache/mod_php
- `ini_set()` in `index.php` won't work for `upload_max_filesize` and `post_max_size` (PHP restriction)
- For `php artisan serve`, use Option 2 or update system php.ini

