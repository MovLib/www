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
abstract class AbstractErrorException extends \MovLib\Exception\Client\AbstractClientException {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The error page's alert message explaining the error.
   *
   * @internal
   *   Keep this public and allow instantiating classes to override the alert's properties.
   * @var \MovLib\Presentation\Partial\Alert
   */
  public $alert;

  /**
   * The HTTP response code.
   *
   * @var integer
   */
  protected $responseCode;

  /**
   * The presentation's title.
   *
   * @var string
   */
  protected $title;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


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
   */
  public function __construct($httpResponseCode, $pageTitle, $alertTitle, $alertMessage) {
    parent::__construct("Client error '" . get_class($this) . "'");
    $this->alert        = new Alert($alertMessage, $alertTitle, Alert::SEVERITY_ERROR);
    $this->responseCode = $httpResponseCode;
    $this->title        = $pageTitle;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  public function getPresentation() {
    http_response_code($this->responseCode);
    $page          = new Page($this->title);
    $page->alerts .= $this->alert;
    return $page->getPresentation();
  }

}
