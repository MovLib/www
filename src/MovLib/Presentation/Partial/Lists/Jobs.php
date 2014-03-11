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
 * Special list for job instances.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Jobs extends \MovLib\Presentation\Partial\Lists\AbstractList {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The span size for a single job's description.
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
   * Instantiate new special job listing.
   *
   * @param \mysqli_result $listItems
   *   The mysqli result object containing the job.
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
    $this->addClass("hover-list no-list r", $this->attributes);
    $this->listItemsAttributes = $listItemsAttributes;
    $this->addClass("s s{$spanSize}", $this->listItemsAttributes);
    $this->listItemsAttributes[]           = "itemscope";
    $this->listItemsAttributes["itemtype"] = "http://schema.org/Person";
    $this->showAdditionalInfo              = $showAdditionalInfo;
    $this->descriptionSpan                 = ($this->showAdditionalInfo === true) ? ($spanSize - 2) : $spanSize;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  protected function render() {
    global $i18n;
    $list = null;
    try {
      /* @var $job \MovLib\Data\Job */
      while ($job = $this->listItems->fetch_object("\\MovLib\\Data\\Job")) {
        $additionalInfo = null;
        if ($this->showAdditionalInfo === true) {
          $additionalInfo =
            "<ul class='no-list s2 hover-item fr'>" .
              "<li>" .
                "<a href='{$i18n->r("/job/{0}/movies", [ $job->id ])}'>" .
                  "<span class='small'>{$i18n->t("{0} Movies", [ $job->getMovieCount() ])}</span>" .
                "</a>" .
              "</li>" .
              "<li>" .
                "<a href='{$i18n->r("/job/{0}/series", [ $job->id ])}'>" .
                  "<span class='small'>{$i18n->t("{0} Series", [ $job->getSeriesCount() ])}</span>" .
                "</a>" .
              "</li>" .
            "</ul>";
        }

        $list .=
          "<li{$this->expandTagAttributes($this->listItemsAttributes)}>" .
            "<div class='r hover-list no-padding'>" .
              "<a class='big-item-text' href='{$i18n->r("/job/{0}", [ $job->id ])}' itemprop='url'>" .
                "<div class='s s{$this->descriptionSpan}'>" .
                  "<span class='link-color' itemprop='jobTitle'>{$job->title}</span>" .
                "</div>" .
              "</a>{$additionalInfo}" .
            "</div>" .
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
