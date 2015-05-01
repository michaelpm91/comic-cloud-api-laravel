#!/bin/bash
#cd /var/www/api.comiccloud.io
#curl -sS https://getcomposer.org/installer | php
#php composer.phar install
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
composer install
