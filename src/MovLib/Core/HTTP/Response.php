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

use \MovLib\Exception\ClientException;
use \MovLib\Presentation\Stacktrace;

/**
 * Represents the response that will be sent to the client.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Response {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Used to collect client alert messages.
   *
   * @see Response::setAlert()
   * @var string
   */
  protected $alerts;

  /**
   * Whether this request is cacheable or not.
   *
   * @var boolean
   */
  public $cacheable = false;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new HTTP request object.
   */
  public function __construct() {
    $this->cacheable = $_SERVER["REQUEST_METHOD"] == "GET";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Create cookie.
   *
   * @global \MovLib\Core\Config $config
   * @global \MovLib\Core\HTTP\Request $request
   * @param string $identifier
   *   The cookie's global unique identifier.
   * @param mixed $value
   *   The cookie's value.
   * @param integer $expire [optional]
   *   The cookie's time to life, defaults to <code>0</code> which means that the cookie will be deleted when the client
   *   closes it's user agent.
   * @param boolean $httpOnly [optional]
   *   Whether the cookie should be available via HTTP only (not accessible for JavaScript) or not. Defaults to
   *   <code>FALSE</code> and the cookie is available to anyone.
   * @return this
   */
  public function createCookie($identifier, $value, $expire = 0, $httpOnly = false) {
    global $config, $request;
    setcookie($identifier, $value, $expire, "/", $config->hostname, $request->https, $httpOnly);
    return $this;
  }

  /**
   * Delete cookie(s).
   *
   * @param array|string $identifiers
   *   The cookie's global unique identifier(s) to delete.
   * @return this
   */
  public function deleteCookie($identifiers) {
    foreach ((array) $identifiers as $id) {
      $this->createCookie($id, "", 1);
    }
    return $this;
  }

  /**
   * Get the response.
   *
   * @global \MovLib\Presentation\AbstractPresenter $presenter
   * @param string $siteName
   *   The site's name.
   * @return string
   *   The response.
   */
  public function respond($siteName) {
    global $presenter;

    try {
      $className = "\\MovLib\\Presentation\\{$_SERVER["PRESENTER"]}";
      $presenter = new $className($siteName);
      $content   = $presenter->getContent();
    }
    catch (ClientException $e) {
      return $e->getPresentation();
    }
    catch (\Exception $e) {
      $presenter = new Stacktrace($siteName, $e);
      $content   = $presenter->getContent();
    }

    // Allow every stage to alter the final presentation.
    $header = $presenter->getHeader();
    $main   = $presenter->getMainContent($content);
    $footer = $presenter->getFooter();

    // Finally try to send the presentation.
    return $presenter->getPresentation($header, $main, $footer);
  }

  /**
   * Set alert.
   *
   * @param string $message
   *   The alert's localized message.
   * @param string $title
   *   The alert's localized title.
   * @param string $severity
   *   The alert's severity.
   * @return this
   */
  protected function setAlert($message, $title, $severity) {
    if ($title) {
      $title = "<h4 class='title'>{$title}</h4>";
    }
    $this->alerts .= "<div class='alert{$severity}' role='alert'><div class='c'>{$title}{$message}</div></div>";
    return $this;
  }

  /**
   * Set error alert (red color).
   *
   * @param string $message
   *   The alert's localized message.
   * @param string $title [optional]
   *   The alert's localized title, defaults to <code>NULL</code> which is suitable for inline alerts that have many
   *   surrounding content. If the alert message is going to be displayed with no or little surrounding content include
   *   a title (in proper title case).
   * @return this
   */
  public function setAlertError($message, $title = null) {
    return $this->setAlert($message, $title, "error");
  }

  /**
   * Set info alert (blue color).
   *
   * @param string $message
   *   The alert's localized message.
   * @param string $title [optional]
   *   The alert's localized title, defaults to <code>NULL</code> which is suitable for inline alerts that have many
   *   surrounding content. If the alert message is going to be displayed with no or little surrounding content include
   *   a title (in proper title case).
   * @return this
   */
  public function setAlertInfo($message, $title = null) {
    return $this->setAlert($message, $title, "info");
  }

  /**
   * Set success alert (green color).
   *
   * @param string $message
   *   The alert's localized message.
   * @param string $title [optional]
   *   The alert's localized title, defaults to <code>NULL</code> which is suitable for inline alerts that have many
   *   surrounding content. If the alert message is going to be displayed with no or little surrounding content include
   *   a title (in proper title case).
   * @return this
   */
  public function setAlertSuccess($message, $title = null) {
    return $this->setAlert($message, $title, "success");
  }

  /**
   * Set warning alert (yellow color).
   *
   * @param string $message
   *   The alert's localized message.
   * @param string $title [optional]
   *   The alert's localized title, defaults to <code>NULL</code> which is suitable for inline alerts that have many
   *   surrounding content. If the alert message is going to be displayed with no or little surrounding content include
   *   a title (in proper title case).
   * @return this
   */
  public function setAlertWarning($message, $title = null) {
    return $this->setAlert($message, $title, "warning");
  }

}
