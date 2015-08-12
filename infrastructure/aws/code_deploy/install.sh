#!/bin/bash
#Permissions
chown -R ec2-user /var/www/api.dev.comiccloud.io/
chmod -R 775 /var/www/api.dev.comiccloud.io/storage/

#Composer Install
cd /var/www/api.dev.comiccloud.io/
COMPOSER_HOME="~/" /usr/local/bin/composer install

#Reset Database
#php /var/www/api.dev.comiccloud.io/artisan migrate:refresh

#Run new migrations
php /var/www/api.dev.comiccloud.io/artisan migrate

#Generate Environment Variables
php /var/www/api.dev.comiccloud.io/artisan genenvvar --env =  develop
