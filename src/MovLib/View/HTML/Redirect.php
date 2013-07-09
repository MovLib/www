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
namespace MovLib\View\HTML;

/**
 * Perform an HTTP Redirect.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Redirect {

  /**
   * The HTTP status code of this redirect.
   *
   * @var int
   */
  public $status = 301;

  /**
   * RFC 2616 compliant redirect, this will send the FastCGI response but processing will continue in order to ensure
   * that any delayed methods will be executed. You should therefor return from your current method without any further
   * changes to the presentation.
   *
   * @param string $route
   *   The route to which the client should be redirected.
   * @param int $status
   *   [Optional] The HTTP response code (301, 302, or 303), defaults to 301.
   */
  public function __construct($route, $status = 301) {
    $this->status = $status;
    header("Location: {$route}", true, $status);
  }

  /**
   * Get the payload for the redirect.
   *
   * @todo Check if we really have to construct this, or is nginx handling this anyways?
   * @return string
   */
  public function getRenderedView() {
    $title = [
      301 => "Moved Permanently",
      302 => "Moved Temporarily",
      303 => "See Other"
    ];

    return "<html><head><title>{$this->status} {$title[$this->status]}</title></head><body bgcolor=\"white\"><center><h1>{$this->status} {$title[$this->status]}</h1></center><hr><center>nginx/{$_SERVER["SERVER_VERSION"]}</center></body></html>";
  }

}
