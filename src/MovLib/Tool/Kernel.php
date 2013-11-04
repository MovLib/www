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
namespace MovLib\Tool;

/**
 * The tool kernel extends the default kernel and is targeted towards console, PHPUnit, or mixed execution.
 *
 * Mixed execution refers to interaction with other vendor software installed via composer.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Kernel extends \MovLib\Kernel {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Absolute path to the directory where user binaries are linked.
   *
   * @var string
   */
  public $usrBinaryPath = "/usr/local/bin";


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new Tool configuration.
   *
   * @global \MovLib\Tool\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Tool\Kernel $kernel
   * @global \MovLib\Data\User\Session $session
   */
  public function __construct() {
    global $db, $i18n, $kernel, $session;

    // The tool kernel has to ensure that the document root is always set to the actual MovLib document root without
    // tampering with any super global (which might destroy other software).
    $this->documentRoot = dirname(dirname(dirname(__DIR__)));

    // Transform all PHP errors to exceptions.
    set_error_handler([ $this, "errorHandler" ], -1);

    // Create global object instances.
    $db      = new \MovLib\Tool\Database();
    $i18n    = new \MovLib\Data\I18n();
    $kernel  = $this;
    $session = new \MovLib\Data\User\Session();
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Initialize environment for CLI usage.
   *
   * @param boolean $composer [optional]
   *   Should be set to <code>TRUE</code> if composer is involved.
   * @return this
   */
  public function initCLI($composer = false) {
    ini_set("display_errors", true);
    // There are too many PHP errors that might occurr and break composer!
    if ($composer) {
      restore_error_handler();
    }
    return $this;
  }

  /**
   * Initialize environment for PHPUnit usage.
   *
   * @global array $backup
   * @global \MovLib\Tool\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\User\Session $session
   * @return this
   */
  public function initPHPUnit() {
    global $backup, $db, $i18n, $session;

    // Most tests rely on a valid session, create one up front.
    $sessionInit = new \ReflectionMethod($session, "init");
    $sessionInit->setAccessible(true);
    $sessionInit->invokeArgs($session, [ 1 ]);

    // Set a user agent string for PHPUnit tests.
    $this->userAgent = ini_get("user_agent");

    // Establish connection to database to ensure that our clone has one.
    $db->connect();

    // Create backups of our global objects.
    $backup = [
      "db"      => clone $db,
      "i18n"    => clone $i18n,
      "kernel"  => clone $this,
      "session" => clone $session,
    ];

    return $this;
  }

}
