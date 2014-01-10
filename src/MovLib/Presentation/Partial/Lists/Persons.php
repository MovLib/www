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
   * The person photo's style.
   *
   * @var integer
   */
  public $imageStyle = PersonImage::STYLE_SPAN_01;


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
   */
  public function __construct($listItems, $noItemsText = "", array $listItemsAttributes = null, array $attributes = null) {
    parent::__construct($listItems, $noItemsText, $listItemsAttributes, $attributes);
    $this->addClass("r", $this->attributes);
    $this->addClass("s s5 r", $this->listItemsAttributes);
    $this->listItemsAttributes[]           = "itemscope";
    $this->listItemsAttributes["itemtype"] = "http://schema.org/Person";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  protected function render() {
    global $i18n;
    if (empty($this->listItems)) {
      return $this->noItemsText;
    }

    $list = null;
    /* @var $person \MovLib\Data\Person\Person */
    while ($person = $this->listItems->fetch_object("\\MovLib\\Data\\Person\\Person")) {
      $list .=
        "<li{$this->expandTagAttributes($this->listItemsAttributes)}>" .
          "<a class='img r' href='{$i18n->r("/person/{0}", [ $person->id ])}' itemprop='url'>" .
            $this->getImage($person->displayPhoto->getStyle($this->imageStyle), false, [ "class" => "s s1", "itemprop" => "image" ]) .
            "<span class='s s4' itemprop='name'>{$person->name}</span>" .
          "</a>" .
        "</li>"
      ;
    }

    return "<ol{$this->expandTagAttributes($this->attributes)}>{$list}</ol>";
  }

}
