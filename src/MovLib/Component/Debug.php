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
namespace MovLib\Component;

use \FirePHP;
use \Krumo;

/**
 * Defines the debug class.
 *
 * The debug class has various handy static methods to print variables and disect stuff. This class mainly provides a
 * single interface to execute code from libraries installed via composer.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class Debug {

  /**
   * Log to browser console.
   *
   * @param mixed $variable
   *   The variable part of the log entry.
   * @param string $label
   *   The label of the log entry.
   * @param string $level
   *   The severity level of the log entry.
   * @param array $options [optional]
   *   Additional options:
   *   <ul>
   *     <li><code>"Collapsed"</code> either <code>TRUE</code> or <code>FALSE</code></li>
   *     <li><code>"Color"</code> either a HEX value or a color name</li>
   *   </ul>
   */
  final protected static function console($variable, $label, $level, array $options = null) {
    FirePHP::getInstance(true)->fb($variable, $label, $level, $options);
  }

  /**
   * Dump variable to browser console.
   *
   * @param string $label
   *   The label of the log entry.
   * @param mixed $variable
   *   The variable part of the log entry.
   * @param string $level
   *   The severity level of the log entry.
   * @param array $options [optional]
   *   Additional options:
   *   <ul>
   *     <li><code>"Collapsed"</code> either <code>TRUE</code> or <code>FALSE</code></li>
   *     <li><code>"Color"</code> either a HEX value or a color name</li>
   *   </ul>
   */
  final public static function consoleDump($label, $variable, array $options = null) {
    self::console($variable, $label, FirePHP::DUMP, $options);
  }

  /**
   * Log error message to browser console.
   *
   * @param string $message
   *   The message to log.
   * @param string $label
   *   The message's label.
   */
  final public static function consoleError($message, $label = null) {
    self::console($message, $label, FirePHP::ERROR);
  }

  /**
   * Log exception to browser console.
   *
   * @param \Exception $exception
   *   The exception to log.
   */
  final public static function consoleException(\Exception $exception) {
    self::console($exception, null, FirePHP::EXCEPTION);
  }

  /**
   * Start a group in the browser console.
   *
   * @param string $title
   *   The group's title.
   * @param boolean $collapsed [optional]
   *   Whether the group should be collapsed or not, defaults to <code>TRUE</code> (group is collapsed).
   */
  final public static function consoleGroupStart($title, $collapsed = true) {
    self::console(null, $title, FirePHP::GROUP_START, [ "Collapsed" => $collapsed ]);
  }

  /**
   * End previously opened group.
   */
  final public static function consoleGroupEnd() {
    self::console(null, null, FirePHP::GROUP_END);
  }

  /**
   * Log info message to browser console.
   *
   * @param string $message
   *   The message to log.
   * @param string $label
   *   The message's label.
   */
  final public static function consoleInfo($message, $label = null) {
    self::console($message, $label, FirePHP::INFO);
  }

  /**
   * Log message to browser console.
   *
   * @param string $message
   *   The message to log.
   * @param string $label
   *   The message's label.
   */
  final public static function consoleLog($message, $label = null) {
    self::console($message, $label, FirePHP::LOG);
  }

  /**
   * Log info message to browser console.
   *
   * @param string $label
   *   The message's label.
   * @param string $table
   *   The table to log.
   */
  final public static function consoleTable($label, array $table) {
    self::console($table, $label, FirePHP::TABLE);
  }

  /**
   * Log warning message to browser console.
   *
   * @param string $message
   *   The message to log.
   * @param string $label
   *   The message's label.
   */
  final public static function consoleWarning($message, $label = null) {
    self::console($message, $label, FirePHP::WARN);
  }

  /**
   * Dump variable.
   *
   * @param mixed $variable
   *   The variable to dump.
   * @param boolean $expanded [optional]
   *   Whether to expand all nodes in the output, defaults to <code>FALSE</code> (nodes aren't expanded). Only has an
   *   effect if <var>$exit</var> is set to <code>TRUE</code> (default).
   * @param boolean $exit [optional]
   *   Whether to exit after dump, defaults to <code>TRUE</code>.
   * @return string
   *   Returns the dump if <var>$exit</var> isn't set to <code>TRUE</code> (default).
   */
  final public static function dump($variable, $expanded = false, $exit = true) {
    if ($exit === true) {
      if ($expanded === true) {
        Krumo::dump($variable, KRUMO_EXPAND_ALL);
      }
      else {
        Krumo::dump($variable);
      }
      exit();
    }
    return Krumo::dump($variable, KRUMO_RETURN);
  }

}
