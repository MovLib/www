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
 * Global MovLib configuration stub, this file is meant for IDE auto-completion and not for direct instantiation.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Configuration {

  /**
   * The name of the default database.
   *
   * @var string
   */
  public $databaseDefault = "movlib";

  /**
   * The name of the localize database.
   *
   * @var string
   */
  public $databaseLocalize = "movlib_localize";

  /**
   * The absolute path to the document root, e.g. <code>"/var/www"</code>.
   *
   * @var string
   */
  public $documentRoot = "/var/www";

  /**
   * The API domain, without scheme or trailing slash, e.g. <code>"api.movlib.org"</code>.
   *
   * @var string
   */
  public $domainAPI = "api.movlib.org";

  /**
   * The default domain, without scheme or trailing slash, e.g. <code>"movlib.org"</code>.
   *
   * @var string
   */
  public $domainDefault = "alpha.movlib.org";

  /**
   * The localize domain, without scheme or trailing slash, e.g. <code>"localize.movlib.org"</code>.
   *
   * @var string
   */
  public $domainLocalize = "localize.movlib.org";

  /**
   * The static domain, without scheme or trailing slash, e.g. <code>"static.movlib.org"</code>.
   *
   * @var string
   */
  public $domainStatic = "alpha.movlib.org";

  /**
   * The developer mailinglist email address.
   *
   * @var string
   */
  public $emailDevelopers = "developers@movlib.org";

  /**
   * The default from address for emails.
   *
   * @var string
   */
  public $emailFrom = "noreply@movlib.org";

  /**
   * The default from name for emails.
   *
   * @var string
   */
  public $emailFromName = '"MovLib, the free movie library."';

  /**
   * The webmaster email address.
   *
   * @var string
   */
  public $emailWebmaster = "webmaster@movlib.org";

  /**
   * The password cost for hashing the user passwords.
   *
   * @var string
   */
  public $passwordCost = 13;

  /**
   * The user name (for file permissions etc.).
   *
   * @var string
   */
  public $phpUser = "movdev";

  /**
   * The group name (for file permissions etc.).
   *
   * @var string
   */
  public $phpGroup = "www-data";

  /**
   * Flag indicating if the website is in production mode or not.
   *
   * @var boolean
   */
  public $production = false;

  /**
   * The site name, e.g. <code>"MovLib"</code>.
   *
   * @var string
   */
  public $siteName = "MovLib";

  /**
   * The site slogan, e.g. <code>"the free movie library"</code>.
   *
   * @var string
   */
  public $siteSlogan = "the free movie library";

  /**
   * Numeric array containing the system locales.
   *
   * @see \MovLib\Data\SystemLanguage
   * @see \MovLib\Data\SystemLanguages
   * @var array
   */
  public $systemLanguages = [ "de" => "de_AT", "en" => "en_US" ];

  /**
   * The version string.
   *
   * @var string
   */
  public $version = "0.0.1-dev";

}
