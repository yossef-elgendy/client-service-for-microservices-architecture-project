@echo off

git clone -b apiGateway https://github.com/AhmedNabil-hub/Bachelor-Project.git
ren Bachelor-Project Backend_AG
cd Backend_AG
type nul > .env
composer install
