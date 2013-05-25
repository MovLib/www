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
namespace MovLib\Entity;

/**
 *
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class User {

  /**
   * The CSRF token for this user's session.
   *
   * @var string
   */
  private $csrfToken;

  public function __construct() {
    $this->csrfToken = $this->sessionGet("csrf_token");
  }

  /**
   * Get a CSRF token for a form to validate that the user really wanted to submit this form.
   *
   * @link https://owasp.org/index.php/Cross-Site_Request_Forgery_%28CSRF%29_Prevention_Cheat_Sheet
   * @link https://owasp.org/index.php/PHP_CSRF_Guard
   * @return string
   *   The unique CSRF token.
   */
  public function getCsrfToken() {
    return $this->csrfToken;
  }

  /**
   * @return boolean
   *   <tt>TRUE</tt> if the current user is logged in, otherwise <tt>FALSE</tt>.
   */
  public function loggedIn() {
    return false;
  }

  /**
   * Retrieve value identified by key from session.
   *
   * @param string $key
   *   The key for selecting the value from the session.
   * @param mixed $default
   *   [Optional] The content of this variable is returned if the session does not contain any value.
   * @return mixed
   *   The value identified by key if present else value from <var>$default</var>.
   */
  private function sessionGet($key, $default = false) {
    if (isset($_SESSION[$key]) === true) {
      return $_SESSION[$key];
    }
    return $default;
  }

  /**
   * Start session if none is active.
   *
   * @return $this
   */
  private function sessionStart() {
    if (session_status() === PHP_SESSION_NONE) {
      // IMPORTANT! There is nothing to set up at this point, use the PHP configuration of your VPS to do so and check
      //            the /conf/php folder for the configuration that is used by MovLib.
      session_start();
      $this->csrfToken = hash("sha512", mt_rand(0, mt_getrandmax()));
      $this->sessionStore("csrf_token", $this->csrfToken);
    }
    return $this;
  }

  /**
   * Store a value identified by key in the users session.
   *
   * @param string $key
   *   Unique key for identification.
   * @param mixed $value
   *   The value to store.
   * @return $this
   */
  private function sessionStore($key, $value) {
    $this->sessionStart();
    $_SESSION[$key] = $value;
    return $this;
  }

  /**
   * Validate a user submitted CSRF token against the token present in the user's session.
   *
   * @param string $submittedToken
   *   The user submitted CSRF token.
   * @return boolean
   *   <tt>TRUE</tt> if the token matches, <tt>FALSE</tt> if no token is present in the session or they are not equal.
   */
  public function validateCsrfToken($submittedToken) {
    return $this->csrfToken === $submittedToken;
  }

}
