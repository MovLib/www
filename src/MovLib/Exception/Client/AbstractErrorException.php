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

use \MovLib\Presentation\Page;
use \MovLib\Presentation\Partial\Alert;

/**
 * Parent class for all HTTP client presentation errors.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractErrorException extends \MovLib\Exception\AbstractException {

  /**
   * The error page for this client exception.
   *
   * @var \MovLib\Presentation\Page
   */
  public $presentation;

  /**
   * Instantiate new client exception.
   *
   * @param int $httpResponseCode
   *   The HTTP response code.
   * @param string $pageTitle
   *   The error page's translated title.
   * @param string $alertTitle
   *   The alert's translated title.
   * @param string $alertMessage
   *   The alert's translated message.
   * @param \Exception $previous [optional]
   *   {@inheritdoc}
   * @param int $code [optional]
   *   {@inheritdoc}
   */
  public function __construct($httpResponseCode, $pageTitle, $alertTitle, $alertMessage, $previous = null, $code = E_NOTICE) {
    parent::__construct("Client error '" . get_class($this) . "'", $previous, $code);
    http_response_code($httpResponseCode);
    $this->presentation = new Page($pageTitle);
    $this->presentation->alerts .= new Alert($alertMessage, $alertTitle, Alert::SEVERITY_ERROR);
  }

}
