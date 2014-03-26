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
namespace MovLib\Partial\FormElement;

/**
 * Specialized input element for choosing sex according to {@link https://en.wikipedia.org/wiki/ISO/IEC_5218 ISO IEC
 * 5218}.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class InputSex extends \MovLib\Partial\FormElement\RadioGroup {

  /**
   * 0
   *
   * Unknown sex according to the standard.
   */
  const UNKNOWN = 0;

  /**
   * 1
   *
   * Male sex according to the standard.
   */
  const MALE = 1;

  /**
   * 2
   *
   * Female sex according to the standard.
   */
  const FEMALE = 2;

  /**
   * Instantiate new input sex element.
   *
   * @param string $id
   *   {@inheritdoc}
   * @param string $label
   *   {@inheritdoc}
   * @param mixed $value
   *   {@inheritdoc}
   * @param null|string $help
   *   The input's help text (if any).
   * @param null|string $helpPopup
   *   The input's help popup (if any).
   *
   */
  public function __construct($id, $label, &$value, $help = null, $helpPopup = false) {
    parent::__construct($id, $label, [
      self::FEMALE  => $this->intl->t("Female"),
      self::MALE    => $this->intl->t("Male"),
      self::UNKNOWN => $this->intl->t("Unknown"),
    ], $value);
    if ($helpPopup) {
      $this->attributes["#help-popup"] = $help;
    }
  }

}
