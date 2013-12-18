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
namespace MovLib\Presentation\Redirect;

/**
 * Temporarily redirect the user and transform the HTTP method to GET.
 *
 * This redirect should be used if the requested operation has completed and the client should continue elsewhere while
 * transforming the request method to GET.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class SeeOther extends \MovLib\Presentation\Redirect\AbstractRedirect {

  /**
   * Instantiate new see other redirect.
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
      parent::__construct(303, $route, "See Other");
    }
  }

}