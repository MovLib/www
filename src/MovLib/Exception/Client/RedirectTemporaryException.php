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
namespace MovLib\Exception\Client;

/**
 * Temporarily redirect the user.
 *
 * Sends a temporary redirect back to the client, please note that this might preserve the HTTP method (GET, POST). The
 * {@link http://www.ietf.org/rfc/rfc2616.txt RFC 2616} says that clients should preserve the HTTP method and that any
 * other behavior is "erroneous".
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class RedirectTemporaryException extends \MovLib\Exception\Client\AbstractRedirectException {

  /**
   * Instantiate new temporary redirect.
   *
   * @global \MovLib\Kernel $kernel
   * @param string $route
   *   {@inheritdoc}
   */
  public function __construct($route) {
    global $kernel;
    if ($kernel->protocol == "HTTP/1.0") {
      parent::__construct(302, $route, "Moved Temporarily");
    }
    else {
      parent::__construct(307, $route, "Temporary Redirect");
    }
  }

}
