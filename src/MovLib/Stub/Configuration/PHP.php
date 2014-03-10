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
namespace MovLib\Stub\Configuration;

/**
 * PHP configuration stub.
 *
 * <b>Note:</b> PHP is not installed with the usual Provision PHP CLI command for obvious reasons. It's together with
 * Composer the only software that is installed via Shell scripts. Both PHP and Composer are responsible for providing
 * the base software for everything else. Like Ruby is for Vagrant/Puppet. Both Shell script (for PHP and Composer) are
 * in the <code>bin</code> directory (there's also a special <code>bin/vagrant.sh</code> for starting the Vagrant
 * provisioning process).
 *
 * @see bin/install-php.sh
 * @see bin/install-composer.sh
 * @see bin/vagrant.sh
 * @see \MovLib\Stub\Configuration
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class PHP {

  /**
   * The checksum for the downloaded archive of <var>PHP::$version</var>.
   *
   * @todo Change to validating via PGP!
   * @var string
   */
  public $checksum;

  /**
   * The absolute path to the php-fpm daemon.
   *
   * @var string
   */
  public $daemon = "/usr/local/sbin/php-fpm";

  /**
   * The arguments that should be passed to the daemon on start-up.
   *
   * @var string
   */
  public $daemonArguments = "";

  /**
   * Absolute URL to download the source archive.
   *
   * <b>Note:</b> <code>"{{ version }}"</code> is replaced by <var>Nginx::$version</var>.
   *
   * @var string
   */
  public $downloadURL = "http://nginx.org/download/nginx-{{ version }}.tar.gz";

  /**
   * The machine's PHP version.
   *
   * @var string
   */
  public $version;

}
