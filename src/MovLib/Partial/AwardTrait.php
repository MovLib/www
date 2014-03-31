<?php

/* !
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
 * @todo Description of AwardTrait
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
trait AwardTrait {

  /**
   * Get the award's first and last awarding years.
   *
   * @param \MovLib\Data\Award\Award $award
   *   The award to get the years from.
   * @return string
   *   The formatted first and last awarding years or <code>NULL</code> if none were present.
   */
  protected function getAwardEventYears(\MovLib\Data\Award\Award $award) {
    if ($award->firstEventYear || $award->lastEventYear) {
      $first = " title='{$this->intl->t("First award ceremony")}'";

      if ($award->firstEventYear) {
        $years = "<time datetime='{$award->firstEventYear}'{$first}>{$award->firstEventYear}</time>";
      }
      else {
        $years = "<em{$first}>{$this->intl->t("unknown")}</em>";
      }

      if ($award->lastEventYear) {
        return $this->intl->t(
          "{0}–{1}",
          [ $years, "<time datetime='{$award->lastEventYear}' title='{$this->intl->t("Last award ceremony")}'>{$award->lastEventYear}</time>" ]
        );
      }

      return $years;
    }
  }

}
