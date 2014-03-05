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
namespace MovLib\Presentation\Partial\Lists;

/**
 * Special list for genre instances.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Genres extends \MovLib\Presentation\Partial\Lists\AbstractList {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The span size for a single genres's description.
   *
   * @var integer
   */
  protected $descriptionSpan;

  /**
   * Show additional information or not.
   *
   * @var boolean
   */
  protected $showAdditionalInfo;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new special genres listing.
   *
   * @param \mysqli_result $listItems
   *   The mysqli result object containing the genre.
   * @param string $noItemsText
   *   {@inheritdoc}
   * @param array $listItemsAttributes
   *   {@inheritdoc}
   * @param array $attributes
   *   {@inheritdoc}
   * @param integer $spanSize [optional]
   *   The span size the list items should reserve, defaults to <code>5</code>
   * @param boolean $showAdditionalInfo [optional]
   *   Show additional information or not, defaults to <code>FALSE</code>.
   */
  public function __construct($listItems, $noItemsText = "", array $listItemsAttributes = null, array $attributes = null, $spanSize = 5, $showAdditionalInfo = false) {
    parent::__construct($listItems, $noItemsText, $attributes);
    $this->addClass("hover-list no-list cf", $this->attributes);
    $this->listItemsAttributes  = $listItemsAttributes;
    $this->addClass("li r s no-padding", $this->listItemsAttributes);
    $this->attributes["typeof"] = "ItemList";
    $this->showAdditionalInfo   = $showAdditionalInfo;
    $this->descriptionSpan      = ($this->showAdditionalInfo === true) ? ($spanSize - 3) : $spanSize;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  protected function render() {
    global $i18n;
    $list = null;
    try {
      /* @var $genre \MovLib\Data\Genre */
      while ($genre = $this->listItems->fetch_object("\\MovLib\\Data\\Genre")) {
        $additionalInfo = null;
        if ($this->showAdditionalInfo === true) {
          $additionalInfo =
            "<div class='s s3 tar'>" .
              "<a class='label ico ico-movie' href='{$i18n->rp("/genre/{0}/movies", [ $genre->id ])}' title='{$i18n->t("Movies with this genre.")}'>" .
                " &nbsp; {$genre->getMovieCount()}" .
              "</a>" .
              "<a class='label ico ico-series' href='{$i18n->rp("/genre/{0}/series", [ $genre->id ])}' title='{$i18n->t("Series with this genre.")}'>" .
                " &nbsp; {$genre->getSeriesCount()}" .
              "</a>" .
            "</div>"
          ;
        }

        $list .=
          "<li{$this->expandTagAttributes($this->listItemsAttributes)}>" .
            "<a class='img s s{$this->descriptionSpan}' href='{$i18n->r("/genre/{0}", [ $genre->id ])}'>" .
              "<span class='link-color' property='itemListElement'>{$genre->name}</span>" .
            "</a>{$additionalInfo}" .
          "</li>";
      }
      if (!$list) {
        return $this->noItemsText;
      }
      return "<ol{$this->expandTagAttributes($this->attributes)}>{$list}</ol>";
    }
    catch (\Exception $e) {
      return $e->getMessage();
    }
  }

}
