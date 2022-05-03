@echo off

cd Backend_Provider
copy .env.example .env
php artisan key:generate
php artisan storage:link