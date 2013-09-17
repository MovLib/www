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
namespace MovLib\Exception\Client;

/**
 * Represents the "forbidden" client error.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class ForbiddenException extends \MovLib\Exception\Client\AbstractClientException {

  /**
   * Instantiate forbidden exception.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param string $message
   *   The exception message.
   * @param \MovLib\Exception\AbstractException $previous
   *   The previous exception.
   * @param int $code
   *   The exception code.
   */
  public function __construct($message, $previous = null, $code = E_NOTICE) {
    global $i18n;
    parent::__construct(
      $message,
      $previous,
      $code,
      $i18n->t("Forbidden"),
      $i18n->t("Access to the requested page is forbidden."),
      $i18n->t(
        "There can be various reasons why you might see this error message. If you feel that receiving this error is a mistake please {0}contact us{1}.",
        [ "<a href='{$i18n->r("/contact")}'>", "</a>" ]
      ),
      400
    );
  }

}
