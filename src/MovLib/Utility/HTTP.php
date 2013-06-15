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
namespace MovLib\Utility;

/**
 * Miscellaneous static HTTP utility methods.
 *
 * HTTP provides shorthand methods for generating HTTP responses.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class HTTP {

  /**
   * RFC 2616 compliant redirect, this will end the current response.
   *
   * <b>Usage example:</b>
   * <pre>HTTP::redirect($i18n->r("/movie/{0}/release-{1}/discussion", [ $movieId, $releaseId ]));</pre>
   *
   * @param string $route
   *   The route to which the client should be redirected.
   * @param int $status
   *   [Optional] The HTTP response code (301, 302, or 303), defaults to 301.
   * @param string $domain
   *   [Optional] Overwrite the current domain, defaults to <var>$_SERVER["SERVER_NAME"]</var>.
   */
  public static function redirect($route, $status = 301, $domain = null) {
    $domain || $domain = $_SERVER["SERVER_NAME"];
    header("Location: https://{$domain}{$route}", true, $status);
    http_response_code($status); // Ensure status is set correctly
    if ($_SERVER["REQUEST_METHOD"] !== "HEAD") {
      $title = [ 301 => "Moved Permanently", 302 => "Moved Temporarily", 303 => "See Other" ];
      // Entity is required per RFC 2616. Our entity is identical to the one that nginx would return.
      echo "<html><head><title>{$status} {$title[$status]}</title></head><body bgcolor=\"white\"><center><h1>{$status} {$title[$status]}</h1></center><hr><center>nginx/{$_SERVER["SERVER_VERSION"]}</center></body></html>";
    }
    exit();
  }

}
