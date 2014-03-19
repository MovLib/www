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
namespace MovLib\Presentation\Partial\Listing;

/**
 * Images list for company instances with release, series and movie counts.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class CompanyIndexListing extends \MovLib\Presentation\Partial\Listing\CompanyListing {


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   */
  protected function getAdditionalContent($company, $listItem) {
    global $i18n;
    // @devStart
    // @codeCoverageIgnoreStart
    if(!($company instanceof \MovLib\Data\Company\Company)) {
      throw new \InvalidArgumentException("\$company must be of type \\MovLib\\Data\\Company\\Company");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    return
      "<span class='fr'>" .
        "<a class='ico ico-movie label' href='{$i18n->rp(
          "{$company->routeKey}/movies",
          [ $company->id ]
        )}' title='{$i18n->t("Movies")}'>" .
          " &nbsp; {$company->getMoviesCount()}" .
        "</a>" .
        "<a class='ico ico-series label' href='{$i18n->rp(
          "{$company->routeKey}/series",
          [ $company->id ]
        )}' title='{$i18n->t("Series")}'>" .
          " &nbsp; {$company->getSeriesCount()}" .
        "</a>" .
        "<a class='ico ico-release label' href='{$i18n->rp(
          "{$company->routeKey}/releases",
          [ $company->id ]
        )}' title='{$i18n->t("Releases")}'>" .
          " &nbsp; {$company->getSeriesCount()}" .
        "</a>" .
      "</span>"
    ;
  }

}
