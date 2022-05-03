@echo off

git clone -b elgendy https://github.com/AhmedNabil-hub/Bachelor-Project.git
ren Bachelor-Project Backend_Client
cd Backend_Client
type nul > .env
composer install
