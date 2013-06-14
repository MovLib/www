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
# "nginx" installation script.
#
# AUTHOR: Richard Fussenegger <richard@fussenegger.info>
# COPYRIGHT: © 2013-present, MovLib
# LICENSE: http://www.gnu.org/licenses/agpl.html AGPL-3.0
# SINCE: 0.0.1-dev
# ----------------------------------------------------------------------------------------------------------------------

source $(pwd)/inc/conf.sh

if [ ${#} == 1 ]; then
  VERSION=${1}
else
  VERSION="1.5.1"
  msginfo "No version string supplied as argument, using default version ${VERSION}!"
fi

aptitude update && aptitude -y install libatomic-ops-dev

NAME="nginx-${VERSION}"
source ${ID}wget.sh "http://nginx.org/download/" ${NAME} ".tar.gz"

# Install OpenSSL
OPENSSL_VERSION="1.0.1e"
msginfo "Using OpenSSL version ${OPENSSL_VERSION}!"
source ${ID}wget.sh "https://www.openssl.org/source/" "openssl-${OPENSSL_VERSION}" ".tar.gz"
cd ..
msginfo "Changing to directory: ${SD}${NAME}"

# Install PCRE
msginfo "Using PCRE version trunk!"
svn co svn://vcs.exim.org/pcre/code/trunk pcre
cd pcre
msginfo "Changing to directory: ${SD}${NAME}/pcre"
./autogen.sh
cd ..
msginfo "Changing to directory: ${SD}${NAME}"

# Install Zlib
source ${ID}git.sh madler zlib
cd ..
msginfo "Changing to directory: ${SD}${NAME}"

# Install nginx UploadProgressModule
source ${ID}git.sh masterzen nginx-upload-progress-module
cd ..
msginfo "Changing to directory: ${SD}${NAME}"

# Install nginx UpstreamFairModule
source ${ID}git.sh gnosek nginx-upstream-fair
cd ..
msginfo "Changing to directory: ${SD}${NAME}"

# Configure our nginx installation.
./configure \
  --add-module="/usr/local/src/${NAME}/nginx-upload-progress-module" \
  --add-module="/usr/local/src/${NAME}/nginx-upstream-fair" \
  --conf-path="/etc/nginx/nginx.conf" \
  --error-log-path="/var/log/nginx/error.log" \
  --group="www-data" \
  --http-client-body-temp-path="/var/cache/nginx/body" \
  --http-fastcgi-temp-path="/var/cache/nginx/fastcgi" \
  --http-log-path="/var/log/nginx/access.log" \
  --lock-path="/var/lock/nginx.lock" \
  --pid-path="/run/nginx.pid" \
  --prefix="/usr/local" \
  --sbin-path="/usr/local/sbin" \
  --user="www-data" \
  --with-cc-opt="-O3 -m64" \
  --with-ld-opt="-O3 -m64" \
  --with-http_gzip_static_module \
  --with-http_spdy_module \
  --with-http_ssl_module \
  --with-ipv6 \
  --with-libatomic="/usr/include" \
  --with-md5-asm \
  --with-md5="/usr/local/src/${NAME}/openssl-${OPENSSL_VERSION}" \
  --with-openssl-opt="-O3 -m64" \
  --with-openssl="/usr/local/src/${NAME}/openssl-${OPENSSL_VERSION}" \
  --with-pcre-jit \
  --with-pcre-opt="-O3 -m64" \
  --with-pcre="/usr/local/src/${NAME}/pcre" \
  --with-sha1-asm \
  --with-sha1-opt="-O3 -m64"
  --with-sha1="/usr/local/src/${NAME}/openssl-${OPENSSL_VERSION}" \
  --with-zlib-opt="-O3 -m64" \
  --with-zlib="/usr/local/src/${NAME}/zlib" \
  --without-http_access_module \
  --without-http_auth_basic_module \
  --without-http_autoindex_module \
  --without-http_empty_gif_module \
  --without-http_geo_module \
  --without-http_limit_conn_module \
  --without-http_limit_req_module \
  --without-http_map_module \
  --without-http_proxy_module \
  --without-http_proxy_module \
  --without-http_referer_module \
  --without-http_scgi_module \
  --without-http_split_clients_module \
  --without-http_ssi_module \
  --without-http_upstream_ip_hash_module \
  --without-http_userid_module \
  --without-http_uwsgi_module

# Create cache directories for nginx.
mkdir -p /var/cache/nginx/body /var/cache/nginx/fastcgi

# Stop currently running nginx process.
/etc/init.d/nginx stop

make
make test
make install
make clean

# Remove the default configuration files.
cd conf
for f in *; do
  rm -f "/etc/nginx/${f}"
done

ln -s /var/www/conf/nginx/nginx.conf /etc/nginx/nginx.conf

ldconfig
LINE=$(msgline)
msgsuccess "${LINE}\nSuccessfully installed ${NAME}\n${LINE}"

# Start newly installed nginx.
/etc/init.d/nginx start

exit 0
