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

use \Locale;
use \MovLib\Tool\Database;
use \MovLib\Data\I18n;
use \MovLib\Data\StreamWrapper\StreamWrapperFactory;
use \MovLib\Data\User\Session;

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
   * The global configuration.
   *
   * @var \MovLib\Stub\Configuration
   */
  public $configuration;

  /**
   * Whether we're running under Windows or not.
   *
   * @var boolean
   */
  public $isWindows;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new Tool configuration.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Kernel $kernel
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\User\Session $session
   * @param boolean $composer [optional]
   *   Set this to <code>TRUE</code> if composer is in use.
   */
  public function __construct($composer = false) {
    global $db, $kernel, $i18n, $session;

    // Transform ALL PHP errors to exceptions unless this is executed in composer context, too many vendor supplied
    // software is casting various deprecated or strict errors.
    if ($composer === false) {
      set_error_handler([ $this, "errorHandler" ], -1);
    }

    // The tool kernel has to ensure that the document root is always set to the actual MovLib document root without
    // tampering with any super global (which might destroy other software).
    $this->documentRoot     = dirname(dirname(dirname(__DIR__)));
    $this->fastCGI          = isset($_SERVER[ "FCGI_ROLE" ]);
    $this->pathTranslations = "{$this->documentRoot}{$this->pathTranslations}";
    $this->production       = !is_dir("{$this->documentRoot}/.git");
    $this->isWindows        = defined("PHP_WINDOWS_VERSION_MAJOR");
    $kernel                 = $this;
    $db                     = new Database();
    $i18n                   = new I18n(Locale::getDefault());
    $session                = new Session();

    StreamWrapperFactory::register();

    // Get the global configuration if present.
    $configuration = "dr://etc/movlib/movlib.json";
    if (file_exists($configuration)) {
      $this->configuration = json_decode(file_get_contents($configuration));
      $this->systemUser    =& $this->configuration->user;
      $this->systemGroup   =& $this->configuration->group;
    }
  }

}
