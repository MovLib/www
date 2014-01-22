#! /bin/bash

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
# "nginx" installation script.
#
# LINK:       http://vincent.bernat.im/en/blog/2011-ssl-perfect-forward-secrecy.html
# AUTHOR:     Richard Fussenegger <richard@fussenegger.info>
# COPYRIGHT:  © 2013 MovLib
# LICENSE:    http://www.gnu.org/licenses/agpl.html AGPL-3.0
# LINK:       https://movlib.org/
# SINCE:      0.0.1-dev
# ----------------------------------------------------------------------------------------------------------------------

# Set working directory and include configuration script.
WD="$(cd `dirname ${0}`; pwd)/"
source ${WD}/inc/conf.sh

if [ ${#} == 1 ]; then
  VERSION=${1}
else
  VERSION="1.5.9"
  msginfo "No version string supplied as argument, using default version ${VERSION}!"
fi

#aptitude update && aptitude -y install libatomic-ops-dev

# Uninstall old and download new nginx.
NAME="nginx"
source ${ID}uninstall.sh

source ${ID}wget.sh "http://nginx.org/download/" "${NAME}-${VERSION}" ".tar.gz"

msginfo "Changing to directory: ${SD}${NAME}-${VERSION}"
cd ${SD}${NAME}-${VERSION}

# Install OpenSSL
OPENSSL_VERSION="1.0.1e"
msginfo "Using OpenSSL version ${OPENSSL_VERSION}!"
source ${ID}wget.sh "https://www.openssl.org/source/" "openssl-${OPENSSL_VERSION}" ".tar.gz" "false"

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
source ${ID}wget.sh "ftp://ftp.csx.cam.ac.uk/pub/software/programming/pcre/" "pcre-${PCRE_VERSION}" ".tar.gz" "false"

# Install Zlib
source ${ID}git.sh madler zlib

msginfo "Changing to directory: ${SD}${NAME}-${VERSION}"
cd ${SD}${NAME}-${VERSION}

CFLAGS="-O3 -m64 -march=native" \
CXXFLAGS="${CFLAGS}" \
./configure \
  --user="www-data" \
  --group="www-data" \
  --prefix="/usr/local" \
  --sbin-path="/usr/local/sbin" \
  --conf-path="/etc/nginx/nginx.conf" \
  --pid-path="/run/nginx.pid" \
  --lock-path="/var/lock/nginx.lock" \
  --error-log-path="/var/log/nginx/error.log" \
  --http-client-body-temp-path="/run/www/uploads" \
  --http-fastcgi-temp-path="/run/www/fastcgi" \
  --http-log-path="/var/log/nginx/access.log" \
  --with-cc-opt="-O3 -m64 -march=native -ffunction-sections -fdata-sections -D FD_SETSIZE=131072" \
  --with-ld-opt="-Wl,--gc-sections" \
  --with-ipv6 \
  --with-http_gzip_static_module \
  --with-http_ssl_module \
  --with-http_spdy_module \
  --with-openssl-opt="enable-ec_nistp_64_gcc_128 no-rc2 no-rc4 no-rc5 no-md2 no-md4 no-ssl2 no-ssl3 no-krb5 no-hw no-engines" \
  --with-openssl="${SD}${NAME}-${VERSION}/openssl-${OPENSSL_VERSION}" \
  --with-md5="${SD}${NAME}-${VERSION}/openssl-${OPENSSL_VERSION}" \
  --with-md5-asm \
  --with-sha1="${SD}${NAME}-${VERSION}/openssl-${OPENSSL_VERSION}" \
  --with-sha1-asm \
  --with-pcre="${SD}${NAME}-${VERSION}/pcre-${PCRE_VERSION}" \
  --with-pcre-jit \
  --with-zlib="${SD}${NAME}-${VERSION}/zlib" \
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

# Create cache directories for nginx.
mkdir -p /var/cache/nginx/body /var/cache/nginx/fastcgi

# Stop currently running nginx process.
set +e
service nginx stop
set -e

make
checkinstall --default --maintainer=webmaster@movlib.org --nodoc --pkgname=${NAME} --pkgversion=${VERSION} --type=debian ${CHECKINSTALL_ARGUMENTS:=""}
make clean

# Remove default configuration files.
rm -rf /etc/nginx/*

# Create symbolic links for MovLib specific configuration files.
CD="$(cd ${WD}/../nginx; pwd)"
for f in $(find ${CD} -name '*.conf' -or -name '*.php' -type f); do
  fs=${f#${CD}}
  mkdir -p /etc/nginx${fs%/*}
  ln -s ${f} /etc/nginx${fs}
done;

# Copy SSL certificates and other stuff from root's home.
cp -r /root/ssl/* /etc/nginx/ssl

ldconfig
LINE=$(msgline)
msgsuccess "${LINE}\nSuccessfully installed ${NAME}\n${LINE}"

# Compile all routes.
movlib nginx-routes

# Start newly installed nginx.
service nginx start

exit 0
