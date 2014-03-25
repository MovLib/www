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
 * Exception thrown if the requested resource is no longer available and no forwarding address is known.
 *
 * @link https://tools.ietf.org/html/rfc2616#section-10.4.11
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class GoneException extends \RuntimeException implements ClientException {

  /**
   * {@inheritdoc}
   * @global \MovLib\Presentation\Page $page
   */
  public function getPresentation() {
    global $presenter;
    http_response_code(410);
    $content = $presenter->getGoneContent();
    $header  = $presenter->getHeader();
    $main    = $presenter->getMainContent($content);
    $footer  = $presenter->getFooter();
    return $presenter->getPresentation($header, $main, $footer);
  }

}
