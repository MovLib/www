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
 * Base implementation for redirect exceptions.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractRedirectException extends \MovLib\Exception\AbstractException {

  /**
   * The redirect's location header string.
   *
   * This <b>must</b> be sent in main because it has side effects!
   *
   * @var string
   */
  public $locationHeader;

  /**
   * The payload as per {@link http://www.ietf.org/rfc/rfc2616.txt RFC 2616}.
   *
   * @todo Do we really have to send this response ourself or is nginx handling this?
   * @var int
   */
  public $presentation;

  /**
   * Instantiate new redirect exception.
   *
   * @param int $httpResponseCode
   *   The redirect's status code.
   * @param string $route
   *   The redirect's translated target route (location).
   * @param string $title
   *   The redirect's translated payload title.
   */
  public function __construct($httpResponseCode, $route, $title) {
    parent::__construct("Redirecting user to {$route} with status {$httpResponseCode}.");
    if (strpos($route, "http") === false) {
      $route = "{$_SERVER["SERVER"]}{$route}";
    }
    http_response_code($httpResponseCode);
    $this->locationHeader = "Location: {$route}";
    $this->presentation   = "<html><head><title>{$httpResponseCode} {$title}</title></head><body bgcolor=\"white\"><center><h1>{$httpResponseCode} {$title}</h1></center><hr><center>nginx/{$_SERVER["SERVER_VERSION"]}</center></body></html>";
  }

}
