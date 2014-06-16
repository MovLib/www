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
namespace MovLib\Stub\Core\Diff;

/**
 * Defines the transformation stub for diffs.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © Error: on line 15, column 19 in Templates/Scripting/PHPClass.php
  The string doesn't match the expected date/time format. The string to parse was: "11.06.2014". The expected format was: "MMM d, yyyy". MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class Transformation {

  /**
   * The transformation's code.
   *
   * One of the \MovLib\Core\Diff\Diff::*_KEY constants.
   *
   * @var integer
   */
  public $code;

  /**
   * The transformation's length.
   *
   * @var integer
   */
  public $length;

  /**
   * The transformation's text.
   *
   * @var string
   */
  public $text;
}
