#!/bin/bash

# cause ubuntu 16.04 does not work nice with vagrant, make sure php7 is available
sudo add-apt-repository ppa:ondrej/php
sudo apt-get update
sudo apt-get install -y \
git \
curl \
nginx \
php7.0 \
php7.0-fpm \
php7.0-xml \
php7.0-zip \
php7.0-curl

curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer

# replace nginx default config
sudo rm /etc/nginx/sites-available/default
sudo ln -s /vagrant/vagrant/default.conf /etc/nginx/sites-available/default

# set up stadard project public folder as webroot
sudo rm -rf /usr/share/nginx/html
sudo ln -s /vagrant/public /usr/share/nginx/html

sudo service php7.0-fpm restart
sudo service nginx restart
