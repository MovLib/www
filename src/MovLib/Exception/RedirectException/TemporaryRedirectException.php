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
 * 307 Temporary Redirect
 *
 * <blockquote>
 * The requested resource resides temporarily under a different URI. Since the redirection MAY be altered on occasion,
 * the client SHOULD continue to use the Request-URI for future requests. This response is only cacheable if indicated
 * by a Cache-Control or Expires header field.
 *
 * The temporary URI SHOULD be given by the Location field in the response. Unless the request method was HEAD, the
 * entity of the response SHOULD contain a short hypertext note with a hyperlink to the new URI(s) , since many
 * pre-HTTP/1.1 user agents do not understand the 307 status. Therefore, the note SHOULD contain the information
 * necessary for a user to repeat the original request on the new URI.
 *
 * If the 307 status code is received in response to a request other than GET or HEAD, the user agent MUST NOT
 * automatically redirect the request unless it can be confirmed by the user, since this might change the conditions
 * under which the request was issued.
 *
 * <b>NOTE</b><br>
 * Many pre-HTTP/1.1 user agents do not understand the 307 status. When interoperability with such clients is a concern,
 * the 302 status code may be used instead.
 * </blockquote>
 *
 * @link https://tools.ietf.org/html/rfc2616#section-10.3.3
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class TemporaryRedirectException extends \MovLib\Exception\RedirectException\AbstractRedirectException implements \MovLib\Exception\ClientException\ClientExceptionInterface {

  /**
   * {@inheritdoc}
   */
  protected function getHttpStatusCode($http1) {
    if ($http1) {
      return self::HTTP_FOUND;
    }
    return self::HTTP_TEMPORARY_REDIRECT;
  }

}
