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
 * List to display award categories.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class AwardCategory extends \MovLib\Presentation\Partial\Listing\AbstractListing {


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new listing.
   *
   * @param \mysqli_result $result
   *   The MySQLi result object containing the entities.
   * @param string $noItemsText
   *   {@inheritdoc}
   */
  public function __construct($result, $noItemsText) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (class_exists("\\MovLib\\Data\\AwardCategory") === false) {
      throw new \InvalidArgumentException("\\MovLib\\Data\\AwardCategory must match an existing class");
    }
    if (method_exists("\\MovLib\\Data\\AwardCategory", "getMoviesCount") === false) {
      throw new \InvalidArgumentException("\\MovLib\\Data\\AwardCategory must implement method 'getMoviesCount()'.");
    }
    if (method_exists("\\MovLib\\Data\\AwardCategory", "getSeriesCount") === false) {
      throw new \InvalidArgumentException("\\MovLib\\Data\\AwardCategory must implement method 'getSeriesCount()'.");
    }
    if (property_exists("\\MovLib\\Data\\AwardCategory", "routeKey") === false) {
      throw new \InvalidArgumentException("\\MovLib\\Data\\AwardCategory must have property 'routeKey'.");
    }
    if (property_exists("\\MovLib\\Data\\AwardCategory", "awardId") === false) {
      throw new \InvalidArgumentException("\\MovLib\\Data\\AwardCategory must have property 'awardId'.");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    parent::__construct($result, $noItemsText);
  }


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
      while ($awardCategory = $this->listItems->fetch_object("\\MovLib\\Data\\AwardCategory")) {
        $list .=
          "<li class='hover-item r'>" .
            "<div class='s s10'>" .
              "<span class='fr'>" .
                "<a class='ico ico-movie label' href='{$i18n->rp($awardCategory->routeKey . "/movies", [ $awardCategory->awardId, $awardCategory->id ])}' title='{$moviesTitle}'> &nbsp; {$awardCategory->getMoviesCount()}</a>" .
                "<a class='ico ico-series label' href='{$i18n->rp($awardCategory->routeKey . "/series", [ $awardCategory->awardId, $awardCategory->id ])}' title='{$seriesTitle}'> &nbsp; {$awardCategory->getSeriesCount()}</a>" .
              "</span>" .
              "<a href='{$awardCategory->route}'>{$awardCategory->name}</a>" .
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
