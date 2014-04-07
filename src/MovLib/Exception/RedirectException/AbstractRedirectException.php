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
 * Defines the default implementation for redirect exceptions.
 *
 * Status code constants and the translation table are borrowed from {@see \Symfony\Component\HttpFoundation\Response}
 * class.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractRedirectException extends \RuntimeException implements \MovLib\Exception\ClientException\ClientExceptionInterface {

  /**
   * {@inheritdoc}
   */
  public function getPresentation(\MovLib\Core\HTTP\DIContainerHTTP $diContainerHTTP) {
    $diContainerHTTP->response->cacheable = false;
    $route = rawurldecode($this->message);
    $code  = $this->getHttpStatusCode($diContainerHTTP->request->protocol == "HTTP/1.0");
    $title = $diContainerHTTP->intl->translate($code, null, "http-status-codes", null);

    if (strpos($route, "//") === false) {
      $route = "{$diContainerHTTP->request->scheme}://{$diContainerHTTP->request->hostname}{$route}";
    }
    else {
      if (strpos($route, $diContainerHTTP->config->hostname) === false) {
        throw new \RuntimeException("Invalid redirect to external host '{$route}'");
      }
      if (strpos($route, "http") === false && strpos($route, "https") === false) {
        $route = "{$diContainerHTTP->request->scheme}{$route}";
      }
    }

    // The body is a direct copy of what nginx would serve the client, note that payload is required per RFC.
    header("Location: {$route}", true, $code);
    return <<<HTML
<!doctype html>
<html>
<head><title>{$code} {$title}</title></head>
<body style='text-align:center'><h1>{$code} {$title}</h1><hr>{$_SERVER["SERVER_SOFTWARE"]}</body>
</html>
HTML
    ;
  }

  /**
   * Get the redirect"s HTTP status code.
   *
   * @param boolean $http1
   *   Whether the request's protocol is HTTP/1.0 (<code>TRUE</code>) or newer (<code>FALSE</code>).
   * @return integer
   *   The redirect"s HTTP status code.
   */
  abstract protected function getHttpStatusCode($http1);

}
