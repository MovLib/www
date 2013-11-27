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

use \MovLib\Data\Image\AbstractBaseImage;

/**
 * Create an image list.
 *
 * <h2>Usage</h2>
 * <ul>
 *   <li>The item's parameter must be an instance of <code>\MovLib\Data\AbstractImages</code></li>
 *   <li>The closure must return the list item and will be called with the following arguments:
 *     <ol>
 *       <li><code>$entity</code> the current image the loop is iterating over <b>as object</b>, you can use the various
 *       stub classes from <code>\MovLib\Data\Stub\*</code> for IDE code completion.</li>
 *       <li><code>$attributes</code> the attributes array of the image containing the following offsets:
 *       <code>"alt"</code> (empty), <code>"src"</code>, <code>"width"</code> and <code>"height"</code>.</li>
 *       <li><code>$index</code> the current loop index.</li>
 *       <li><code>$total</code> the total images count.</il>
 *     </ol>
 *   </li>
 * </ul>
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Images extends \MovLib\Presentation\Partial\Lists\AbstractList {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The image style of the image's.
   *
   * @var int
   */
  public $imageStyle = AbstractBaseImage::STYLE_SPAN_02;

  /**
   * The list's items attributes.
   *
   * @var array
   */
  public $listItemsAttributes;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new images list.
   *
   * @param \MovLib\Data\Images\AbstractImages $images
   *   The images instance.
   * @param string $noItemsText [optional]
   *   The list's translated text if no items are present, defaults to no text.
   * @param array $attributes [optional]
   *   The list's attributes, defaults to <code>[ "class" => "no-list row" ]</code>.
   * @param array $listItemsAttributes [optional]
   *   The list's items attributes, will be applied to each list item's <code><li></code> element. Defaults to
   *   <code>[ "class" => "span span--2" ]</code>.
   */
  public function __construct($listItems, $noItemsText = null, array $attributes = null, array $listItemsAttributes = null) {
    if (!$attributes) {
      $attributes = [ "class" => "no-list row" ];
    }
    parent::__construct($listItems, $noItemsText, $attributes);
    $this->listItemsAttributes = $listItemsAttributes ?: [ "class" => "span span--2" ];
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  protected function render() {
    if (empty($this->listItems)) {
      return $this->noItemsText;
    }

    $list = null;
    foreach ($this->listItems as $delta => $item) {
      if ($this->closure) {
        $item = call_user_func($this->closure, $item, $delta);
      }
      else {
        $item = $this->getImage($this->imageStyle);
      }
      $list .= "<li{$this->expandTagAttributes($this->listItemsAttributes)}>{$item}</li>";
    }
    return "<ol{$this->expandTagAttributes($this->attributes)}>{$list}</ol>";
  }

}
