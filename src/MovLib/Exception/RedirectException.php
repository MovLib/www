<?php

/* !
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

namespace MovLib\Exception;

use \RuntimeException;

/**
 * Description of RedirectException
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class RedirectException extends RuntimeException {

  /**
   * The redirect's target route.
   *
   * @var string
   */
  public $route;

  /**
   * The redirect's HTTP status code.
   * @var int
   */
  public $status;

  /**
   * Instantiate new redirect exception.
   *
   * @param string $route
   *   The already translated route.
   * @param int $status [optional]
   *   The HTTP redirect status code, one of <code>301</code>, <code>302</code>, or <code>303</code>. Defaults to
   *   <code>301</code>.
   */
  public function __construct($route, $status = 301) {
    parent::__construct("Redirecting user to {$route} with status {$status}.", E_NOTICE, null);
    if (strpos($route, "http") === false) {
      $route = "{$_SERVER["SCHEME"]}://{$_SERVER["SERVER_NAME"]}{$route}";
    }
    $this->route = $route;
    $this->status = $status;
  }

}
