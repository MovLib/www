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

use \MovLib\Data\Award as DataAward;

/**
 * Special images list for award instances.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Award extends \MovLib\Presentation\Partial\Listing\AbstractListing {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The award photo's style.
   *
   * @var integer
   */
  public $imageStyle = DataAward::STYLE_SPAN_01;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get the rendered listing.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return string
   *   The rendered listing.
   */
  public function __toString() {
    global $i18n;
    // @devStart
    // @codeCoverageIgnoreStart
    try {
    // @codeCoverageIgnoreEnd
    // @devEnd
      $list = null;
      $moviesTitle = $i18n->t("Movies");
      $seriesTitle = $i18n->t("Series");
      /* @var $award \MovLib\Data\Award */
      while ($award = $this->listItems->fetch_object("\\MovLib\\Data\\Award")) {
        // @devStart
        // @codeCoverageIgnoreStart
        if (!($award instanceof \MovLib\Data\Award)) {
          throw new \LogicException($i18n->t("\$award has to be a valid award object!"));
        }
        // @codeCoverageIgnoreEnd
        // @devEnd

        $additionalInfo = null;
        if ($award->firstAwardingYear || $award->lastAwardingYear) {
          $additionalInfo    = "<br><span class='small'>";
          if ($award->firstAwardingYear && $award->lastAwardingYear) {
            $additionalInfo .= $i18n->t("from {0} to {1}", [ $award->firstAwardingYear, $award->lastAwardingYear ]);
          }
          else if ($award->firstAwardingYear) {
            $additionalInfo .= $i18n->t("since {0}", [ $award->firstAwardingYear ]);
          }
          else if ($award->lastAwardingYear) {
            $additionalInfo .= $i18n->t("until {0}", [ $award->lastAwardingYear ]);
          }
          $additionalInfo   .= "</span>";
        }

        $list .=
          "<li class='hover-item r'>" .
            "<div class='s s10'>" .
              $this->getImage($award->getStyle($this->imageStyle), $award->route, [ "property" => "image" ], [ "class" => "fl" ]) .
              "<span class='s s9'>" .
                "<span class='fr'>" .
                  "<a class='ico ico-movie label' href='{$i18n->rp("{$award->routeKey}/movies", [ $award->id ])}' title='{$moviesTitle}'>" .
                    " &nbsp; {$award->getMoviesCount()}" .
                  "</a>" .
                  "<a class='ico ico-series label' href='{$i18n->rp("{$award->routeKey}/series", [ $award->id ])}' title='{$seriesTitle}'>" .
                    " &nbsp; {$award->getSeriesCount()}" .
                  "</a>" .
                "</span>" .
                "<a href='{$award->route}'>{$award->name}</a>{$additionalInfo}" .
              "</span>" .
            "</div>" .
          "</li>"
        ;
      }
      if ($list) {
        return "<ol class='hover-list'>{$list}</ol>";
      }
      return (string) $this->noItemsText;
    // @devStart
    // @codeCoverageIgnoreStart
    }
    catch (\Exception $e) {
      return (string) new \MovLib\Presentation\Partial\Alert(
        "<pre>{$e}</pre>",
        $i18n->t("Error Rendering List"),
        \MovLib\Presentation\Partial\Alert::SEVERITY_ERROR
      );
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
  }

}
