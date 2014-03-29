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
namespace MovLib\Partial\Person;

use \MovLib\Partial\Date;

/**
 * Add various person formatting functions to presentation.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
trait PersonTrait {

  /**
   * Get a person's biographical dates.
   *
   * @param \MovLib\Presentation\AbstractPresenter $presenter
   *   The presenting presenter.
   * @param \MovLib\Core\Intl $intl
   *   The active intl instance.
   * @param \MovLib\Data\Person\Person $person
   *   The person to get the biographical dates from.
   * @return null|string
   *   The formatted biographical dates or <code>NULL</code> if none were present.
   */
  final protected function getPersonBioDates(\MovLib\Presentation\AbstractPresenter $presenter, \MovLib\Core\Intl $intl, \MovLib\Data\Person\Person $person) {
    if ($person->birthDate || $person->deathDate) {
      $dates = null;

      if ($person->birthDate) {
        $dates .= (new Date($presenter, $person->birthDate))->format($intl, [
          "property" => "birthDate",
          "title" => $intl->t("Date of Birth"),
        ]);
      }
      else {
        $dates .= "<em title='{$intl->t("Date of Birth")}'>{$intl->t("unknown")}</em>";
      }

      if ($person->deathDate) {
        $dates = $intl->t("{0}–{1}", [ $dates, (new Date($presenter, $person->deathDate))->format($intl, [
          "property" => "deathDate",
          "title" => $intl->t("Date of Death") ]),
        ]);
      }

      return $dates;
    }
  }

}
