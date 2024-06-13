#!/bin/sh

# Apacheをバックグラウンドで起動
apache2-foreground &

# Horizonを起動
php /var/www/html/src/artisan horizon
