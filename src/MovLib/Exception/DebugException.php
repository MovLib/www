<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link http://movlib.org/ MovLib}.
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
 * A debug exception can be thrown to dissect variables.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class DebugException extends \MovLib\Exception\AbstractException {

  /**
   * The variable to dissect.
   *
   * @var mixed
   */
  public $mixed;

  /**
   * Instantiate new debug exception.
   *
   * @param mixed $mixed
   *   The variable that should be dissected.
   * @param string|array $method [optional]
   *   The method with which <var>$mixed</var> should be dissected. Possible pre-defined values are
   *   <code>"var_dump"</code> (default) and <code>"print_r"</code>. You can pass any name of a callable in here which
   *   will be called with <code>call_user_func()</code> or <code>call_user_func_array()</code> (depending on the type
   *   of <var>$mixed</var>).
   */
  public function __construct($mixed, $method = "var_dump") {
    parent::__construct("Debug Exception Output", null, E_NOTICE);
    if ($method == "var_dump") {
      ob_start();
      var_dump($mixed);
      $this->mixed = ob_get_clean();
    }
    elseif ($method == "print_r") {
      $this->mixed = print_r($mixed, true);
    }
    elseif (is_array($mixed) || is_object($mixed)) {
      $this->mixed = call_user_func_array($method, $mixed);
    }
    else {
      $this->mixed = call_user_func($method, $mixed);
    }
  }

  /**
   * The debug exception does not have a trace per sé, instead the dissected variable as string is used.
   *
   * @return string
   */
  public function __toString() {
    return htmlspecialchars($this->mixed, ENT_QUOTES|ENT_HTML5);
  }

}
