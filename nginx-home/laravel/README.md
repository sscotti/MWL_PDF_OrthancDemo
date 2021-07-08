# Orthanc-Laravel-Portal

From a fresh install, run from within the Docker Container, /nginx-home/PortalRads


php artisan migrate

php artisan db:seed --class=PermissionsDemoSeeder

php artisan db:seed --class=DatabaseSeeder