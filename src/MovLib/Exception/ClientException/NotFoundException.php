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
namespace MovLib\Exception\ClientException;

/**
 * 404 Not Found
 *
 * The server has not found anything matching the Request-URI. No indication is given of whether the condition is
 * temporary or permanent. The 410 (Gone) status code SHOULD be used if the server knows, through some internally
 * configurable mechanism, that an old resource is permanently unavailable and has no forwarding address. This status
 * code is commonly used when the server does not wish to reveal exactly why the request has been refused, or when no
 * other response is applicable.
 *
 * @link https://tools.ietf.org/html/rfc2616#section-10.4.5
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class NotFoundException extends \RuntimeException implements \MovLib\Exception\ClientException\ClientExceptionInterface {

  /**
   * {@inheritdoc}
   */
  public function getPresentation(\MovLib\Core\HTTP\DIContainerHTTP $diContainerHTTP) {

  }

}
