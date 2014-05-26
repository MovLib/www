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
  public function getPresentation(\MovLib\Core\HTTP\Container $container) {
    $container->response->cacheable = false;
    $route = rawurldecode($this->message);
    $code  = $this->getHttpStatusCode($container->request->protocol == "HTTP/1.0");
    $title = $container->intl->translate($code, null, "http-status-codes", null);

    if (strpos($route, "//") === false) {
      $route = "{$container->request->scheme}://{$container->request->hostname}{$route}";
    }
    else {
      if (strpos($route, $container->config->hostname) === false) {
        throw new \RuntimeException("Invalid redirect to external host '{$route}'");
      }
      if (strpos($route, "http") === false && strpos($route, "https") === false) {
        $route = "{$container->request->scheme}{$route}";
      }
    }

    // Send any alert messages as a cookie to the client, this allows us to display them on the next page view.
    if (!empty($container->presenter->alerts)) {
      $container->response->createCookie("alerts", $container->presenter->alerts);
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
