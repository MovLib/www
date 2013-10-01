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
namespace MovLib\Presentation\Validation;

use \MovLib\Exception\ValidationException;
use \MovLib\Data\User;

/**
 * Username validation.
 *
 * @see \MovLib\Presentation\Validation\InterfaceValidation
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Username extends \MovLib\Presentation\AbstractBase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Empty user object.
   *
   * @var \MovLib\Data\User
   */
  public $user;

  /**
   * The user's name to validate.
   *
   * @var string
   */
  public $username;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods

  /**
   * Instantiate new username validator.
   *
   * @param \MovLib\Data\User $user
   *   An empty user object.
   * @param string $username [optional]
   *   The username to validate, defaults to none.
   */
  public function __construct($user, $username = null) {
    $this->user = $user;
    $this->username = $username;
  }

  /**
   * Get the username.
   *
   * @return string
   *   The username.
   */
  public function __toString() {
    return $this->username ?: "";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Set the username to validate.
   *
   * @param string $username
   *   The username to validate.
   * @return this
   */
  public function set($username) {
    $this->username = $username;
    return $this;
  }

  /**
   * Validate the user's name.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return string
   *   The user's valid name.
   * @throws \MovLib\Exception\ValidationException
   */
  public function validate() {
    global $i18n;
    $errors = null;

    if (substr($this->username, 0, 1) == " ") {
      $errors[] = $i18n->t("The username cannot begin with a space.");
    }

    if (substr($this->username, -1) == " ") {
      $errors[] = $i18n->t("The username cannot end with a space.");
    }

    if (strpos($this->username, "  ") !== false) {
      $errors[] = $i18n->t("The username cannot contain multiple spaces in a row.");
    }

    if (strpos($this->username, "/") !== false) {
      $errors[] = $i18n->t("The username cannot contain slashes.");
    }

    if (mb_strlen($this->username) > User::MAX_LENGTH_NAME) {
      $errors[] = $i18n->t("The username is too long: it must be {0,number,integer} characters or less.", [ User::MAX_LENGTH_NAME ]);
    }

    if ($errors) {
      throw new ValidationException(implode("<br>", $errors));
    }

    if ($this->user->checkName($this->username) === true) {
      throw new ValidationException($i18n->t("The username {0} is already taken, please choose another one.", [ $this->placeholder($this->username) ]));
    }

    return $this->username;
  }

}
