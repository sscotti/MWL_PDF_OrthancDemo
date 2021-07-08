#!/bin/bash
# Laravel Symlinks for Storage, mimics php artisan storage:link in laravel, but in container.
# ln -sfn /nginx-home/PortalRads/storage/app/public/ /nginx-home/PortalRads/public/storage
cd /nginx-home/laravel
php artisan storage:link
chown -R www-data:www-data /nginx-home/laravel/public
# Probably need to cd back to a different one to get the supervisor pid saved in the container
exec /usr/bin/supervisord --configuration=/etc/supervisor/conf.d/supervisord.conf
