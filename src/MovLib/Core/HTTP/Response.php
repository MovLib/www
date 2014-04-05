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
namespace MovLib\Core\HTTP;

use \MovLib\Exception\ClientExceptionInterface;
use \MovLib\Presentation\Stacktrace;

/**
 * Represents the response that will be sent to the client.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Response {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Whether this request is cacheable or not.
   *
   * @var boolean
   */
  public $cacheable = false;

  /**
   * The default hostname.
   *
   * @var string
   */
  protected $hostname;

  /**
   * The active request instance.
   *
   * @var \MovLib\Core\HTTP\Request
   */
  protected $request;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new HTTP request object.
   *
   * @param \MovLib\Core\HTTP\Request $request
   *   The active request instance.
   * @param string $hostname
   *   The default hostname.
   */
  public function __construct(\MovLib\Core\HTTP\Request &$request, $hostname) {
    $this->cacheable = $request->methodGET;
    $this->hostname  = $hostname;
    $this->request  =& $request;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Create cookie.
   *
   * @param string $identifier
   *   The cookie's global unique identifier.
   * @param mixed $value
   *   The cookie's value.
   * @param integer $expire [optional]
   *   The cookie's time to life, defaults to <code>0</code> which means that the cookie will be deleted when the client
   *   closes it's user agent.
   * @param boolean $httpOnly [optional]
   *   Whether the cookie should be available via HTTP only (not accessible for JavaScript) or not. Defaults to
   *   <code>FALSE</code> and the cookie is available to anyone.
   * @return this
   */
  public function createCookie($identifier, $value, $expire = 0, $httpOnly = false) {
    setcookie($identifier, $value, $expire, "/", $this->hostname, $this->request->https, $httpOnly);
    return $this;
  }

  /**
   * Delete cookie(s).
   *
   * @param array|string $identifiers
   *   The cookie's global unique identifier(s) to delete.
   * @return this
   */
  public function deleteCookie($identifiers) {
    foreach ((array) $identifiers as $id) {
      $this->createCookie($id, "", 1);
    }
    return $this;
  }

}
