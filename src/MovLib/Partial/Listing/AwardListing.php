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

use \MovLib\Data\Award;
use \MovLib\Presentation\Partial\Alert;

/**
 * Images list for award instances.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class AwardListing extends \MovLib\Presentation\AbstractBase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The list items to display.
   *
   * @var mixed
   */
  protected $listItems;

  /**
   * The text to display if there are no items.
   *
   * @var mixed
   */
  protected $noItemsText;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new award listing.
   *
   * @param mixed $listItems
   *   The items to build the award listing.
   * @param mixed $noItemsText [optional]
   *   The text to display if there are no items, defaults to a generic {@see \MovLib\Presentation\Partial\Alert}.
   */
  public function __construct($listItems, $noItemsText = null) {
    $this->listItems   = $listItems;
    $this->noItemsText = $noItemsText;
  }


  /**
   * Get the string representation of the listing.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return string
   *   The string representation of the listing.
   */
  public function __toString() {
    global $i18n;

    // @devStart
    // @codeCoverageIgnoreStart
    try {
    // @codeCoverageIgnoreEnd
    // @devEnd
      $list = null;
      /* @var $award \MovLib\Data\Award*/
      while ($award = $this->listItems->fetch_object("\\MovLib\\Data\\Award")) {
        // @devStart
        // @codeCoverageIgnoreStart
        if (!($award instanceof \MovLib\Data\Award)) {
          throw new \LogicException($i18n->t("\$award has to be a valid award object!"));
        }
        // @codeCoverageIgnoreEnd
        // @devEnd
        $list .= $this->formatListItem($award);
      }

      if ($list) {
        return "<ol class='hover-list no-list'>{$list}</ol>";
      }

      if (!$this->noItemsText) {
        $this->noItemsText = new Alert(
          $i18n->t(
            "We couldn’t find any award matching your filter criteria, or there simply isn’t any award available." .
            "Would you like to {0}create a new entry{1}?",
            [ "<a href='{$i18n->r("/award/create")}'>", "</a>" ]
          ),
          $i18n->t("No Companies"),
          Alert::SEVERITY_INFO
        );
      }
      return (string) $this->noItemsText;

    // @devStart
    // @codeCoverageIgnoreStart
    } catch (\Exception $e) {
      return (string) new Alert("<pre>{$e}</pre>", "Error Rendering Award List", Alert::SEVERITY_ERROR);
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods

  /**
   * Format a award list item.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param \MovLib\Data\Company\FullCompany $award
   *   The award to format.
   * @param mixed $listItem [optional]
   *   The current list item if different from $award.
   * @return string
   *   The formatted award list item.
   */
  final protected function formatListItem($award, $listItem = null) {
    global $i18n;

    // Put award dates together.
    $awardDates = null;
    if ($award->firstAwardingYear || $award->lastAwardingYear) {
      $awardDates    = "<br><span class='small'>";
      if ($award->firstAwardingYear && $award->lastAwardingYear) {
        $awardDates .= $i18n->t("from {0} to {1}", [ $award->firstAwardingYear, $award->lastAwardingYear ]);
      }
      else if ($award->firstAwardingYear) {
        $awardDates .= $i18n->t("since {0}", [ $award->firstAwardingYear ]);
      }
      else if ($award->lastAwardingYear) {
        $awardDates .= $i18n->t("until {0}", [ $award->lastAwardingYear ]);
      }
      $awardDates   .= "</span>";
    }

    // Put the award list entry together.
    return
      "<li class='hover-item r' typeof='Corporation'>" .
        "<div class='s s10'>" .
          $this->getImage(
            $award->getStyle(Award::STYLE_SPAN_01),
            $award->route,
            [ "property" => "image" ],
            [ "class" => "fl" ]
          ) .
          "<span class='s s9'>" .
            $this->getAdditionalContent($award, $listItem) .
            "<a href='{$award->route}' property='url'>" .
              "<span property='name'>{$award->name}</span>" .
            "</a>{$awardDates}" .
          "</span>" .
        "</div>" .
      "</li>"
    ;
  }

  /**
   * Get additional content to display on a award list item.
   *
   * @param \MovLib\Data\Company\FullCompany $award
   *   The award providing the information.
   * @return string
   *   The formatted additional content.
   */
  protected function getAdditionalContent($award, $listItem) {
    // The default implementation returns no additional content.
  }

}
