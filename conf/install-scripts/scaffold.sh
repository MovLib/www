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
# DESCRIPTION
#
# AUTHOR:     Forename Surname <email@address>
# COPYRIGHT:  © 2013 MovLib
# LICENSE:    http://www.gnu.org/licenses/agpl.html AGPL-3.0
# LINK:       https://movlib.org/
# SINCE:      0.0.1-dev
# ----------------------------------------------------------------------------------------------------------------------

# This will import some variables and provide the functions to print colored messages.
source $(pwd)/inc/conf.sh

# Always allow the user to overwrite the version.
if [ ${#} == 1 ]; then
  VERSION=${1}
else
  # Insert the default version in this variable.
  VERSION=""
  msginfo "No version string supplied as argument, using default version ${VERSION}!"
fi

# Insert the name in this variable.
NAME=""

# Always uninstall the software before installing the new one.
source ${ID}uninstall.sh

# You have two prepared methods available to fetch the source:
#
# Download a tarball via wget and extract it.
source ${ID}wget.sh "URL" "FILENAME" "EXTENSION"
#
# Check it our from a GitHub repository.
source ${ID}git.sh "USER" "PROJECT"
#
# If you need some other way (e.g. svn) write a script and extend the scaffold or directly implement it in the install
# script (if it is only needed once).

# Configure the program at this point. Always try compiler options -O3 and -m64. Do not forget that you have to escape
# the linefeed with a backslash!
./configure \
  CFLAGS="-O3 -m64" \
  --flags

# This will create a Debian package via checkinstall, some notes:
#   * Do not create a documentation
#   * Package manager should be webmaster@movlib.org
#   * Ensure package name is the same as you use in the variable ${NAME} (otherwise uninstall.sh won't work)
source ${ID}install.sh

# You can add post-installation logic at this point!
