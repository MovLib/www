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
 * Permanently redirect the user.
 *
 * Sends a permanent redirect back to the client, please note that this might preserve the HTTP method (GET, POST). The
 * {@link http://www.ietf.org/rfc/rfc2616.txt RFC 2616} says that clients should preserve the HTTP method and that any
 * other behavior is "erroneous".
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class RedirectPermanentException extends \MovLib\Exception\Client\AbstractRedirectException {

  /**
   * Instantiate new permanent redirect.
   *
   * @param string $route
   *   {@inheritdoc}
   */
  public function __construct($route) {
    parent::__construct(301, $route, "Moved Permanently");
  }

}
