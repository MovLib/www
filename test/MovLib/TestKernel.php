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
namespace MovLib;

/**
 * The PHPUnit kernel.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class TestKernel extends \MovLib\Tool\Kernel {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Numeric array containing all delayed emails.
   *
   * The test kernel exposes this property for easy access in our tests. Furthermore the content of this property is
   * reset after each test.
   *
   * @var null|array
   */
  public $delayedEmails;

  /**
   * Numeric array containing all delayed methods.
   *
   * The test kernel exposes this property for easy access in our tests. Furthermore the content of this property is
   * reset after each test.
   *
   * @var null|array
   */
  public $delayedMethods;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new test kernel.
   *
   * @global array $backup
   * @global \MovLib\Tool\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\User\Session $session
   */
  public function __construct() {
    global $backup, $db, $i18n, $session;
    parent::__construct();

    // Most tests rely on a valid session, create one up front.
    $session->active          = true;
    $session->authentication  = $_SERVER["REQUEST_TIME"];
    $session->isAuthenticated = true;
    $session->userId          = 1;
    $session->userName        = "Fleshgrinder";
    $session->userTimeZone  = "Europe/Vienna";

    // Set a user agent string for PHPUnit tests.
    $this->userAgent          = ini_get("user_agent");

    // Create backups of our global objects.
    $backup = [
      "db"      => clone $db,
      "i18n"    => clone $i18n,
      "kernel"  => clone $this,
      "session" => clone $session,
    ];
  }

}
