# nginx configuration
Nginx configuration used at [MovLib](http://movlib.org/). MovLib is meant to be running in a root or VPS environment;
therefor we keep the configuration files within our application for easy deployment (even after software upgrades).

* The `conf` folder contains configuration files that are globally included.
* The `sites` folder contains server (in Apache terms vhost) specific configuration files.
  * The `sites/conf` folder contains server specific configuration files that are the same for all servers.
  * The `sites/conf/routes` folder contains reusable location blocks and is used for generated / localized routes.
* The `ssl` folder contains SSL certificate configurations.

Please note that the naming scheme of the files in these folders is not following the normal OO standards. This is
because the filenames should be as specific as possible and/or match their server name in case of the server
configuration files.

## Weblinks
* [Calomel: Nginx Secure Web Server https://calomel.org/nginx.html]
* [Getting IPv6 connectivity under Linux](http://www.pps.univ-paris-diderot.fr/~jch/software/ipv6-connectivity.html)
