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
# Ensure all Git submodules and Vagrant plugins are installed and start Vagrant MovDev machine.
#
# AUTHOR:     Richard Fussenegger <richard@fussenegger.info>
# COPYRIGHT:  © 2013 MovLib
# LICENSE:    http://www.gnu.org/licenses/agpl.html AGPL-3.0
# LINK:       https://movlib.org/
# SINCE:      0.0.1-dev
# ----------------------------------------------------------------------------------------------------------------------

echo 'Updating git submodules...'
git submodule update --remote

# Please note that install and update are actually the same in Vagrant, therefore we don't have to check if a plugin is
# already installed if we want to update it.
echo 'Installing and updating Vagrant plugins...'
plugins=( 'hostsupdater' 'vbguest' 'puppet-install' )
for i in "${plugins[@]}"; do
  vagrant plugin install "vagrant-${i}"
done

echo 'Starting Vagrant...'
vagrant up
