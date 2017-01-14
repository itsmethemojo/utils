#!/bin/bash

sudo apt-get install -y \
redis-server \
php-redis

sudo service php7.0-fpm restart
