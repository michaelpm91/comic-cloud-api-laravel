#!/bin/bash
#Permissions
chown -R ec2-user /var/www/api.dev.comiccloud.io/
chmod -R 775 /var/www/api.dev.comiccloud.io/storage/
#Composer Install
cd /var/www/api.dev.comiccloud.io/
COMPOSER_HOME="~/" /usr/local/bin/composer install
#Supervisor Queue Scripts
echo -e '#!/bin/bash \n php /var/www/api.dev.comiccloud.io/artisan queue:listen --timeout=0' > /usr/local/run_queue.sh
chmod +x /usr/local/run_queue.sh
cat > /etc/supervisor/conf.d/laravel_queue.conf <<EOF
[program:laravel_queue]
command=/usr/local/bin/run_queue.sh
autostart=true
autorestart=true
stderr_logfile=/var/log/laraqueue.err.log
stdout_logfile=/var/log/laraqueue.out.log
EOF
supervisorctl reread
supervisorctl update