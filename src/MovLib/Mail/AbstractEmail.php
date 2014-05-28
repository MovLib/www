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
namespace MovLib\Mail;

/**
 * Base email implementation.
 *
 * All email templates have to extend this class in order to work with the mailer system.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractEmail {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "AbstractEmail";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Email priority high.
   *
   * @var int
   */
  const PRIORITY_HIGH = 1;

  /**
   * Email priority normal.
   *
   * @var int
   */
  const PRIORITY_NORMAL = 3;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The active config instance.
   *
   * @var \MovLib\Core\Config
   */
  protected $config;

  /**
   * The dependency injection container.
   *
   * @var \MovLib\Core\Container
   */
  protected $container;

  /**
   * The active file system instance.
   *
   * @var \MovLib\Core\FileSystem
   */
  protected $fs;

  /**
   * The active intl instance.
   *
   * @var \MovLib\Core\Intl
   */
  protected $intl;

  /**
   * The active kernel instance.
   *
   * @var \MovLib\Core\Kernel
   */
  protected $kernel;

  /**
   * The active log instance.
   *
   * @var \MovLib\Core\Log
   */
  protected $log;

  /**
   * The active presenter instance.
   *
   * @var \MovLib\Core\Presentation\DependencyInjectionBase
   */
  protected $presenter;

  /**
   * The email's priority.
   *
   * @var int
   */
  public $priority = self::PRIORITY_NORMAL;

  /**
   * The email's recipient.
   *
   * @var string
   */
  public $recipient;

  /**
   * The active request instance.
   *
   * @var \MovLib\Core\HTTP\Request
   */
  protected $request;

  /**
   * The active response instance.
   *
   * @var \MovLib\Core\HTTP\Response
   */
  protected $response;

  /**
   * The active session instance.
   *
   * @var \MovLib\Core\HTTP\Session
   */
  protected $session;

  /**
   * The email's subject.
   *
   * @var string
   */
  public $subject;


  // ------------------------------------------------------------------------------------------------------------------- Abstract Methods


  /**
   * Get the email's translated HTML message.
   *
   * @return string
   *   The email's translated HTML message.
   */
  abstract public function getHTML();

  /**
   * Get the email's translated plain text message.
   *
   * @return string
   *   The email's translated plain text message.
   */
  abstract public function getPlainText();


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get the absolute URL.
   *
   * @param string $route
   *   The route part of the URL.
   * @param array $query [optional]
   *   Associative array containing the data for the query string, keys in the array will be keys and values the values
   *    in the query string.
   * @return string
   *   The absolute URL.
   */
  final protected function url($route, array $query = null) {
    if ($query) {
      array_walk($query, function (&$value, $key) {
        $value = rawurlencode($this->intl->r($key)) . "=" . rawurlencode($value);
      });
      $query = "?" . implode("&amp;", $query);
    }
    return "{$this->request->scheme}://{$this->request->hostname}{$this->fs->urlEncodePath($route)}{$query}";
  }

  /**
   * Initialize email.
   *
   * @param \MovLib\Core\HTTP\Container $container
   *   The HTTP dependency injection container.
   * @return boolean
   *   <code>TRUE</code> if everything is fine and the email should be sent, <code>FALSE</code> if sending of the email
   *   should be aborted.
   */
  public function init(\MovLib\Core\HTTP\Container $container) {
    $this->container = $container;
    foreach (get_object_vars($container) as $property => $value) {
      if (property_exists($this, $property)) {
        $this->$property = $value;
      }
    }
    return true;
  }

}
