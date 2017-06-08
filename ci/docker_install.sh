#!/bin/bash

[[ ! -e /.dockerenv ]] && exit 0

set -xe

# copy php-ci.ini to PHP_INI_SCAN_DIR
cp /builds/php-platform/web-session/ci/php-ci.ini /usr/local/etc/php/conf.d/php-ci.ini

# apache configuration
echo "<Directory /var/www/html/web-session>" > /etc/apache2/conf-available/web-session.conf
echo "    AllowOverride All" >> /etc/apache2/conf-available/web-session.conf
echo "</Directory>" >> /etc/apache2/conf-available/web-session.conf
a2enconf web-session.conf
a2ensite 000-default
a2enmod rewrite
apache2ctl start

# softlink build directory to directory acccesible from apache
ln -s /builds/php-platform/web-session /var/www/html/web-session

# composer update
composer update
