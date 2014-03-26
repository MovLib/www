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
namespace MovLib\Partial\Listing;

use \MovLib\Data\Company\Company;
use \MovLib\Data\Person\Person;
use \MovLib\Data\AwardCategory;

/**
 * List for movie instances that have won or were nominatet for an award.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class AwardEventMovieListing extends \MovLib\Partial\Listing\AwardMovieListing {


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   */
  protected function getAdditionalContent($movie, $listItem) {
    global $i18n;

    // @devStart
    // @codeCoverageIgnoreStart
    if (empty($movie->awardCategoryIds)) {
      throw new \LogicException($i18n->t("\$movie->awardCategoryIds can not be empty!"));
    }
    if (empty($movie->awardCategoryWon)) {
      throw new \LogicException($i18n->t("\$movie->awardCategoryWon can not be empty!"));
    }
    if (empty($movie->awardedCompanyIds)) {
      throw new \LogicException($i18n->t("\$movie->awardedCompanyIds can not be empty!"));
    }
    if (empty($movie->awardedPersonIds)) {
      throw new \LogicException($i18n->t("\$movie->awardedPersonIds can not be empty!"));
    }
    if (count($movie->awardCategoryIds) != count($movie->awardCategoryWon)) {
      throw new \LogicException($i18n->t("The count of \$movie->awardCategoryIds and \$movie->awardCategoryWon has to be equal!"));
    }
    if (count($movie->awardCategoryIds) != count($movie->awardedCompanyIds)) {
      throw new \LogicException($i18n->t("The count of \$movie->awardCategoryIds and \$movie->awardedCompanyIds has to be equal!"));
    }
    if (count($movie->awardCategoryIds) != count($movie->awardedPersonIds)) {
      throw new \LogicException($i18n->t("The count of \$movie->awardCategoryIds and \$movie->awardedPersonIds has to be equal!"));
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    $awards       = null;
    $hasWon       = $i18n->t("has won");
    $wasNominated = $i18n->t("was nominated in");
    $c            = count($movie->awardCategoryIds);
    for ($i = 0; $i < $c; ++$i) {
      $who = $how = $what = null;
      if (isset($movie->awardedCompanyIds[$i]) && $movie->awardedPersonIds[$i]) {
        $company = new Company($movie->awardedCompanyIds[$i]);
        $person  = new Person($movie->awardedPersonIds[$i]);
        $who     = "<a href='{$person->route}'>{$person->name}</a> (<a href='{$company->route}'>{$company->name}</a>) ";
      }
      else if (isset($movie->awardedCompanyIds[$i])) {
        $company = new Company($movie->awardedCompanyIds[$i]);
        $who     = "<a href='{$company->route}'>{$company->name}</a> ";
      }
      else if ($movie->awardedPersonIds[$i]) {
        $person = new Person($movie->awardedPersonIds[$i]);
        $who    = "<a href='{$person->route}'>{$person->name}</a> ";
      }

      if ($movie->awardCategoryWon[$i]) {
        $how = $hasWon;
      }
      else {
        $how = $wasNominated;
      }

      if (isset($movie->awardCategoryIds[$i])) {
        $category = new AwardCategory($movie->awardCategoryIds[$i]);
        $what     = "<a href='{$category->route}'>{$category->name}</a>";
      }

      $awards .="<li class='small'>{$who} {$how} {$what}</li>";
    }

    return "<ul>{$awards}</ul>";
  }

}
