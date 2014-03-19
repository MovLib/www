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


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Translated movie route of first item.
   *
   * @var string
   */
  private $firstMovieRoute;

  /**
   * Translated release route of first item.
   *
   * @var string
   */
  private $firstReleaseRoute;

  /**
   * Translated series route of first item.
   *
   * @var string
   */
  private $firstSeriesRoute;

  /**
   * Translated movies title.
   *
   * @var string
   */
  private $moviesTitle;

  /**
   * Translated releases title.
   *
   * @var string
   */
  private $releasesTitle;

  /**
   * Translated series title.
   *
   * @var string
   */
  private $seriesTitle;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   */
  public function __construct($listItems, $noItemsText = null) {
    global $i18n;
    $this->moviesTitle   = $i18n->t("Movies");
    $this->releasesTitle = $i18n->t("Releases");
    $this->seriesTitle   = $i18n->t("Series");
    parent::__construct($listItems, $noItemsText);
  }


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

    if (!isset($this->firstMovieRoute)) {
      $this->firstMovieRoute = $i18n->rp("{$company->routeKey}/movies");
    }
    if (!isset($this->firstReleaseRoute)) {
      $this->firstReleaseRoute = $i18n->rp("{$company->routeKey}/releases");
    }
    if (!isset($this->firstSeriesRoute)) {
      $this->firstSeriesRoute = $i18n->rp("{$company->routeKey}/series");
    }

    $currentMovieRoute   = str_replace("{0}", $company->id, $this->firstMovieRoute);
    $currentReleaseRoute = str_replace("{0}", $company->id, $this->firstReleaseRoute);
    $currentSeriesRoute  = str_replace("{0}", $company->id, $this->firstSeriesRoute);

    return
      "<span class='fr'>" .
        "<a class='ico ico-movie label' href='{$currentMovieRoute}' title='{$i18n->t("Movies")}'>" .
          " &nbsp; {$company->getMoviesCount()}" .
        "</a>" .
        "<a class='ico ico-series label' href='{$currentSeriesRoute}' title='{$i18n->t("Series")}'>" .
          " &nbsp; {$company->getSeriesCount()}" .
        "</a>" .
        "<a class='ico ico-release label' href='{$currentReleaseRoute}' title='{$i18n->t("Releases")}'>" .
          " &nbsp; {$company->getSeriesCount()}" .
        "</a>" .
      "</span>"
    ;
  }

}
