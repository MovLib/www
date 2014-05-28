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

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "InputSex";
  // @codingStandardsIgnoreEnd

  /**
   * {@inheritdoc}
   */
  public function __construct(\MovLib\Core\HTTP\Container $container, $id, $label, &$value, $help = null, $helpPopup = false) {
    $options = $container->intl->getTranslations("sex");
    unset($options[9]); // We remove the "not applicable" option from this input element.
    parent::__construct($container, $id, $label, $options, $value);
    if ($helpPopup) {
      $this->attributes["#help-popup"] = $help;
    }
  }

}
