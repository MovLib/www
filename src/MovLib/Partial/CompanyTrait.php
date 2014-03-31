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

use \MovLib\Data\Date;

/**
 * Contains utility methods for companies.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
trait CompanyTrait {

  /**
   * Get a company's founding and defunct dates.
   *
   * @param \MovLib\Data\Company\Company $company
   *   The company to get the dates from.
   * @return null|string
   *   The formatted founding and defunct dates or <code>NULL</code> if none were present.
   */
  protected function getCompanyDates(\MovLib\Data\Company\Company $company) {
    if ($company->foundingDate || $company->defunctDate) {
      $founded = " title='{$this->intl->t("Founding Date")}'";

      if ($company->foundingDate) {
        $date = new Date($company->foundingDate);
        $years = "<time datetime='{$date->year}' property='foundingDate'{$founded}>{$date->year}</time>";
      }
      else {
        $years = "<em{$founded}>{$this->intl->t("unknown")}</em>";
      }

      if ($company->defunctDate) {
        $date = new Date($company->defunctDate);
        $years = $this->intl->t(
          "{0}–{1}",
          [ $years, "<time datetime='{$date->year}' property='defunctDate' title='{$this->intl->t("Defunct Date")}'>{$date->year}</time>" ]
        );
      }

      return $years;
    }
  }

}
