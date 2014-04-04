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
namespace MovLib\Partial;

/**
 * Defines the sex object.
 *
 * @link https://en.wikipedia.org/wiki/ISO/IEC_5218
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Sex {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Not Known
   *
   * @var integer
   */
  const UNKNOWN = 0;

  /**
   * Male
   *
   * @var integer
   */
  const MALE = 1;

  /**
   * Female
   *
   * @var integer
   */
  const FEMALE = 2;

  /**
   * Not Applicable
   *
   * @var integer
   */
  const NOT_APPLICABLE = 9;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The sex's unique identifier.
   *
   * @var integer
   */
  public $id;

  /**
   * The sex's name in the current locale.
   *
   * @var string
   */
  public $name;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new sex object.
   *
   * @param \MovLib\Core\Intl $intl
   *   The active intl instance.
   * @param integer $sex
   *   The sex constant to instantiate.
   * @throws \ErrorException
   *   If the given <var>$sex</var> is not a valid unique sex identifier.
   */
  public function __construct(\MovLib\Core\Intl $intl, $sex) {
    $this->id   = $sex;
    $this->name = $intl->getTranslations("sex")[$sex];
  }

  /**
   * Get the string representation of the sex.
   *
   * @return string
   *   The string representation of the sex.
   */
  public function __toString() {
    return $this->name;
  }

}
