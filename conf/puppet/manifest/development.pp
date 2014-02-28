# ----------------------------------------------------------------------------------------------------------------------
# This file is part of {@link https://github.com/MovLib MovLib}.
#
# Copyright (c) 2013-present {@link https://movlib.org/ MovLib}.
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
# Bootstrap the MovDev machine to be as close to the production environment as possible.#
#
# NOTE: Using an <code>include</code> means that the module does nothing by itself and is only used by another module.
#       Use <code>class</code> to include a module that's actually doing something and always specify the important
#       parameters, even if the are the defaults of the provided module.
#
# NOTE: Run <code>puppet-lint --no-80chars-check bootstrap.pp</code> to ensure correct formatting according to the
#       Puppetlabs style guide. We don't honor the 80 characters limit because we have a global 120 character soft limit
#       in our style guide. Also note that we aren't using the correct copyright sign within this file because the
#       puppet linter uses the <i>check whitespace</i> gem which will fail on this character.
#
# AUTHOR:     Richard Fussenegger <richard@fussenegger.info>
# COPYRIGHT:  (c) 2013 MovLib
# LICENSE:    http://www.gnu.org/licenses/agpl.html AGPL-3.0
# LINK:       https://movlib.org/
# SINCE:      0.0.1-dev
# ----------------------------------------------------------------------------------------------------------------------

# This helps broken modules.
Exec { path => '/usr/local/bin:/usr/bin:/bin:/usr/local/sbin:/usr/sbin:/sbin' }

# Install all modules.
hiera_include('classes')

# Install npm global packages.
$npm_packages = hiera_array('npm_packages', undef)
if $npm_packages {
  package { $npm_packages:
    ensure   => 'present',
    provider => 'npm',
  }
}
