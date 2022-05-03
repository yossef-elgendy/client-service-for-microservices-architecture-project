@echo off

cd Backend_AG
copy .env.example .env
php artisan key:generate
php artisan storage:link