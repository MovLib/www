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
namespace MovLib\Data\StreamWrapper;

use \MovLib\Data\Log;

/**
 * Factory to create, register, and unregister MovLib stream wrappers.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class StreamWrapperFactory {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Array containing all stream wrappers available to the factory.
   *
   * @var array
   */
  protected static $wrapper = [];


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Create new stream wrapper for given URI.
   *
   * @param string $uri
   *   Absolute URI for which a new stream wrapper should be created.
   * @return \MovLib\Data\StreamWrapper\AbstractLocalStreamWrapper
   *   The stream wrapper responsible for URIs kind of streams.
   * @throws \ErrorException
   */
  public static function create($uri) {
    /* @var $instance \MovLib\Data\StreamWrapper\AbstractLocalStreamWrapper */
    $instance = new static::$wrapper[parse_url($uri, PHP_URL_SCHEME)]();
    $instance->uri = $uri;
    return $instance;
  }

  /**
   * Register new stream wrapper.
   *
   * @param string|array $schemes
   *   The scheme(s) the stream wrapper(s) provides.
   * @throws \LogicException
   */
  public static function register($schemes) {
    $schemes = (array) $schemes;
    $c       = count($schemes);
    for ($i = 0; $i < $c; ++$i) {
      $class = "\\MovLib\\Data\\StreamWrapper\\" . ucfirst($schemes[$i]) . "StreamWrapper";
      // @devStart
      // @codeCoverageIgnoreStart
      if (class_exists($class) === false) {
        throw new \LogicException("Couldn't find stream wrapper '{$class}'");
      }
      // @codeCoverageIgnoreEnd
      // @devEnd
      if (stream_wrapper_register($schemes[$i], $class) === false) {
        Log::debug(
          "Couldn't register {$class} for as stream wrapper for scheme {$schemes[$i]} because there's already another " .
          "stream wrapper registered for this scheme."
        );
      }
      static::$wrapper[$schemes[$i]] = $class;
    }
  }

  /**
   * Unregister scheme wrapper.
   *
   * @param string|array $schemes
   *   The registered stream wrapper(s)'s scheme(s).
   * @throws \UnexpectedValueException
   */
  public static function unregister($schemes) {
    $schemes = (array) $schemes;
    $c       = count($schemes);
    for ($i = 0; $i < $c; ++$i) {
      if (stream_wrapper_unregister($schemes[$i]) === false) {
        throw new \UnexpectedValueException("Couldn't unregister stream wrapper for scheme {$schemes[$i]}.");
      }
      unset(static::$wrapper[$schemes[$i]]);
    }
  }

}
