<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link http://movlib.org/ MovLib}.
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
namespace MovLib\Tool;

/**
 * Extended MovLib configuration, containing development properties.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Configuration extends \MovLib\Configuration {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The tools domain, without scheme or trailing slash, e.g. <code>"tools.movlib.org"</code>.
   *
   * @var string
   */
  public $domainTools = "tools.movlib.org";

  /**
   * The secure tools domain, without scheme or trailing slash, e.g. <code>"secure.tools.movlib.org"</code>.
   *
   * @var string
   */
  public $domainSecureTools = "secure.tools.movlib.org";

  /**
   * Flag indicating if the client has authenticated via certificate.
   *
   * @var boolean
   */
  public $sslClientVerify = false;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new Tool configuration.
   *
   * @global \MovLib\Tool\Database $db
   */
  public function __construct() {
    global $db;

    // Instantiate new global developer database object if non is available yet.
    if (!$db) {
      $db = new \MovLib\Tool\Database();
    }

    // Check whetever the client authenticated against nginx.
    if (!empty($_SERVER["SSL_CLIENT_VERIFY"])) {
      $this->sslClientVerify = $_SERVER["SSL_CLIENT_VERIFY"] == "SUCCESS";
    }
  }

}
