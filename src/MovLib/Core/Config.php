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
namespace MovLib\Core;

/**
 * Defines the default configuration.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Config {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Document root relative path to the serialized configuration file.
   *
   * @var string
   */
  const PATH = "/etc/movlib/movlib.ser";

  /**
   * URI to the serialized configuration file.
   *
   * @todo We can reuse the PATH constant in PHP 5.6
   * @var string
   */
  const URI = "dr:///etc/movlib/movlib.ser";


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The document root.
   *
   * Should point to the symbolic link that links to the real files. This is mainly used for CLI and configuration of
   * software that should point to the symbolic link rather than the real directory where the source files reside.
   *
   * @var string
   */
  public $documentRoot = "/var/www";

  /**
   * Developer mailing list.
   *
   * @var string
   */
  public $emailDevelopers = "developers@movlib.org";

  /**
   * Default FROM email address.
   *
   * @var string
   */
  public $emailFrom = "noreply@movlib.org";

  /**
   * Webmaster email address.
   *
   * @var string
   */
  public $emailWebmaster = "webmaster@movlib.org";

  /**
   * The system group.
   *
   * @var string
   */
  public $group = "www-data";

  /**
   * The default hostname.
   *
   * @var string
   */
  public $hostname = "movlib.org";

  /**
   * The hostname for static content.
   *
   * We use the same domain for static content all over our subdomains. While it's not the best for performance, it's
   * the best for caching, because it doesn't matter where a client is browsing, the file will always be pointing to
   * the same domain and if the client's cache has fetched the file on one subdomain it stays cached on the other.
   *
   * @var string
   */
  public $hostnameStatic = "movlib.org";

  /**
   * The password hashing algorithm.
   *
   * @link http://php.net/manual/password.constants.php
   * @var integer
   */
  public $passwordAlgorithm = PASSWORD_BCRYPT;

  /**
   * Password options.
   *
   * @see Config::$passwordAlgorithm
   * @var array
   */
  public $passwordOptions = [ "cost" => 12 ];

  /**
   * Whether this is a production system or not.
   *
   * @var boolean
   */
  public $production = false;

  /**
   * The session name.
   *
   * @var string
   */
  public $sessionName = "MOVSID";

  /**
   * The site's name.
   *
   * @var string
   */
  public $sitename = "MovLib";

  /**
   * The default time zone.
   *
   * @var string
   */
  public $timezone = "UTC";

  /**
   * The system user.
   *
   * @var string
   */
  public $user = "movdev";

}
