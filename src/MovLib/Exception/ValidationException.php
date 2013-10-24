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
namespace MovLib\Exception;

/**
 * A Validation exception might be thrown if a user supplied value doesn't match the applied validation rules.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class ValidationException extends \RuntimeException {

  /**
   * Instantiate new validation exception.
   *
   * @param string|array $message
   *   The string or numeric array of strings with messages explaining the problem.
   * @param int $code [optional]
   *   The exception code, defaults to <code>0</code>.
   * @param \Exception $previous [optional]
   *   The previous exception, default to <code>NULL</code>.
   */
  public function __construct($message, $code = 0, $previous = null) {
    parent::__construct(is_array($message) ? implode("<br>", $message) : $message, $code, $previous);
  }

  /**
   * Append messages to this exceptions message.
   *
   * @param string|array $append
   *   A string or numeric array of strings containing the message(s) to append to this exceptions message.
   * @return this
   */
  public function appendToMessage($append) {
    if (is_array($append)) {
      $append = implode("<br>", $append);
    }
    $this->message .= "<br>{$append}";
    return $this;
  }

}
