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
# Bootstrap the MovDev machine to be as close to the production environment as possible.
#
# NOTE! Currently we"re all working with Debian only, therefore our puppet tasks don"t care if some package might be
# named differently on other *nix systems. This might change in the future.
#
# PS: But everything should work just fine if you"re using Ubuntu :)
#
# AUTHOR:     Richard Fussenegger <richard@fussenegger.info>
# COPYRIGHT:  © 2013 MovLib
# LICENSE:    http://www.gnu.org/licenses/agpl.html AGPL-3.0
# LINK:       https://movlib.org/
# SINCE:      0.0.1-dev
# ----------------------------------------------------------------------------------------------------------------------

# Global defaults for various commands.
Exec    { path   => [ "/usr/bin", "/usr/sbin", "/usr/local/bin", "/usr/local/sbin" ] }
Package { ensure => "latest" }
Service { path   => "/etc/init.d" }

# Make sure that we have the latest package definitions.
exec { "apt-get update": }

# Ensure default software is installed.
package { [ "debconf-utils", "git", "subversion", "wget", "curl", "ntp", "gcc", "g++", "build-essential", "make" ]: }

# TODO: The following packages are for PHP installation, move to class!
package { [ "libxml2-dev", "libssl-dev", "libcurl4-openssl-dev", "libmcrypt-dev", "libtidy-dev", "autoconf" ]: }

# TODO: The following packages are for MovLib installation, move to class!
package { "pwgen": }

# Make sure machine time is always correctly synced.
service { "ntp":
  enable => true,
  ensure => "running",
}

#
# NOTE: Using an `include` means that the module does nothing by itself and is only used by another module.
#       Use `class` to include a module that's actually doing something and always specify the important parameters,
#       even if the are the defaults of the provided module.
#

# Module includes, dependencies for other later modules.
include apt
include wget

# Execute Puppet modules...
class { "timezone":
  autoupgrade => true,
  timezone    => "UTC",
}

class { "locales":
  default_locale => "en_US.UTF-8",
  autoupgrade    => true,
}

class { "mariadb":
  version => "10.0",
}

#class { "oracle_java_jdk":
#  version => "7",
#  release => "trusty",
#}

class { "elasticsearch":
  autoupgrade  => true,
  manage_repo  => true,
  repo_version => "1.0",
}
