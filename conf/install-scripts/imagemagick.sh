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
# "ImageMagick" installation script.
#
# AUTHOR: Richard Fussenegger <richard@fussenegger.info>
# COPYRIGHT: © 2013-present, MovLib
# LICENSE: http://www.gnu.org/licenses/agpl.html AGPL-3.0
# SINCE: 0.0.1-dev
# ----------------------------------------------------------------------------------------------------------------------

cd /usr/local/src
wget http://www.imagemagick.org/download/ImageMagick-6.8.5-10.tar.gz
tar xzf ImageMagick-6.8.5-10.tar.gz
mv ImageMagick-6.8.5-10 ImageMagick
rm -f ImageMagick-6.8.5-10.tar.gz
cd ImageMagick
./configure --help
./configure CFLAGS="-O3"
make
make test
make install
rm -rf /usr/local/src/ImageMagick
ldconfig
exit 0
