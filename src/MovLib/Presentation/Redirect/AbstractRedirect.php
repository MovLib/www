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
 * Base implementation for redirect exceptions.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractRedirect extends \MovLib\Exception\AbstractClientException {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The HTTP status code.
   *
   * @var integer
   */
  protected $responseCode;

  /**
   * The HTTP location route.
   *
   * @var string
   */
  protected $locationRoute;

  /**
   * The redirect title.
   *
   * @var string
   */
  protected $title;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new redirect exception.
   *
   * @global \MovLib\Data\Cache $cache
   * @global \MovLib\Kernel $kernel
   * @param int $httpResponseCode
   *   The redirect's status code.
   * @param string $route
   *   The redirect's translated target route (location).
   * @param string $title
   *   The redirect's translated payload title.
   */
  public function __construct($httpResponseCode, $route, $title) {
    global $cache, $kernel;
    $cache->cacheable = false;
    if (strpos($route, "http") === false) {
      $route = "{$kernel->scheme}://{$kernel->hostname}{$route}";
    }
    parent::__construct("Redirecting user to {$route} with status {$httpResponseCode}.");
    $this->responseCode  = $httpResponseCode;
    $this->locationRoute = $route;
    $this->title         = $title;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  public function getPresentation() {
    http_response_code($this->responseCode);
    header("Location: {$this->locationRoute}");
    return
      "<!doctype html>" .
      "<html>" .
      "<head><title>{$this->responseCode} {$this->title}</title></head>" .
      "<body style='text-align:center'><h1>{$this->responseCode} {$this->title}</h1><hr>{$_SERVER["SERVER_SOFTWARE"]}</body>" .
      "</html>"
    ;
  }

}
