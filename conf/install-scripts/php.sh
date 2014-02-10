#!/bin/sh

# ----------------------------------------------------------------------------------------------------------------------
# This file is part of {@link https://github.com/MovLib MovLib}.
#
# Copyright © 2013-present {@link https://movlib.org/ MovLib}.
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
# AUTHOR:     Richard Fussenegger <richard@fussenegger.info>
# COPYRIGHT:  © 2013 MovLib
# LICENSE:    http://www.gnu.org/licenses/agpl.html AGPL-3.0
# LINK:       https://movlib.org/
# SINCE:      0.0.1-dev
# ----------------------------------------------------------------------------------------------------------------------

# Define work directory and include configuration.
WD="$(cd `dirname ${0}`; pwd)/"
source ${WD}/inc/conf.sh

CHECKINSTALL_ARGUMENTS="--provides=php,php-cgi,php-config,php-fpm,phpize"
NAME="php"

if [ ${#} == 1 ]; then
  VERSION=${1}
else
  VERSION="5.5.9"
  msginfo "No version string supplied as argument, using default version ${VERSION}!"
fi

source ${ID}uninstall.sh
source ${ID}wget.sh "http://us1.php.net/distributions/" "${NAME}-${VERSION}" ".tar.gz"

# We need PDO for PHPUnit in our development system. Otherwise we can't run any database tests.
CFLAGS="-O3 -m64 -DMYSQLI_NO_CHANGE_USER_ON_PCONNECT" CXXFLAGS="-O3 -m64" ./configure \
  --disable-flatfile \
  --disable-inifile \
  --disable-short-tags \
  --enable-bcmath \
  --enable-fpm \
  --enable-intl \
  --enable-libgcc \
  --enable-libxml \
  --enable-mbstring \
  --enable-mysqlnd \
  --enable-opcache \
  --enable-pcntl \
  --enable-re2c-cgoto \
  --enable-xml \
  --enable-zend-signals \
  --enable-zip \
  --sysconfdir="/etc/php-fpm" \
  --with-config-file-path="/etc/php-fpm" \
  --with-curl \
  --with-fpm-group="www-data" \
  --with-fpm-user="www-data" \
  --with-icu-dir="/usr/local" \
  --with-mcrypt="/usr/lib/libmcrypt" \
  --with-mysql-sock="/run/mysqld/mysqld.sock" \
  --with-mysqli \
  --with-openssl \
  --with-pcre-regex \
  --with-pear \
  --with-tidy \
  --with-zend-vm="GOTO" \
  --with-zlib

source ${ID}install.sh
