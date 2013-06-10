#! /bin/bash

# ----------------------------------------------------------------------------------------------------------------------
# This file is part of {@link https://github.com/MovLib MovLib}.
#
# Copyright © 2013-present {@link http://movlib.org/ MovLib}.
#
# MovLib is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public
# License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later
# version.
#
# MovLib is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY# without even the implied warranty
# of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more details.
#
# You should have received a copy of the GNU Affero General Public License along with MovLib.
# If not, see {@link http://www.gnu.org/licenses/ gnu.org/licenses}.
# ----------------------------------------------------------------------------------------------------------------------

# ----------------------------------------------------------------------------------------------------------------------
# "PHP" installation script.
#
# AUTHOR: Richard Fussenegger <richard@fussenegger.info>
# COPYRIGHT: © 2013-present, MovLib
# LICENSE: http://www.gnu.org/licenses/agpl.html AGPL-3.0
# SINCE: 0.0.1-dev
# ----------------------------------------------------------------------------------------------------------------------

cd /usr/local/src
wget http://downloads.php.net/dsp/php-5.5.0RC3.tar.gz
tar xzf php-5.5.0RC3.tar.gz
mv php-5.5.0RC3.tar.gz php
rm -f php-5.5.0RC3.tar.gz
cd php
./configure \
  CFLAGS="-O3" \
  --disable-flatfile \
  --disable-inifile \
  --disable-pdo \
  --disable-short-tags \
  --disable-sqlite3 \
  --enable-bcmath \
  --enable-fpm \
  --enable-intl \
  --enable-libgcc \
  --enable-libxml \
  --enable-mbstring \
  --enable-mysqlnd \
  --enable-opcache \
  --enable-re2c-cgoto \
  --enable-xml \
  --enable-zend-signals \
  --enable-zip \
  --sysconfdir=/etc/php-fpm \
  --with-bz2 \
  --with-config-file-path=/etc/php-fpm \
  --with-curl \
  --with-fpm-group=www-data \
  --with-fpm-user=www-data \
  --with-icu-dir=/usr/local \
  --with-mcrypt \
  --with-mysql-sock=/run/mysqld/mysqld.sock \
  --with-mysqli \
  --with-openssl \
  --with-pcre-regex \
  --with-pear \
  --with-zend-vm=GOTO \
  --with-zlib
make
make test
make install
rm -rf /usr/local/src/php
exit 0
