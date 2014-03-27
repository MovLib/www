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

/**
 * Images list for company instances with release, series and movie counts.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class CompanyIndexListing extends \MovLib\Partial\Listing\CompanyListing {


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
   */
  public function __construct($listItems, $noItemsText = null) {
    $this->moviesTitle   = $this->intl->t("Movies");
    $this->releasesTitle = $this->intl->t("Releases");
    $this->seriesTitle   = $this->intl->t("Series");
    parent::__construct($listItems, $noItemsText);
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  protected function getAdditionalContent($company, $listItem) {
    // @devStart
    // @codeCoverageIgnoreStart
    if(!($company instanceof \MovLib\Data\Company\Company)) {
      throw new \InvalidArgumentException("\$company must be of type \\MovLib\\Data\\Company\\Company");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    if (!isset($this->firstMovieRoute)) {
      $this->firstMovieRoute = $this->intl->rp("{$company->routeKey}/movies");
    }
    if (!isset($this->firstReleaseRoute)) {
      $this->firstReleaseRoute = $this->intl->rp("{$company->routeKey}/releases");
    }
    if (!isset($this->firstSeriesRoute)) {
      $this->firstSeriesRoute = $this->intl->rp("{$company->routeKey}/series");
    }

    $currentMovieRoute   = str_replace("{0}", $company->id, $this->firstMovieRoute);
    $currentReleaseRoute = str_replace("{0}", $company->id, $this->firstReleaseRoute);
    $currentSeriesRoute  = str_replace("{0}", $company->id, $this->firstSeriesRoute);

    return
      "<span class='fr'>" .
        "<a class='ico ico-movie label' href='{$currentMovieRoute}' title='{$this->intl->t("Movies")}'>" .
          " &nbsp; {$company->getMoviesCount()}" .
        "</a>" .
        "<a class='ico ico-series label' href='{$currentSeriesRoute}' title='{$this->intl->t("Series")}'>" .
          " &nbsp; {$company->getSeriesCount()}" .
        "</a>" .
        "<a class='ico ico-release label' href='{$currentReleaseRoute}' title='{$this->intl->t("Releases")}'>" .
          " &nbsp; {$company->getSeriesCount()}" .
        "</a>" .
      "</span>"
    ;
  }

}
