<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link https://movlib.org/ MovLib}.
 *
 * MovLib is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public
 * License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later
 * version.
 *
 * MovLib is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License along with MovLib.
 * If not, see {@link http://www.gnu.org/licenses/ gnu.org/licenses}.
 */
namespace MovLib\Tool\Console\Command\Production;

use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * Install PHP.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class InstallPHP extends \MovLib\Tool\Console\Command\AbstractInstall {

  /**
   * @inheritdoc
   */
  public function __construct() {
    parent::__construct("install-php", "php");
  }

  /**
   * @inheritdoc
   */
  protected function configure() {
    $this->setDescription("Remove installed PHP and install new version.");
    $this->addArgument("version", InputArgument::REQUIRED, "The PHP version to install.");
  }

  /**
   * @inheritdoc
   * @throws",MovLib\Exception\ConsoleException
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $options = parent::execute($input, $output);
    $this->checkPrivileges()->installPHP($input->getArgument("version"));
    return $options;
  }

  /**
   * Install PHP.
   *
   * @param string $version
   *   The version to install.
   * @return this
   * @throws",InvalidArgumentException
   */
  protected function installPHP($version) {
    $this->setVersion($version);
    $name = $this->getInstallationName();
    if (preg_match("/[0-9]\.[0-9]\.[0-9]/", $version) == false) {
      $downloadURL    = "http://us1.php.net/distributions/";
      $nameAndVersion = "{$name}-{$version}";
    }
    else {
      $downloadURL    = "http://snaps.php.net/";
      $nameAndVersion = "{$name}{$version}";
    }
    $this->uninstall();
    chdir(self::SOURCE_DIRECTORY);
    if (!file_exists("{$nameAndVersion}.tar.gz")) {
      $this->wget("{$downloadURL}{$nameAndVersion}.tar.gz");
      $this->tar("{$nameAndVersion}.tar.gz");
    }
    chdir($nameAndVersion);
    $this->configureInstallation([
      "--disable-flatfile",
      "--disable-inifile",
      "--disable-short-tags",
      "--enable-bcmath",
      "--enable-fpm",
      "--enable-intl",
      "--enable-libgcc",
      "--enable-libxml",
      "--enable-mbstring",
      "--enable-mysqlnd",
      "--enable-opcache",
      "--enable-pcntl",
      "--enable-re2c-cgoto",
      "--enable-xml",
      "--enable-zend-signals",
      "--enable-zip",
      "--sysconfdir='/etc/php-fpm'",
      "--with-config-file-path='/etc/php-fpm'",
      "--with-curl",
      "--with-fpm-group='www-data'",
      "--with-fpm-user='www-data'",
      "--with-icu-dir='/usr/local'",
      "--with-mcrypt='/usr/lib/libmcrypt'",
      "--with-mysql-sock='/run/mysqld/mysqld.sock'",
      "--with-mysqli",
      "--with-openssl",
      "--with-pcre-regex",
      "--with-pear",
      "--with-zend-vm='GOTO'",
      "--with-zlib",
    ], "CFLAGS='-O3 -m64 -DMYSQLI_NO_CHANGE_USER_ON_PCONNECT' CXXFLAGS='-O3 -m64'");
    $this->install();
    return $this;
  }

}
