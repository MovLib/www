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
namespace MovLib\Stub;

/**
 * Environment configuration stub.
 *
 * This stub is used for IDE auto-completion and for documentation auto-generation for
 * <code>conf/movlib.dist.json</code> and respective environment configuration files. Each value contains the default
 * value from the aformentioned <i>dist</i> file, but they aren't actually used anywhere, the JSON files are combined
 * depending on the environment into a single file that is placed at <code>conf/movlib.json</code>. This means that the
 * <code>conf/movlib.json</code> will be a combination of <code>conf/movlib.dist.json</code> and
 * <code>conf/movlib.vagrant.json</code> if your machine is running within our Vagrant setup. Also note that the
 * contents are only constantly read from the file in a development or CLI environment. The contents are hard coded
 * within the Kernel class on deployment for the real production system to improved performance and get read of the
 * unnecessary file read and parse process.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Configuration {

  /**
   * The machine's hostname.
   *
   * @var string
   */
  public $hostname = "movlib.org";

  /**
   * The machine's locale.
   *
   * @var string
   */
  public $locale = "en_US";

  /**
   * The machine's time zone.
   *
   * @var string
   */
  public $timezone = "Etc/UTC";

  /**
   * The email address of the webmaster.
   *
   * @var string
   */
  public $webmaster = "webmaster@movlib.org";

  /**
   * The machine's user (PHP, nginx, ...).
   *
   * @var string
   */
  public $user = "movlib";

  /**
   * The machine's group (PHP, nginx, ...).
   *
   * @vars string
   */
  public $group = "movlib";

  /**
   * The MovLib version.
   *
   * @link http://semver.org/
   * @var string
   */
  public $version = "0.0.1";

  /**
   * The MovLib release.
   *
   * @link http://semver.org/
   * @var string
   */
  public $release = "dev";

  /**
   * The machine's directories for the environment.
   *
   * @var \MovLib\Stub\Configuration\Directory
   */
  public $directory;

  /**
   * The machine's PHP configuration.
   *
   * @var \MovLib\Stub\Configuration\PHP
   */
  public $php;

  /**
   * The machine's MariaDB configuration.
   *
   * @var \MovLib\Stub\Configuration\MariaDB
   */
  public $mariadb;

  /**
   * The machine's nginx configuration.
   *
   * @var \MovLib\Stub\Configuration\Nginx
   */
  public $nginx;

  /**
   * The machine's nodejs configuration.
   *
   * @var \MovLib\Stub\Configuration\Nodejs
   */
  public $nodejs;

  /**
   * The machine's Java configuration.
   *
   * @var \MovLib\Stub\Configuration\Java
   */
  public $java;

  /**
   * The machine's Elasticsearch configuration.
   *
   * @var \MovLib\Stub\Configuration\Elasticsearch
   */
  public $elasticsearch;

}
