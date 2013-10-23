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
# LINK: http://vincent.bernat.im/en/blog/2011-ssl-perfect-forward-secrecy.html
# AUTHOR: Richard Fussenegger <richard@fussenegger.info>
# COPYRIGHT: © 2013-present, MovLib
# LICENSE: http://www.gnu.org/licenses/agpl.html AGPL-3.0
# SINCE: 0.0.1-dev
# ----------------------------------------------------------------------------------------------------------------------

source $(pwd)/inc/conf.sh

if [ ${#} == 1 ]; then
  VERSION=${1}
else
  VERSION="1.5.6"
  msginfo "No version string supplied as argument, using default version ${VERSION}!"
fi

#aptitude update && aptitude -y install libatomic-ops-dev

# Uninstall old and download new nginx.
NAME="nginx"
source ${ID}uninstall.sh
source ${ID}wget.sh "http://nginx.org/download/" "${NAME}-${VERSION}" ".tar.gz"

# Install OpenSSL
OPENSSL_VERSION="1.0.1e"
msginfo "Using OpenSSL version ${OPENSSL_VERSION}!"
source ${ID}wget.sh "https://www.openssl.org/source/" "openssl-${OPENSSL_VERSION}" ".tar.gz"
cd ..
msginfo "Changing to directory: ${SD}${NAME}-${VERSION}"

# Install PCRE
#msginfo "Using PCRE version trunk!"
#svn co svn://vcs.exim.org/pcre/code/trunk pcre
#cd pcre
#msginfo "Changing to directory: ${SD}${NAME}/pcre"
#./autogen.sh
#cd ..
#msginfo "Changing to directory: ${SD}${NAME}"
PCRE_VERSION="8.33"
msginfo "Using PCRE version ${PCRE_VERSION}!"
source ${ID}wget.sh "ftp://ftp.csx.cam.ac.uk/pub/software/programming/pcre/" "pcre-${PCRE_VERSION}" ".tar.gz"
cd ..
msginfo "Changing to directory: ${SD}${NAME}-${VERSION}"

# Install Zlib
source ${ID}git.sh madler zlib
cd ..
msginfo "Changing to directory: ${SD}${NAME}-${VERSION}"

./configure \
  --user="www-data" \
  --group="www-data" \
  --prefix="/usr/local" \
  --sbin-path="/usr/local/sbin" \
  --conf-path="/etc/nginx/nginx.conf" \
  --pid-path="/run/nginx.pid" \
  --lock-path="/var/lock/nginx.lock" \
  --error-log-path="/var/log/nginx/error.log" \
  --http-client-body-temp-path="/run/shm/nginx/body" \
  --http-fastcgi-temp-path="/run/shm/nginx/fastcgi" \
  --http-log-path="/var/log/nginx/access.log" \
  --with-cc-opt="-O2 -m64" \
  --with-ld-opt="-m64" \
  --with-ipv6 \
  --with-http_gzip_static_module \
  --with-http_ssl_module \
  --with-http_spdy_module \
  --with-openssl-opt="enable-ec_nistp_64_gcc_128" \
  --with-openssl="/usr/local/src/${NAME}-${VERSION}/openssl-${OPENSSL_VERSION}" \
  --with-md5="/usr/local/src/${NAME}-${VERSION}/openssl-${OPENSSL_VERSION}" \
  --with-md5-asm \
  --with-sha1="/usr/local/src/${NAME}-${VERSION}/openssl-${OPENSSL_VERSION}" \
  --with-sha1-asm \
  --with-pcre="/usr/local/src/${NAME}-${VERSION}/pcre-${PCRE_VERSION}" \
  --with-pcre-jit \
  --with-zlib="/usr/local/src/${NAME}-${VERSION}/zlib" \
  --without-http_access_module \
  --without-http_auth_basic_module \
  --without-http_autoindex_module \
  --without-http_empty_gif_module \
  --without-http_geo_module \
  --without-http_limit_conn_module \
  --without-http_limit_req_module \
  --without-http_proxy_module \
  --without-http_proxy_module \
  --without-http_referer_module \
  --without-http_scgi_module \
  --without-http_split_clients_module \
  --without-http_ssi_module \
  --without-http_upstream_ip_hash_module \
  --without-http_userid_module \
  --without-http_uwsgi_module

exitonerror

# Create cache directories for nginx.
mkdir -p /var/cache/nginx/body /var/cache/nginx/fastcgi

# Stop currently running nginx process.
/etc/init.d/nginx stop

make && exitonerror
checkinstall make install && exitonerror
make clean && exitonerror

# Remove the default configuration files.
cd conf
for f in *; do
  rm -f /etc/nginx/${f}
done
rm -f /etc/nginx/*.default

ldconfig
LINE=$(msgline)
msgsuccess "${LINE}\nSuccessfully installed ${NAME}\n${LINE}"

# Start newly installed nginx.
/etc/init.d/nginx start

exit 0
