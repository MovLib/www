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
namespace MovLib\Exception;

/**
 * An unauthorized exception might be thrown is a user is not allowed to access some specific content.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class UnauthorizedException extends \MovLib\Exception\AbstractException {

  /**
   * The title.
   *
   * @var string
   */
  protected $title;

  /**
   * Instantiate new unauthorized exception.
   *
   * @param string $message [optional]
   *   The message that should be displayed to the user. Please note that you cannot change the title of the alert!
   */
  public function __construct($message = null, $title = null) {
    global $i18n;
    parent::__construct("{$title} {$message}");
    $this->title = empty($title) ? $i18n->t("You must be signed in to access this content.") : $title;
    if (empty($this->message)) {
      $this->message = "<p>{$i18n->t("Please use the form below to sign in or go to the {0}registration page to sign up{1}.", [ "<a href='{$i18n->r("/user/register")}'>", "</a>" ])}</p>";
    }
  }

  /**
   * Get the title.
   *
   * @return string
   */
  public function getTitle() {
    return $this->title;
  }

}
