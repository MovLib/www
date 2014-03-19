#!/bin/bash

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
# Download, configure, and install PHP.
#
# NOTE:
#   This script will also alter apt's sources.list and preferences configuration files by adding the Austrian mirror as
#   default for stable, testing, and unstable. The pin for stable is set to 500, to -10 for testing and unstable. After
#   that an update and save upgrade is performed and additional software will be installed that is necessary for
#   compiling PHP. All installed packages will remain on the system for future compilation.
#
# USAGE:
#   `./install-php.sh -u <username> [-g <groupname>]`
#
# The user's name is used as group name if no group name is passed to the script.
#
# AUTHOR:     Richard Fussenegger <richard@fussenegger.info>
# COPYRIGHT:  © 2014 MovLib
# LICENSE:    http://www.gnu.org/licenses/agpl.html AGPL-3.0
# LINK:       https://movlib.org/
# SINCE:      0.0.1-dev
# ----------------------------------------------------------------------------------------------------------------------

set -e

cat <<EOF > /etc/apt/sources.list
# /etc/apt/sources.list

deb http://ftp.at.debian.org/debian stable main non-free contrib
deb http://ftp.at.debian.org/debian testing main non-free contrib
deb http://ftp.at.debian.org/debian unstable main non-free contrib

EOF

cat <<EOF > /etc/apt/preferences
# /etc/apt/preferences

Package: *
Pin: release a=stable
Pin-Priority: 500

Package: *
Pin: release a=testing
Pin-Priority: -10

Package: *
Pin: release a=unstable
Pin-Priority: -10

EOF

export DEBIAN_FRONTEND=noninteractive

apt-get update

apt-get upgrade --yes

apt-get install --yes   \
  bison                 \
  build-essential       \
  ca-certificates       \
  checkinstall          \
  coreutils             \
  libbz2-dev            \
  libcurl4-openssl-dev  \
  libssl-dev            \
  libtidy-dev           \
  libxml2-dev           \
  python                \
  re2c                  \
  tar                   \
  wget                  \

apt-get install --yes --target-release testing \
  libicu-dev

set +e
dpkg --purge movlib-php
set -e

DIR="$(dirname "$(dirname "$(readlink -f "${BASH_SOURCE[0]}")")")"

json_get() {
  local ENV='dist'
  if [[ "${2}" ]]; then
    local ENV=${2}
  fi
  echo $(python -c "import json; print json.load(open('${DIR}/etc/movlib/${ENV}.json'))${1}")
}

if [[ "${MOVLIB_ENVIRONMENT}" ]]; then
  USER=$(json_get "['user']" ${MOVLIB_ENVIRONMENT})
  GROUP=$(json_get "['group']" ${MOVLIB_ENVIRONMENT})
else
  USER=$(json_get "['user']")
  GROUP=$(json_get "['group']")
fi
VERSION=$(json_get "['php']['version']")
CHECKSUM=$(json_get "['php']['checksum']")
WEBMASTER=$(json_get "['webmaster']")

cd /usr/local/src

wget "http://at1.php.net/distributions/php-${VERSION}.tar.gz"

if [[ $(echo "${CHECKSUM}  php-${VERSION}.tar.gz" | md5sum --check -) -ne 0 ]]; then
  rm "/usr/local/src/php-${VERSION}.tar.gz"
  exit 1
fi

tar --extract --gzip --file "/usr/local/src/php-${VERSION}.tar.gz"

rm "/usr/local/src/php-${VERSION}.tar.gz"

chown --recursive root:root "/usr/local/src/php-${VERSION}"

cd "/usr/local/src/php-${VERSION}"

CFLAGS='-O3 -m64 -march=native -DMYSQLI_NO_CHANGE_USER_ON_PCONNECT' \
CXXFLAGS='-O3 -m64 -march=native' \
./configure \
  --disable-flatfile \
  --disable-inifile \
  --disable-short-tags \
  --enable-fpm \
  --enable-inline-optimization \
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
  --sysconfdir="/etc/php" \
  --with-bz2 \
  --with-config-file-path="/etc/php" \
  --with-curl \
  --with-fpm-group="${GROUP}" \
  --with-fpm-user="${USER}" \
  --with-icu-dir='/usr' \
  --with-mysql-sock="/run/mysql/mysql.sock" \
  --with-mysqli \
  --with-openssl \
  --with-pcre-regex \
  --with-pear \
  --with-tidy \
  --with-zend-vm='GOTO' \
  --with-zlib \

checkinstall \
  --default \
  --install \
  --maintainer="${WEBMASTER}" \
  --nodoc \
  --pkgname='movlib-php' \
  --pkgrelease='1' \
  --pkgversion="${VERSION}" \
  --provides='php' \
  --type='debian' \

rm -r "/usr/local/src/php-${VERSION}"

exit 0
