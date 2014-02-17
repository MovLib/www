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
namespace MovLib\Presentation\Partial\FormElement;

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
final class InputSex extends \MovLib\Presentation\Partial\FormElement\RadioGroup {

  public function __construct($id, $label, &$value, $help = null, $helpPopup = false) {
    global $i18n;
    parent::__construct($id, $label, [
      2 => $i18n->t("Female"),
      1 => $i18n->t("Male"),
      0 => $i18n->t("Unknown"),
    ], $value, $help, $helpPopup);
  }

}
