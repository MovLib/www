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
 * Provision configuration stub class.
 *
 * This stub is used for IDE auto-completion and for documentation auto-generation for <code>conf/provision.json</code>.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class Provision {

  /**
   * The machine's time zone.
   *
   * @var string
   */
  public $timeZone;

  /**
   * The machine's locales.
   *
   * Contains two properties:
   * <ul>
   *   <li><code>"available"</code> is an array containing all available locales.</li>
   *   <li><code>"default"</code> is a string containing the default locale.</li>
   * </ul>
   *
   * A locale always consists of two two-letter codes where the first defines the language (e.g. <code>"en"</code>) and
   * the second one (all uppercase) the country (e.g. <code>"US"</code>). Both codes are combined with an underscore,
   * full example <code>"en_US"</code>. The locales are OS dependend, not every locale is available in every OS. For
   * instance <code>"de_AT"</code> is not available for Debian.
   *
   * Please note that the locale is always hard-coded to <code>"UTF-8"</code>, you cannot change that!
   *
   * @var \stdClass
   */
  public $locales;

}
