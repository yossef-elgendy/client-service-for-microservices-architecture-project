@echo off

php artisan queue:listen rabbitmq --queue=client