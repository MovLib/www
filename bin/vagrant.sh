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

export MOVLIB_ENVIRONMENT=vagrant

# Change to the document root.
cd /vagrant

# Install PHP, otherwise we can't do anything.
sh bin/install-php.sh

# Make sure that the vendor directory is empty (remember that this is a shared folder between host and guest).
if [ -d vendor ]; then
  rm -rf vendor
fi

# Now we can install Composer and all dependencies.
sh bin/install-composer.sh

# We need Symfony console to start our provisioning command.
composer update

# Install everything else.
php bin/movlib.php provision --all --environment=${MOVLIB_ENVIRONMENT}

exit 0
