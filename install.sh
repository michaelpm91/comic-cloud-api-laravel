#!/bin/bash
cd /var/www/api.comiccloud.io
curl -sS https://getcomposer.org/installer | php
php composer.phar install
