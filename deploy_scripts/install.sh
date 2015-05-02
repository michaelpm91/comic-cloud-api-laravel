#!/bin/bash
chown -R ec2-user /var/www/api.dev.comiccloud.io/
chmod -R 775 /var/www/api.dev.comiccloud.io/storage/
cd /var/www/api.dev.comiccloud.io/
COMPOSER_HOME="~/" /usr/local/bin/composer install
