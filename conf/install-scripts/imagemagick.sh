#!/bin/sh

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
# LINK: http://www.imagemagick.org/script/advanced-unix-installation.php
# LINK: http://tldp.org/LDP/Bash-Beginners-Guide/html/index.html
# AUTHOR: Richard Fussenegger <richard@fussenegger.info>
# COPYRIGHT: © 2013-present, MovLib
# LICENSE: http://www.gnu.org/licenses/agpl.html AGPL-3.0
# SINCE: 0.0.1-dev
# ----------------------------------------------------------------------------------------------------------------------

source $(pwd)/inc/conf.sh

if [ ${#} == 1 ]; then
  VERSION=${1}
else
  VERSION="6.8.7-10"
  msginfo "No version string supplied as argument, using default version ${VERSION}!"
fi

NAME="ImageMagick"
aptitude install -y libjpeg-dev libpng-dev
source ${ID}uninstall.sh
source ${ID}wget.sh "http://www.imagemagick.org/download/" "${NAME}-${VERSION}" ".tar.gz"
./configure \
  CFLAGS="-O3 -m64 -pthread" \
  CXXFLAGS="-O3 -m64 -pthread" \
  --disable-static \
  --enable-shared \
  --with-jpeg \
  --with-png \
  --with-quantum-depth=8 \
  --with-rsvg \
  --with-webp \
  --without-bzlib \
  --without-djvu \
  --without-dps \
  --without-fftw \
  --without-fontconfig \
  --without-freetype \
  --without-gvc \
  --without-jbig \
  --without-jp2 \
  --without-lcms \
  --without-lcms2 \
  --without-lqr \
  --without-lzma \
  --without-magick-plus-plus \
  --without-openexr \
  --without-pango \
  --without-perl \
  --without-tiff \
  --without-wmf \
  --without-x \
  --without-xml \
  --without-zlib
source ${ID}install.sh
