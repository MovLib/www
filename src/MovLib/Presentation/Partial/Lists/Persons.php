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

use \MovLib\Data\Image\PersonImage;
use \MovLib\Presentation\Partial\Date;

/**
 * Special images list for person instances.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Persons extends \MovLib\Presentation\Partial\Lists\Images {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The span size for a single person's description.
   *
   * @var integer
   */
  protected $descriptionSpan;

  /**
   * The person photo's style.
   *
   * @var integer
   */
  public $imageStyle = PersonImage::STYLE_SPAN_01;

  /**
   * Show additional information or not.
   *
   * @var boolean
   */
  protected $showAdditionalInfo;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new special persons listing.
   *
   * @param \mysqli_result $listItems
   *   The mysqli result object containing the persons.
   * @param string $noItemsText
   *   {@inheritdoc}
   * @param array $listItemsAttributes
   *   {@inheritdoc}
   * @param array $attributes
   *   {@inheritdoc}
   * @param integer $spanSize [optional]
   *   The span size the list items should reserve, defaults to <code>5</code>
   * @param boolean $showAdditionalInfo [optional]
   *   Show additional information e.g. life dates or not, defaults to <code>FALSE</code>.
   */
  public function __construct($listItems, $noItemsText = "", array $listItemsAttributes = null, array $attributes = null, $spanSize = 5, $showAdditionalInfo = false) {
    parent::__construct($listItems, $noItemsText, $listItemsAttributes, $attributes);
    $this->addClass("r", $this->attributes);
    $this->addClass("s r", $this->listItemsAttributes);
    $this->addClass("s{$spanSize}", $this->listItemsAttributes);
    $this->descriptionSpan                 = --$spanSize;
    $this->listItemsAttributes[]           = "itemscope";
    $this->listItemsAttributes["itemtype"] = "http://schema.org/Person";
    $this->showAdditionalInfo              = $showAdditionalInfo;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  protected function render() {
    global $i18n;
    $list = null;
    try {
    /* @var $person \MovLib\Data\Person\Person */
    while ($person = $this->listItems->fetch_object("\\MovLib\\Data\\Person\\Person")) {
      $additionalInfo = null;
      if ($this->showAdditionalInfo === true) {
        $additionalNames = null;
        if ($person->bornName) {
          $additionalNames .= $i18n->t("{0} ({1})", [
            "<span itemprop='additionalName'>{$person->bornName}</span>",
            "<i>{$i18n->t("born name")}</i>",
          ]);
        }
        if ($person->nickname) {
          if ($additionalNames) {
            $additionalNames .= " ";
          }
          $additionalNames .= $i18n->t("aka “{0}”", [ "<span itemprop='additionalName'>{$person->nickname}</span>" ]);
        }
        if ($additionalNames) {
          $additionalNames = "<br>{$additionalNames}";
        }

        $lifeDates = null;
        if ($person->birthDate || $person->deathDate) {
          if ($person->birthDate) {
            $lifeDates .= (new Date($person->birthDate))->format([ "itemprop" => "birthDate", "title" => $i18n->t("Date of Birth") ]);
          }
          else {
            $lifeDates .= $i18n->t("{0}unknown{1}", [ "<em title='{$i18n->t("Date of Birth")}'>", "</em>" ]);
          }

          if ($person->deathDate) {
            $lifeDates .= " – " . (new Date($person->deathDate))->format([ "itemprop" => "deathDate", "title" => $i18n->t("Date of Death") ]);
          }

          $lifeDates = "<br>{$lifeDates}";
        }

        if ($additionalNames || $lifeDates) {
          $additionalInfo = "<span class='small'>{$additionalNames}{$lifeDates}</span>";
        }
      }

      $list .=
        "<li{$this->expandTagAttributes($this->listItemsAttributes)}>" .
          "<a class='img li r' href='{$i18n->r("/person/{0}", [ $person->id ])}' itemprop='url'>" .
            $this->getImage($person->displayPhoto->getStyle($this->imageStyle), false, [ "class" => "s s1", "itemprop" => "image" ]) .
            "<span class='s s{$this->descriptionSpan}'><span class='link-color' itemprop='name'>{$person->name}</span>{$additionalInfo}</span>" .
          "</a>" .
        "</li>"
      ;
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
