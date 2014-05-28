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
 * 410 Gone
 *
 * The requested resource is no longer available at the server and no forwarding address is known. This condition is
 * expected to be considered permanent. Clients with link editing capabilities SHOULD delete references to the Request-
 * URI after user approval. If the server does not know, or has no facility to determine, whether or not the condition
 * is permanent, the status code 404 (Not Found) SHOULD be used instead. This response is cacheable unless indicated
 * otherwise.
 *
 * The 410 response is primarily intended to assist the task of web maintenance by notifying the recipient that the
 * resource is intentionally unavailable and that the server owners desire that remote links to that resource be
 * removed. Such an event is common for limited-time, promotional services and for resources belonging to individuals no
 * longer working at the server's site. It is not necessary to mark all permanently unavailable resources as "gone" or
 * to keep the mark for any length of time -- that is left to the discretion of the server owner.
 *
 * @link https://tools.ietf.org/html/rfc2616#section-10.4.11
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class GoneException extends \RuntimeException implements \MovLib\Exception\ClientException\ClientExceptionInterface {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "GoneException";
  // @codingStandardsIgnoreEnd

  /**
   * {@inheritdoc}
   */
  public function getPresentation(\MovLib\Core\HTTP\Container $container) {

  }

}
