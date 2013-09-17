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

use \MovLib\Presentation\Partial\Alert;

/**
 * Parent class for all HTTP client presentation errors.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractClientException extends \MovLib\Exception\AbstractException {

  /**
   * The alert to display.
   *
   * @var \MovLib\Presentation\Partial\Alert
   */
  public $alert;

  /**
   * The title for the resulting error page.
   *
   * @var string
   */
  public $title;

  /**
   * The HTTP status code.
   *
   * @var int
   */
  public $status;

  /**
   * Instantiate new client exception.
   *
   * @param string $message
   *   The exception message.
   * @param \MovLib\Exception\AbstractException $previous
   *   The previous exception.
   * @param int $code
   *   The exception code.
   * @param string $pageTitle
   *   The already translated title of the resulting error page.
   * @param string $alertTitle
   *   The already translated title of the displayed alert.
   * @param string $alterMessage
   *   The already translated text of the displayed alert.
   * @param int $statusCode
   *   The HTTP status code of the error.
   */
  public function __construct($message, $previous, $code, $pageTitle, $alertTitle, $alertMessage, $statusCode) {
    parent::__construct($message, $previous, $code);
    $this->title = $pageTitle;
    $this->alert = new Alert("<p>{$alertMessage}</p>");
    $this->alert->block = true;
    $this->alert->title = $alertTitle;
    $this->alert->severity = Alert::SEVERITY_ERROR;
    $this->status = $statusCode;
  }
}
