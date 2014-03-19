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
namespace MovLib\Presentation;

use \MovLib\Presentation\Partial\Date;

/**
 * Add various person formatting functions to presentation.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
trait TraitPerson {

  /**
   * Construct life date information for display.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param \MovLib\Data\Person\Person $person
   *   The person to format.
   * @return null|string
   *   The formatted life dates or <code>NULL</code> if none were present.
   */
  public function getLifeDates($person) {
    global $i18n;

    $lifeDates = null;
    if ($person->birthDate || $person->deathDate) {
      if ($person->birthDate) {
        $birthDate = (new Date($person->birthDate))->format([
          "property" => "birthDate",
          "title" => $i18n->t("Date of Birth")
        ]);
      }
      else {
        $birthDate = "<em title='{$i18n->t("Date of Birth")}'>{$i18n->t("unknown")}</em>";
      }

      if ($person->deathDate) {
        $lifeDates = $i18n->t("{0}–{1}", [
          $birthDate,
          (new Date($person->deathDate))->format([ "property" => "deathDate", "title" => $i18n->t("Date of Death") ])
        ]);
      }
      else {
        $lifeDates = $birthDate;
      }
    }

    if ($lifeDates) {
      return $lifeDates;
    }
  }

}
