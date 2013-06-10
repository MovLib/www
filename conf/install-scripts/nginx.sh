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

cd /usr/local/src
wget http://nginx.org/download/nginx-1.5.1.tar.gz
tar xzf nginx-1.5.1.tar.gz
mv nginx-1.5.1 nginx
rm -f nginx-1.5.1.tar.gz
cd nginx
./configure \
  CFLAGS="-O3" \
  --add-module=/usr/local/src/nginx-upload-progress-module \
  --add-module=/usr/local/src/nginx-upstream-fair \
  --conf-path=/etc/nginx/nginx.conf \
  --error-log-path=/var/log/nginx/error.log \
  --group=www-data \
  --http-client-body-temp-path=/var/cache/nginx/body \
  --http-fastcgi-temp-path=/var/cache/nginx/fastcgi \
  --http-log-path=/var/log/nginx/access.log \
  --lock-path=/var/lock/nginx.lock \
  --pid-path=/run/nginx.pid \
  --prefix=/usr/local \
  --sbin-path=/usr/local/sbin \
  --user=www-data \
  --with-http_gzip_static_module \
  --with-http_spdy_module \
  --with-http_ssl_module \
  --with-ipv6 \
  --with-md5-asm \
  --with-md5=/usr/local/src/openssl-1.0.1e \
  --with-openssl=/usr/local/src/openssl-1.0.1e \
  --with-pcre-jit \
  --with-pcre=/usr/local/src/pcre-8.33 \
  --with-sha1-asm \
  --with-sha1=/usr/local/src/openssl-1.0.1e \
  --with-zlib=/usr/local/src/zlib \
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
make
make test
make install
rm -rf /usr/local/src/nginx
mkdir /var/cache/nginx/body
mkdir /var/cache/nginx/fastcgi
exit 0
