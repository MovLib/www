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
 * Create HTML ordered list: <code><ol></code>
 *
 * <h2>Usage</h2>
 * <ul>
 *   <li>The item's parameter must be passed as numeric array with string values: <code>[ "item-1", "item-2" ]</code></li>
 *   <li>The closure must return the altered item and will be called with the following arguments:
 *   <code>$this->closure($item, $index, $total);</code></li>
 * </ul>
 *
 * @link http://www.w3.org/TR/html-markup/ol.html
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Ordered extends \MovLib\Presentation\Partial\Lists\AbstractList {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The list's tag.
   *
   * @var string
   */
  protected $tag = "ol";

  /**
   * The list's items attributes.
   *
   * @var array
   */
  public $listItemsAttributes;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new HTML list.
   *
   * @param mixed $listItems
   *   The list's items, defaults to no items.
   * @param string $noItemsText [optional]
   *   The list's translated text if no items are present, defaults to no text.
   * @param array $attributes [optional]
   *   The list's attributes, defaults to no attributes.
   * @param array $listItemsAttributes [optional]
   *   The list's items attributes, will be applied to each list item's <code><li></code> element.
   */
  public function __construct($listItems, $noItemsText = "", array $attributes = null, array $listItemsAttributes = null) {
    parent::__construct($listItems, $noItemsText, $attributes);
    $this->listItemsAttributes = $listItemsAttributes;
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
      $list .= "<li{$this->expandTagAttributes($this->listItemsAttributes)}>{$item}</li>";
    }
    return "<{$this->tag}{$this->expandTagAttributes($this->attributes)}>{$list}</{$this->tag}>";

  }

}
