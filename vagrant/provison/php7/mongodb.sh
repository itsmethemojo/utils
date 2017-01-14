#!/bin/bash

sudo apt-get install -y \
mongodb-server \
php-mongodb

sudo service php7.0-fpm restart
