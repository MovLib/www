# MovLib Configuration
The `conf`-directory contains several configuration files and documentation related to the configurations.

## Operating System stuff
Things one might want to configure as well (to be extended):
* Set the time zone: `dpkg-reconfigure tzdata`
* Install an NTP program to keep the clock accurate: `aptitude install ntp` (might be installed already, run `/etc/init.d/ntp start`)
* Set the default locale: `dpkg-reconfigure locales` (we use `en_US.UTF-8`)

## Software needed to run MovLib
* nginx 1.4+
* MariaDB 10+
* PHP 5.5+
  * PEAR, PECL and PHP CLI
  * Memcached
  * libmemcached
  * ImageMagick
  * pwgen
  * much more ...
