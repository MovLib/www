# `bin`
The `bin` directory contains executables (command line interfaces, mostly based on the Symfony2 Console project) and
also init scripts to start/stop/restart/reload services (e.g. nginx or memcached).

## The MovLib console command

You should create symbolic links to the executables for easy global access, the following is an example on how to
achieve this:

```Shell
# ln -s /var/www/bin/movlib.php /usr/local/bin/movlib && chmod 2770 /usr/local/bin/movlib
```

## Init scripts
The init scripts provided in the `bin` folder provide an easy and LSB compliant way of controlling all the services
needed for the MovLib software including the *nginx* webserver, *php-fpm* and *memcached*.
**Please note**: **memcached-server1** is currently unused by our software, but will soon be put into action.

### Putting the scripts into action
To put the init scripts into action, you also have to create symbolic links. The following example contains all the init
scripts currently in use by our server:

```Shell
# ln -s /var/www/bin/init-nginx.sh /etc/init.d/nginx && chmod 2770 /var/www/bin/init-nginx.sh
# ln -s /var/www/bin/init-php-fpm.sh /etc/init.d/php-fpm && chmod 2770 /var/www/bin/init-php-fpm.sh
# ln -s /var/www/bin/init-memcached-session.sh /etc/init.d/memcached-session && chmod 2770 /var/www/bin/init-memcached-session.sh
# ln -s /var/www/bin/init-memcached-server1.sh /etc/init.d/memcached-server1 && chmod 2770 /var/www/bin/init-memcached-server1.sh
```

**Please note**: Since **mysql** (aka MariaDB) already provides an init script, we don't have to link it!

### Starting/Stopping/Restarting/Reloading services
After linking the init scripts, you can use their service name (name of the symbolic link in `/etc/init.d`) to
start/stop/restart/reload the respective service. The following example shows the correct usage of the **service**
command when starting *nginx*:

```Shell
# service nginx start
```

### Managing the automatic start of services at boot time
To automatically start all the required services once the server machine boots is necessary to resume operations after
e.g. a power outage or a machine crash. The following example shows how to achieve that on **Debian**-based Linux systems.
**Please note**: The services have to be registered first, i.e. a symbolic link has to exist for each and every one of
them in `/etc/init.d`.

```Shell
# update-rc.d memcached-session defaults
# update-rc.d memcached-server1 defaults
# update-rc.d php-fpm defaults
# update-rc.d nginx defaults
# update-rc.d mysql defaults
```

## Weblinks
* http://symfony.com/doc/current/components/console/introduction.html
* https://github.com/symfony/Console
* https://wiki.debian.org/LSBInitScripts
* http://www.debian-administration.org/articles/28
