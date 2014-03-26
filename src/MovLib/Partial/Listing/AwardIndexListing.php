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
 * Images list for award instances with series and movie counts.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class AwardIndexListing extends \MovLib\Partial\Listing\AwardListing {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Translated movie route of first item.
   *
   * @var string
   */
  private $firstMovieRoute;

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
    $this->moviesTitle = $i18n->t("Movies");
    $this->seriesTitle = $i18n->t("Series");
    parent::__construct($listItems, $noItemsText);
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   */
  protected function getAdditionalContent($award, $listItem) {
    global $i18n;

    // @devStart
    // @codeCoverageIgnoreStart
    if(!($award instanceof \MovLib\Data\Award)) {
      throw new \InvalidArgumentException("\$award must be of type \\MovLib\\Data\\Award");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    if (!isset($this->firstMovieRoute)) {
      $this->firstMovieRoute = $i18n->rp("{$award->routeKey}/movies");
    }
    if (!isset($this->firstSeriesRoute)) {
      $this->firstSeriesRoute = $i18n->rp("{$award->routeKey}/series");
    }

    $currentMovieRoute  = str_replace("{0}", $award->id, $this->firstMovieRoute);
    $currentSeriesRoute = str_replace("{0}", $award->id, $this->firstSeriesRoute);

    return
      "<span class='fr'>" .
        "<a class='ico ico-movie label' href='{$currentMovieRoute}' title='{$this->moviesTitle}'>" .
          " &nbsp; {$award->getMoviesCount()}" .
        "</a>" .
        "<a class='ico ico-series label' href='{$currentSeriesRoute}' title='{$this->seriesTitle}'>" .
          " &nbsp; {$award->getSeriesCount()}" .
        "</a>" .
      "</span>"
    ;
  }

}
