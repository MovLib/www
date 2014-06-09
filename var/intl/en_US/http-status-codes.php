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

/**
 * System message translations.
 *
 * <b>NOTE</b><br>
 * We manage these translations via PHP array because we don't want to stress the database with endless string look-ups
 * and lock during all those operations. It also allows us to keep the {@see \MovLib\Core\Intl} database free which is
 * important if we encounter any database related problems and still want to translate our presentations (even if we're
 * only talking about error pages at this point).
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
// @codeCoverageIgnoreStart
return [

  // These are translations for the user interface and not meant for HTTP headers!
  //
  // The list of codes is complete according to the Hypertext Transfer Protocol (HTTP) Status Code Registry (last
  // updated 2012-02-13). Unless otherwise noted, the status code is defined in RFC7231.
  //
  // @link http://www.iana.org/assignments/http-status-codes/
  //
  // HTTP Staus Codes >>>
  // {{{ Informational
  100 => "Continue",
  101 => "Switching Protocols",
  102 => "Processing", // RFC2518
  // }}}
  // {{{ Successful
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
  // }}}
  // {{{ Redirection
  300 => "Multiple Choices",
  301 => "Moved Permanently",
  302 => "Found",
  303 => "See Other",
  304 => "Not Modified",
  305 => "Use Proxy",
  306 => "Reserved",
  307 => "Temporary Redirect",
  308 => "Permanent Redirect", // RFC-reschke-http-status-308-07
  // }}}
  // {{{ Client Error
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
  // }}}
  // {{{ Server Error
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
  // }}}
  // HTTP Staus Codes <<<

];
// @codeCoverageIgnoreEnd
