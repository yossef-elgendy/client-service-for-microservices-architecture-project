@echo off

cd Backend_Client
copy .env.example .env
php artisan key:generate
php artisan storage:link