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
 * Nginx configuration stub.
 *
 * <b>Note:</b> We compile nginx from source for best performance and highest customization factor.
 *
 * @link http://nginx.org/
 * @see \MovLib\Stub\Configuration
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Nginx {

  /**
   * The nginx configure flags and arguments.
   *
   * @var \MovLib\Stub\Configuration\Configure
   */
  public $configure;

  /**
   * The OpenSSL version to compile into nginx.
   *
   * @link https://www.openssl.org/
   * @var string
   */
  public $opensslVersion;

  /**
   * Array containing module names and their download location that should be added to nginx.
   *
   * @var array
   */
  public $modules = [];

  /**
   * The PCRE version to compile into nginx.
   *
   * @link http://www.pcre.org/
   * @var string
   */
  public $pcreVersion;

  /**
   * The machine's nginx version.
   *
   * @var string
   */
  public $version;

}
