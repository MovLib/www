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
   * URI to the serialized configuration file.
   *
   * @var string
   */
  const URI = "dr://etc/movlib/movlib.ser";


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The default language code.
   *
   * <b>NOTE</b><br>
   * Automatically set based on default locale.
   *
   * @see Config::$defaultLocale
   * @var string
   */
  public $defaultLanguageCode;

  /**
   * The default locale.
   *
   * @var string
   */
  public $defaultLocale = "en_US";

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
   * Supported system language codes and locales.
   *
   * Each key contains a ISO 639-1 language code and the value is the associated locale (combination of aforementioned
   * language code followed by an underline (<code>"_"</code>) and the ISO 3166-1 alpha-2 country code).
   *
   * @var array
   */
  public $locales = [ "de_AT", "en_US" ];

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
   * The site name.
   *
   * @var string
   */
  public $siteName = "MovLib";

  /**
   * The system user.
   *
   * @var string
   */
  public $user = "movdev";

  /**
   * The full software version.
   *
   * @link http://semver.org/
   * @var string
   */
  public $version = "0.0.1-dev";


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new default configuration.
   */
  public function __construct() {
    $this->setDefaultLocale($this->defaultLocale);
    $this->setLocales($this->locales);
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Set the default locale.
   *
   * @param string $locale
   *   The default locale to set.
   * @return this
   * @throws \InvalidArgumentException
   */
  public function setDefaultLocale($locale) {
    if (strlen($locale) !== 5) {
      throw new \InvalidArgumentException("A locale has to be exactly 5 characters long (e.g. 'en_US').");
    }
    if (!in_array($locale, $this->locales)) {
      throw new \InvalidArgumentException("The default locale must be part of available system locales, currently the following system locales are configured: " . implode(", ", $this->languages));
    }

    $this->defaultLanguageCode = "{$locale[0]}{$locale[1]}";
    $this->defaultLocale       = $locale;

    return $this;
  }

  /**
   * Set the locales.
   *
   * @param array $locales
   *   The locales to set.
   * @return this
   */
  public function setLocales($locales) {
    if (!in_array($this->defaultLocale, $locales)) {
      throw new \InvalidArgumentException("The current default locale '{$this->defaultLocale}' is not part of the new available locales: " . implode(", ", $locales));
    }
    $this->locales = [];
    foreach ($locales as $locale) {
      $this->locales["{$locale[0]}{$locale[1]}"] = $locale;
    }
    return $this;
  }

}
