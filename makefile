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
# Execute various automated tasks.
#
# AUTHOR:     Richard Fussenegger <richard@fussenegger.info>
# COPYRIGHT:  © 2013 MovLib
# LICENSE:    http://www.gnu.org/licenses/agpl.html AGPL-3.0
# LINK:       https://movlib.org/
# SINCE:      0.0.1-dev
# ----------------------------------------------------------------------------------------------------------------------

DOCUMENT_ROOT=/var/www

install:
	sudo composer update && \
	movlib seed-import && \
	sudo movlib nginx-routes

clean:
	sudo movlib fix-permissions && \
	rm -rf ${DOCUMENT_ROOT}/private/upload/* ${DOCUMENT_ROOT}/public/upload/* && \
	rm -rf ~/vendor && \
	mv ${DOCUMENT_ROOT}/vendor ~ && \
	git clean -xdf && \
	git reset --hard && \
	git pull && \
	mv ~/vendor . && \
	chmod 2770 ${DOCUMENT_ROOT}/bin/movlib.php && \
	sudo movlib fix-permissions

mariadb:
	aptitude update
	aptitude -y purge \
	  libdb-mysql-perl \
	  libmariadbclient18 \
	  libmysqlclient18 \
	  mariadb-server-10.0 \
	  mariadb-client-10.0 \
	  mariadb-client-core-10.0 \
	  mariadb-common \
	  mysql-common && \
	aptitude autoclean
	rm -rf /etc/mysql /var/lib/mysql && \
	mkdir -p /etc/mysql && \
	cat ${DOCUMENT_ROOT}/conf/mariadb/my.ini > /etc/mysql/my.cnf
	movlib fp
	aptitude -y install mariadb-server-10.0
	movlib si

nginx:
	sh conf/install-scripts/nginx.sh
