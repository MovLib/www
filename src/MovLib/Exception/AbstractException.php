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
 * The MovLib abstract exception is the base class for all exceptions and used to extend the default exception
 * implementation of PHP. Our exceptions are always SPL runtime exceptions, if you need to throw another kind of
 * exception, please use the other {@link http://www.php.net/manual/en/spl.exceptions.php SPL exception classes}.
 *
 * @link http://ralphschindler.com/2010/09/15/exception-best-practices-in-php-5-3
 *   Ralph Schindler: Exception best practices in PHP 5.3
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class AbstractException extends \RuntimeException {

  /**
   * Construct the exception.
   *
   * We re-order the exception parameter and make the message mandatory.
   *
   * @param string $message
   *   The exception message to throw.
   * @param string $previous [optional]
   *   The previous exception used for the exception chaining.
   * @param string $code [optional]
   *   The exception code, default to <var>E_RECOVERABLE_ERROR</var>.
   */
  public function __construct($message, $previous = null, $code = E_RECOVERABLE_ERROR) {
    parent::__construct($message, $code, $previous);
  }

}
