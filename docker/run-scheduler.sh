#!/bin/bash

# Run Laravel scheduler every minute
# This replaces the need for crontab in Docker

while true; do
    cd /var/www
    php artisan schedule:run --no-interaction >> /dev/null 2>&1
    sleep 60
done