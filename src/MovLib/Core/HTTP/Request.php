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
namespace MovLib\Core\HTTP;

use \MovLib\Component\DateTime;
use \MovLib\Component\URL;
use \MovLib\Presentation\Error\Forbidden;

/**
 * The request is an object-oriented layer to the client's request.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Request {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Request";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The request's cookies.
   *
   * @var array
   */
  public $cookies = [];

  /**
   * The request's date and time.
   *
   * @var \MovLib\Component\DateTime
   */
  public $dateTime;

  /**
   * The request's submitted files.
   *
   * @var array
   */
  public $files = [];

  /**
   * The request's hostname (e.g. <code>"movlib.org"</code>).
   *
   * @var string
   */
  public $hostname;

  /**
   * Whether this request is encrypted or not.
   *
   * @var boolean
   */
  public $https = false;

  /**
   * The request's HTTP method.
   *
   * @var string
   */
  public $method = "GET";

  /**
   * Whether this is a GET request or not.
   *
   * @var boolean
   */
  public $methodGET = true;

  /**
   * Whether this is a POST request or not.
   *
   * @var boolean
   */
  public $methodPOST = false;

  /**
   * The request's system language's ISO 639-1 alpha-2 code.
   *
   * @var string
   */
  public $languageCode = "en";

  /**
   * The request's path.
   *
   * @var string
   */
  public $path = "/";

  /**
   * The request's POST data.
   *
   * @var array
   */
  public $post = [];

  /**
   * The request's protocol/version.
   *
   * @var string
   */
  public $protocol = "HTTP/1.1";

  /**
   * The request's query parameters.
   *
   * @var array
   */
  public $query = [];

  /**
   * The request's query string (without leading <code>"?"</code>).
   *
   * @var string
   */
  public $queryString = "";

  /**
   * The request's remote (IP) address.
   *
   * @var string
   */
  public $remoteAddress = "127.0.0.1";

  /**
   * The request's scheme (e.g. <code>"http"</code>).
   *
   * @var string
   */
  public $scheme = "https";

  /**
   * The request's start timestamp.
   *
   * @var integer
   */
  public $time;

  /**
   * The request's start timestamp with microsecond precision.
   *
   * @var float
   */
  public $timeFloat;

  /**
   * The request's URI (path and query).
   *
   * @var string
   */
  public $uri;

  /**
   * The request's user agent string.
   *
   * @var string
   */
  public $userAgent = false;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new client request object.
   *
   * <b>NOTE</b><br>
   * For some reason we can't use <code>filter_input()</code> for the server variables, therefore we fall back to
   * <code>filter_var()</code>.
   *
   * @param \MovLib\Core\Intl $intl
   *   Active intl instance.
   */
  public function __construct(\MovLib\Core\Intl $intl) {
    $this->cookies       =& $_COOKIE;
    $this->files         =& $_FILES;
    $this->dateTime      =  DateTime::createFromTimestamp($_SERVER["REQUEST_TIME"]);
    $this->hostname      =  $_SERVER["SERVER_NAME"];
    $this->https         =  $_SERVER["HTTPS"] == "on";
    $this->method        =  $_SERVER["REQUEST_METHOD"];
    $this->methodGET     =  $this->method == "GET";
    $this->methodPOST    = !$this->methodGET;
    $this->languageCode  =  $_SERVER["LANGUAGE_CODE"];
    $this->path          =  URL::encodePath($_SERVER["REQUEST_PATH"]);
    $this->post          =& $_POST;
    $this->protocol      =  $_SERVER["SERVER_PROTOCOL"];
    $this->query         =& $_GET;
    $this->queryString   =  $_SERVER["QUERY_STRING"];
    $this->remoteAddress =  filter_var($_SERVER["REMOTE_ADDR"], FILTER_VALIDATE_IP, FILTER_REQUIRE_SCALAR);
    $this->scheme        =  $_SERVER["SCHEME"];
    $this->time          =  $_SERVER["REQUEST_TIME"];
    $this->timeFloat     =  $_SERVER["REQUEST_TIME_FLOAT"];
    $this->uri           =  $_SERVER["REQUEST_URI"];
    $this->userAgent     =  empty($_SERVER["HTTP_USER_AGENT"]) ? false : filter_var($_SERVER["HTTP_USER_AGENT"], FILTER_SANITIZE_STRING, FILTER_REQUIRE_SCALAR | FILTER_FLAG_STRIP_LOW);

    // Careful, it wouldn't make much sense to tell the client to read our privacy policy and at the same time block
    // that page. Therefore we have to make sure that the client without IP address and/or user agent string is at least
    // able to go to that page.
    $privacyPolicyRoute = $intl->r("/privacy-policy");
    if ($privacyPolicyRoute != $this->path && $this->remoteAddress === false || $this->userAgent === false) {
      throw new Forbidden(
        "<p>{$intl->t("IP address or user agent string is invalid or empty.")}</p>" .
        "<p>{$intl->t(
          "Please note that you have to submit your IP address and user agent string to identify yourself as being " .
          "human; should you have privacy concerns read our {privacy_policy}.",
          [ "privacy_policy" => "<a href='{$privacyPolicyRoute}'>{$intl->t("Privacy Policy")}</a>" ]
        )}</p>"
      );
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  // @codeCoverageIgnoreStart
  /**
   * Get variable from input by name.
   *
   * This is a proxy method for {@see filter_input()} that allows us to utilize PHP's built in filter mechanism and
   * unit testing our implementations.
   *
   * @link http://php.net/manual/function.filter-input.php
   * @param integer $type
   *   One of <code>INPUT_GET</code>, <code>INPUT_POST</code>, <code>INPUT_COOKIE</code>, <code>INPUT_SERVER</code>, or
   *   <code>INPUT_ENV</code>.
   * @param string $name
   *   The name of the variable to get.
   * @param integer $filter [optional]
   *   The identifier of the filter to apply
   * @param type $options
   */
  public function filterInput($type, $name, $filter = FILTER_DEFAULT, $options = null) {
    return filter_input($type, $name, $filter, $options);
  }
  // @codeCoverageIgnoreEnd

  // @codeCoverageIgnoreStart
  /**
   * Get variables from input and filter them.
   *
   * This is a proxy method for {@see filter_input_array()} that allows us to utilize PHP's built in filter mechanism
   * and unit testing our implementations.
   *
   * @link http://php.net/manual/function.filter-input-array.php
   * @param integer $type
   *   One of <code>INPUT_GET</code>, <code>INPUT_POST</code>, <code>INPUT_COOKIE</code>, <code>INPUT_SERVER</code>, or
   *   <code>INPUT_ENV</code>.
   * @param array $definition
   *   See original documentation for more information.
   * @param boolean $addEmpty [optional]
   *   Add missing keys as <code>NULL</code> to the return value.
   * @return array|null
   *   Array containing the values of the requested variables on success, <code>FALSE</code> on failure. An array value
   *   will be <code>FALSE</code> if the filter fails, <code>NULL</code> if the variable isn't set. If the flag
   *   <var>FILTER_NULL_ON_FAILURE</var> is used, it returns <code>FALSE</code> if the variable is not set and
   *   <code>NULL</code> if the filter fails.
   */
  public function filterInputArray($type, array $definition, $addEmpty = true) {
    return filter_input_array($type, $definition, $addEmpty);
  }
  // @codeCoverageIgnoreEnd

  // @codeCoverageIgnoreStart
  /**
   * Get variable from input by name and apply the string filter with require scaler plus stripping of low characters.
   *
   * This is a proxy method for {@see filter_input()} that allows us to utilize PHP's built in filter mechanism and
   * unit testing our implementations.
   *
   * @link http://php.net/manual/function.filter-input.php
   * @param integer $type
   *   One of <code>INPUT_GET</code>, <code>INPUT_POST</code>, <code>INPUT_COOKIE</code>, <code>INPUT_SERVER</code>, or
   *   <code>INPUT_ENV</code>.
   * @param string $name
   *   The name of the variable to get.
   * @param integer $filter [optional]
   *   The identifier of the filter to apply
   * @param type $options
   */
  public function filterInputString($type, $name) {
    return filter_input($type, $name, FILTER_SANITIZE_STRING, FILTER_REQUIRE_SCALAR | FILTER_FLAG_STRIP_LOW);
  }
  // @codeCoverageIgnoreEnd

}
