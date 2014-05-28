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
namespace MovLib\Exception\RedirectException;

/**
 * 303 See Other
 *
 * <blockquote>
 * The response to the request can be found under a different URI and SHOULD be retrieved using a GET method on that
 * resource. This method exists primarily to allow the output of a POST-activated script to redirect the user agent to a
 * selected resource. The new URI is not a substitute reference for the originally requested resource. The 303 response
 * MUST NOT be cached, but the response to the second (redirected) request might be cacheable.
 *
 * The different URI SHOULD be given by the Location field in the response. Unless the request method was HEAD, the
 * entity of the response SHOULD contain a short hypertext note with a hyperlink to the new URI(s).
 *
 * <b>NOTE</b><br>
 * Many pre-HTTP/1.1 user agents do not understand the 303 status. When interoperability with such clients is a concern,
 * the 302 status code may be used instead, since most user agents react to a 302 response as described here for 303.
 * </blockquote>
 *
 * @link https://tools.ietf.org/html/rfc2616#section-10.3.4
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class SeeOtherException extends \MovLib\Exception\RedirectException\AbstractRedirectException implements \MovLib\Exception\ClientException\ClientExceptionInterface {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "SeeOtherException";
  // @codingStandardsIgnoreEnd

  /**
   * {@inheritdoc}
   */
  protected function getHttpStatusCode($http1) {
    if ($http1) {
      return self::HTTP_MOVED_PERMANENTLY;
    }
    return self::HTTP_SEE_OTHER;
  }

}
