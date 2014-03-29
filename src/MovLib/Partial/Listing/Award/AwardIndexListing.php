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
namespace MovLib\Partial\Listing\Award;

/**
 * Images list for award instances with series and movie counts.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class AwardIndexListing extends \MovLib\Partial\Listing\AbstractMySQLiResultListing {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Title attribute content for explanation of the movie icon.
   *
   * @var string
   */
  protected $moviesTitle;

  /**
   * Title attribute content for explanation of the series icon.
   *
   * @var string
   */
  protected $seriesTitle;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * {@inheritdoc}
   */
  public function __construct(\MovLib\Core\HTTP\DIContainerHTTP $diContainerHTTP, \MovLib\Data\SetInterface $set, $oderedBy) {
    parent::__construct($diContainerHTTP, $set, $oderedBy);
    $this->moviesTitle = $diContainerHTTP->intl->t("Movies");
    $this->seriesTitle = $diContainerHTTP->intl->t("Series");
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   * @param \MovLib\Data\Award $item {@inheritdoc}
   */
  public function formatItem($award, $delta = null) {
    // @todo Move to "\MovLib\Partial\Award\AwardTrait" if needed elsewhere!
    $years = null;
    if ($award->firstAwardingYear || $award->lastAwardingYear) {
      if ($award->firstAwardingYear && $award->lastAwardingYear) {
        $years = $this->diContainerHTTP->intl->t(
          "from {year_from} to {year_to}",
          [ "year1" => $award->firstAwardingYear, "year2" => $award->lastAwardingYear ]
        );
      }
      elseif ($award->firstAwardingYear) {
        $years = $this->diContainerHTTP->intl->t("since {year}", [ "year" => $award->firstAwardingYear ]);
      }
      else {
        $years = $this->diContainerHTTP->intl->t("until {year}", [ "year" => $award->lastAwardingYear ]);
      }
      $years = "<br><span class='small'>{$years}</span>";
    }

    return
      "<li class='hover-item r' typeof='Corporation'>" .
        "<div class='s s10'>" .
//          $this->getImage(
//            $award->getStyle(Award::STYLE_SPAN_01),
//            $award->route,
//            [ "property" => "image" ],
//            [ "class" => "fl" ]
//          ) .
          "<img alt='' src='{$this->diContainerHTTP->presenter->getExternalURL("asset://img/logo/vector.svg")}' width='60' height='60' property='image' class='fl'>" .
          "<span class='s s9'>" .
            "<span class='fr'>" .
              "<a class='ico ico-movie label' href='{$this->diContainerHTTP->intl->rp(
                "/award/{0}/movies",
                $award->id
              )}' title='{$this->moviesTitle}'> &nbsp; {$award->getCount("movie", "movies", true)}</a>" .
              "<a class='ico ico-series label' href='{$this->diContainerHTTP->intl->rp(
                "/award/{0}/series",
                $award->id
              )}' title='{$this->seriesTitle}'> &nbsp; 0</a>" .
            "</span>" .
            "<a href='{$award->route}' property='url'><span property='name'>{$award->name}</span></a>" .
            $years .
          "</span>" .
        "</div>" .
      "</li>"
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function getListing($items) {
    return "<ol class='hover-list no-list'>{$items}</ol>";
  }

}
