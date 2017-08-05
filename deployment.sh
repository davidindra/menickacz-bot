#!/bin/bash

echo 'Deploying...'
cd /var/www/menicka.deia.cz

rm -f /var/www/menicka.deia.cz/slack/cache.db

php7.0 /bin/composer install --no-dev --no-interaction 2>&1;

echo 'Deployment finished successfully.'