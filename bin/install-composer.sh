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
# Download and install Composer <https://getcomposer.org/>.
#
# AUTHOR:     Richard Fussenegger <richard@fussenegger.info>
# COPYRIGHT:  © 2014 MovLib
# LICENSE:    http://www.gnu.org/licenses/agpl.html AGPL-3.0
# LINK:       https://movlib.org/
# SINCE:      0.0.1-dev
# ----------------------------------------------------------------------------------------------------------------------

set +e
which php > /dev/null
if [[ ${?} != 0 ]]; then
  echo 'PHP must be installed on your system!'
  exit 1
fi

which composer > /dev/null
if [[ ${?} = 0 ]]; then
  set -e
  composer self-update
else
  set -e
  wget --output-document=/tmp/getcomposer-installer https://getcomposer.org/installer
  php /tmp/getcomposer-installer
  mv composer.phar /usr/local/bin/composer
  rm /tmp/getcomposer-installer
fi

exit 0
