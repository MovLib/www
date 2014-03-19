# PHP configuration
PHP configuration used at [MovLib](http://movlib.org/). MovLib is meant to be running in a root or VPS environment.
Therefor we keep the configuration files within our application for easy deployment (even after software upgrades).

* The `configure` file contains the flags passed to configuring PHP before utilizing make.
* The `init` is the script we use to start php-fpm via sysvinit on our Debian operating system.
* The `php.ini` is the global PHP configuration.
* The `fpm.ini` is the global PHP FastCGI Process Manager configuration.
* The `pool.ini` file is for inclusion in the `fpm.ini` file because we don't repeat ourselfs and therefor only write the FPM pool configuration once.
