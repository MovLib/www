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

use \MovLib\Data\Person\Person;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\Date;

/**
 * Special images list for person instances.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Persons extends \MovLib\Presentation\Partial\Lists\AbstractList {


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
  public $imageStyle = Person::STYLE_SPAN_01;

  /**
   * The attributes of the list's items.
   *
   * @var array
   */
  public $listItemsAttributes;

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
   *   The span size the list items should reserve, defaults to <code>10</code>
   * @param boolean $showAdditionalInfo [optional]
   *   Show additional information e.g. life dates or not, defaults to <code>FALSE</code>.
   */
  public function __construct($listItems, $noItemsText = "", array $listItemsAttributes = null, array $attributes = null, $spanSize = 10, $showAdditionalInfo = false) {
    parent::__construct($listItems, $noItemsText, $attributes);
    $this->addClass("hover-list no-list r", $this->attributes);
    $this->listItemsAttributes = $listItemsAttributes;
    $this->addClass("s s{$spanSize}", $this->listItemsAttributes);
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
            $birthDate = (new Date($person->birthDate))->format([ "itemprop" => "birthDate", "title" => $i18n->t("Date of Birth") ]);
          }
          else {
            $birthDate = "<em title='{$i18n->t("Date of Birth")}'>{$i18n->t("unknown")}</em>";
          }

          if ($person->deathDate) {
            $lifeDates = $i18n->t("{0}–{1}", [
              $birthDate,
              (new Date($person->deathDate))->format([ "itemprop" => "deathDate", "title" => $i18n->t("Date of Death") ])
            ]);
          }
          else {
            $lifeDates = $birthDate;
          }

          $lifeDates = "<br>{$lifeDates}";
        }

        if ($additionalNames || $lifeDates) {
          $additionalInfo = "<span class='small'>{$additionalNames}{$lifeDates}</span>";
        }
      }

      // Add the role info if there is any.
      if (isset($person->role)) {
        $additionalInfo .= "<br><span class='small'>{$person->role}</small>";
      }

      $list .=
        "<li{$this->expandTagAttributes($this->listItemsAttributes)}>" .
          "<a class='img li r' href='{$i18n->r("/person/{0}", [ $person->id ])}' itemprop='url'>" .
            $this->getImage($person->getStyle($this->imageStyle), false, [ "class" => "s s1", "itemprop" => "image" ]) .
            "<span class='s s{$this->descriptionSpan}'><span class='link-color' itemprop='name'>{$person->name}</span>{$additionalInfo}</span>" .
          "</a>" .
        "</li>"
      ;
    }
    if (!$list) {
      return (string) new Alert($this->noItemsText, null, Alert::SEVERITY_INFO);
    }
    return "<ol{$this->expandTagAttributes($this->attributes)}>{$list}</ol>";
  }

}
