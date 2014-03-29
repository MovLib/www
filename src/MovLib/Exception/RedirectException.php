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
abstract class RedirectException extends \RuntimeException implements \MovLib\Exception\ClientExceptionInterface {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  const HTTP_CONTINUE = 100;
  const HTTP_SWITCHING_PROTOCOLS = 101;
  const HTTP_PROCESSING = 102; // RFC2518
  const HTTP_OK = 200;
  const HTTP_CREATED = 201;
  const HTTP_ACCEPTED = 202;
  const HTTP_NON_AUTHORITATIVE_INFORMATION = 203;
  const HTTP_NO_CONTENT = 204;
  const HTTP_RESET_CONTENT = 205;
  const HTTP_PARTIAL_CONTENT = 206;
  const HTTP_MULTI_STATUS = 207; // RFC4918
  const HTTP_ALREADY_REPORTED = 208; // RFC5842
  const HTTP_IM_USED = 226; // RFC3229
  const HTTP_MULTIPLE_CHOICES = 300;
  const HTTP_MOVED_PERMANENTLY = 301;
  const HTTP_FOUND = 302;
  const HTTP_SEE_OTHER = 303;
  const HTTP_NOT_MODIFIED = 304;
  const HTTP_USE_PROXY = 305;
  const HTTP_RESERVED = 306;
  const HTTP_TEMPORARY_REDIRECT = 307;
  const HTTP_PERMANENTLY_REDIRECT = 308; // RFC-reschke-http-status-308-07
  const HTTP_BAD_REQUEST = 400;
  const HTTP_UNAUTHORIZED = 401;
  const HTTP_PAYMENT_REQUIRED = 402;
  const HTTP_FORBIDDEN = 403;
  const HTTP_NOT_FOUND = 404;
  const HTTP_METHOD_NOT_ALLOWED = 405;
  const HTTP_NOT_ACCEPTABLE = 406;
  const HTTP_PROXY_AUTHENTICATION_REQUIRED = 407;
  const HTTP_REQUEST_TIMEOUT = 408;
  const HTTP_CONFLICT = 409;
  const HTTP_GONE = 410;
  const HTTP_LENGTH_REQUIRED = 411;
  const HTTP_PRECONDITION_FAILED = 412;
  const HTTP_REQUEST_ENTITY_TOO_LARGE = 413;
  const HTTP_REQUEST_URI_TOO_LONG = 414;
  const HTTP_UNSUPPORTED_MEDIA_TYPE = 415;
  const HTTP_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
  const HTTP_EXPECTATION_FAILED = 417;
  const HTTP_I_AM_A_TEAPOT = 418; // RFC2324
  const HTTP_UNPROCESSABLE_ENTITY = 422; // RFC4918
  const HTTP_LOCKED = 423; // RFC4918
  const HTTP_FAILED_DEPENDENCY = 424; // RFC4918
  const HTTP_RESERVED_FOR_WEBDAV_ADVANCED_COLLECTIONS_EXPIRED_PROPOSAL = 425; // RFC2817
  const HTTP_UPGRADE_REQUIRED = 426; // RFC2817
  const HTTP_PRECONDITION_REQUIRED = 428; // RFC6585
  const HTTP_TOO_MANY_REQUESTS = 429; // RFC6585
  const HTTP_REQUEST_HEADER_FIELDS_TOO_LARGE = 431; // RFC6585
  const HTTP_INTERNAL_SERVER_ERROR = 500;
  const HTTP_NOT_IMPLEMENTED = 501;
  const HTTP_BAD_GATEWAY = 502;
  const HTTP_SERVICE_UNAVAILABLE = 503;
  const HTTP_GATEWAY_TIMEOUT = 504;
  const HTTP_VERSION_NOT_SUPPORTED = 505;
  const HTTP_VARIANT_ALSO_NEGOTIATES_EXPERIMENTAL = 506; // RFC2295
  const HTTP_INSUFFICIENT_STORAGE = 507; // RFC4918
  const HTTP_LOOP_DETECTED = 508; // RFC5842
  const HTTP_NOT_EXTENDED = 510; // RFC2774
  const HTTP_NETWORK_AUTHENTICATION_REQUIRED = 511; // RFC6585


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * HTTP status code constants to English explanation mapping.
   *
   * The list of codes is complete according to the
   * {@link http://www.iana.org/assignments/http-status-codes/ Hypertext Transfer Protocol (HTTP) Status Code Registry}
   * (last updated 2012-02-13).
   *
   * Unless otherwise noted, the status code is defined in RFC2616.
   *
   * @var array
   */
  public static $statusTexts = [
    100 => "Continue",
    101 => "Switching Protocols",
    102 => "Processing", // RFC2518
    200 => "OK",
    201 => "Created",
    202 => "Accepted",
    203 => "Non-Authoritative Information",
    204 => "No Content",
    205 => "Reset Content",
    206 => "Partial Content",
    207 => "Multi-Status", // RFC4918
    208 => "Already Reported", // RFC5842
    226 => "IM Used", // RFC3229
    300 => "Multiple Choices",
    301 => "Moved Permanently",
    302 => "Found",
    303 => "See Other",
    304 => "Not Modified",
    305 => "Use Proxy",
    306 => "Reserved",
    307 => "Temporary Redirect",
    308 => "Permanent Redirect", // RFC-reschke-http-status-308-07
    400 => "Bad Request",
    401 => "Unauthorized",
    402 => "Payment Required",
    403 => "Forbidden",
    404 => "Not Found",
    405 => "Method Not Allowed",
    406 => "Not Acceptable",
    407 => "Proxy Authentication Required",
    408 => "Request Timeout",
    409 => "Conflict",
    410 => "Gone",
    411 => "Length Required",
    412 => "Precondition Failed",
    413 => "Request Entity Too Large",
    414 => "Request-URI Too Long",
    415 => "Unsupported Media Type",
    416 => "Requested Range Not Satisfiable",
    417 => "Expectation Failed",
    418 => "I’m a teapot", // RFC2324
    422 => "Unprocessable Entity", // RFC4918
    423 => "Locked", // RFC4918
    424 => "Failed Dependency", // RFC4918
    425 => "Reserved for WebDAV advanced collections expired proposal", // RFC2817
    426 => "Upgrade Required", // RFC2817
    428 => "Precondition Required", // RFC6585
    429 => "Too Many Requests", // RFC6585
    431 => "Request Header Fields Too Large", // RFC6585
    500 => "Internal Server Error",
    501 => "Not Implemented",
    502 => "Bad Gateway",
    503 => "Service Unavailable",
    504 => "Gateway Timeout",
    505 => "HTTP Version Not Supported",
    506 => "Variant Also Negotiates (Experimental)", // RFC2295
    507 => "Insufficient Storage", // RFC4918
    508 => "Loop Detected", // RFC5842
    510 => "Not Extended", // RFC2774
    511 => "Network Authentication Required", // RFC6585
  ];


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  public function getPresentation(\MovLib\Core\HTTP\DIContainerHTTP $diContainerHTTP) {
    $diContainerHTTP->response->cacheable = false;
    $route = $this->message;
    $code  = $this->getHttpStatusCode($diContainerHTTP->request->protocol == "HTTP/1.0");
    $title = $diContainerHTTP->intl->t(static::$statusTexts[$code]);

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
