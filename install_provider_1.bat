@echo off

git clone -b elnawawy https://github.com/AhmedNabil-hub/Bachelor-Project.git
ren Bachelor-Project Backend_Provider
cd Backend_Provider
type nul > .env
composer install
