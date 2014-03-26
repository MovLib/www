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
# Shell provisioner for our vagrant box.
#
# AUTHOR:     Richard Fussenegger <richard@fussenegger.info>
# COPYRIGHT:  © 2014 MovLib
# LICENSE:    http://www.gnu.org/licenses/agpl.html AGPL-3.0
# LINK:       https://movlib.org/
# SINCE:      0.0.1-dev
# ----------------------------------------------------------------------------------------------------------------------

# Exit on all errors.
set -e

# Make sure bash is installed on our system. We need bash for INI parsing.
apt-get install --yes bash

# Export the current environment, this is important for INI parsing.
export MOVLIB_ENVIRONMENT=vagrant

# Install PHP, otherwise we can't do anything.
bash /vagrant/bin/install-php.sh

# Now we can install Composer and all dependencies.
bash /vagrant/bin/install-composer.sh

# We need Symfony console to start our provisioning command.
composer update --no-interaction --prefer-source --working-dir=/vagrant

# Create all symbolic binary links in /usr/local/bin
php /vagrant/bin/movlib.php

# Provision the system.
movadmin config -cVagrant -s
movadmin fix-permissions
movinstall all

exit 0
