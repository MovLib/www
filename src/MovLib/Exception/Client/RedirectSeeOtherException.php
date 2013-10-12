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
namespace MovLib\Exception\Client;

/**
 * Temporarily redirect the user and transform the HTTP method to GET.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class RedirectSeeOtherException extends \MovLib\Exception\Client\AbstractRedirectException {

  /**
   * Instantiate new temporary redirect.
   *
   * @param string $route
   *   {@inheritdoc}
   */
  public function __construct($route) {
    if ($_SERVER["SERVER_PROTOCOL"] == "HTTP/1.0") {
      parent::__construct(302, $route, "Moved Temporarily");
    }
    else {
      parent::__construct(303, $route, "See Other");
    }
  }

}
