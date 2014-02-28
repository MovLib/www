# Class: movlib
#
# This module manages MovLib specific stuff.
#
# Bug: It's not possible to define the hiera_array() lookup in the class definition, see:
#      https://tickets.puppetlabs.com/browse/HI-118
class movlib(
  $document_root     = "/var/www",
  $packages          = undef,
  $packages_testing  = undef,
  $packages_unstable = undef,
) {

  # --------------------------------------------------------------------------------------------------------------------
  #                                                                                                               Debian
  # --------------------------------------------------------------------------------------------------------------------
  # pub   4096R/46925553 2012-04-27 [expires: 2020-04-25]
  # uid                  Debian Archive Automatic Signing Key (7.0/wheezy) <ftpmaster@debian.org>

  if $::operatingsystem == 'Debian' {

    # ----------------------------------------------------------------------------------------------------------- Stable

    # Set up proper pinning for stable Debian releases.
    apt::pin{ 'debian_stable':
      priority   => 500,
      originator => 'Debian',
      release    => 'stable',
    }

    # Execute all save package updates.
    exec { 'apt_upgrade':
      command => '/usr/bin/apt-get upgrade -y',
      require => Exec['apt_update'],
    }

    # Add Puppetlabs source.
    apt::source { 'puppetlabs':
      location   => 'http://apt.puppetlabs.com',
      repos      => 'main',
      key        => '4BD6EC30',
      key_server => 'pgp.mit.edu',
      pin        => '-10',
    }

    # Install packages, if any.
    if $packages {
      $pkgs = hiera_array('movlib::packages')
      package { $pkgs:
        require => Exec['apt_update'],
      }
    }

    # ---------------------------------------------------------------------------------------------------------- Testing

    if $packages_testing {
      apt::source { 'debian_testing':
        location          => 'http://ftp.at.debian.org/debian/',
        release           => 'testing',
        repos             => 'main',
        required_packages => 'debian-keyring debian-archive-keyring',
        key               => '46925553',
        key_server        => 'subkeys.pgp.net',
      }

      apt::pin { 'debian_testing_pin':
        priority   => '-10',
        originator => 'Debian',
        release    => 'testing',
        require    => Apt::Source['debian_testing'],
      }

      apt::force { $pkgst:
        release => 'testing',
        require => [ Exec['apt_update'], Apt::Source['debian_testing'], Apt::Pin['debian_testing_pin'] ],
      }
    }

    # --------------------------------------------------------------------------------------------------------- Unstable

    if $packages_unstable {
      apt::source { 'debian_unstable':
        location          => 'http://ftp.at.debian.org/debian/',
        release           => 'unstable',
        repos             => 'main',
        required_packages => 'debian-keyring debian-archive-keyring',
        key               => '46925553',
        key_server        => 'subkeys.pgp.net',
        pin               => '-10',
      }

      apt::pin { 'debian_unstable_pin':
        priority   => '-10',
        originator => 'Debian',
        release    => 'unstable',
        require    => Apt::Source['debian_unstable'],
      }

      $pkgsu = hiera_array('movlib::packages_unstable')
      apt::force { $packages_unstable:
        release => 'unstable',
        require => [ Exec['apt_update'], Apt::Source['debian_unstable'], Apt::Pin['debian_unstable_pin'] ],
      }
    }
  }

}
