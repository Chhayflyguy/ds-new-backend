#!/bin/bash
# Start Laravel server with custom PHP configuration for 5MB+ image uploads

php -d upload_max_filesize=32M -d post_max_size=32M -d memory_limit=256M artisan serve

