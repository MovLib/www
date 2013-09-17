<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link http://movlib.org/ MovLib}.
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
namespace MovLib\Presentation\Partial;

/**
 * Represents a general purpose list which can be used as comma separated, unordered, ordered and description list.
 *
 * The name Lists was chosen, because <code>list</code> is a reserved keyword in PHP.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Lists extends \MovLib\Presentation\AbstractBase {

  /**
   * Associative array containing the list's HTML attributes.
   *
   * @var array
   */
  public $attributes;

  /**
   * Numeric array containing the list items.
   *
   * @var array
   */
  public $items;

  /**
   * The text to dispay if no items are present.
   *
   * @var string
   */
  public $noItemsText;


  /**
   * Instatiate new List partial.
   *
   * @param array $items
   *   Numeric array containing the list elements and optionally their attributes as associative array.
   *   Format: <code>[ "list_item" ]</code> or <code>[ [ "list_item", [ "attr" => "attr_value" ] ] ]</code>
   *   Please note that description lists need a special format: <code>[ [ "dt_text", "dd_text" ] ]</code>
   * @param string $noItemsText
   *   The text to dispay if no items are present.
   * @param array $attributes [optional]
   *   Associative array containing the list's attributes.
   */
  public function __construct($items, $noItemsText, array $attributes = null) {
    $this->items = $items;
    $this->noItemsText = $noItemsText;
    $this->attributes = $attributes;
  }

  /**
   * Render this list as comma separated list.
   *
   * @param callable $callback [optional]
   *   The callback will be called with each list item.
   * @return string
   *   The comma separated list.
   */
  public function toCommaSeparatedList($callback = null) {
    if (($c = count($this->items))) {
      $list = "";
      for ($i = 0; $i < $c; ++$i) {
        if (!empty($this->items[$i])) {
          if ($i !== 0) {
            $list .= ", ";
          }
          $list .= $callback ? $callback($this->items[$i]) : $this->items[$i];
        }
      }
      return $list;
    }
    return $this->noItemsText;
  }

  /**
   * Render this list as description list.
   *
   * @param callable $callback [optional]
   *   The callback will be called with each list item, the first parameter is the description title and the second
   *   parameter is the description data, e.g. <code>$callback($dt, $dd)</code>.
   *   Please note that description lists are harder to construce and therefore special attributes for the list elements
   *   have to be applied in the callback!
   * @return string
   *   The list as HTML description list or the no items text, if no items are present.
   */
  public function toDescriptionList($callback = null) {
    if (($c = count($this->items))) {
      $list = "";
      for ($i = 0; $i < $c; ++$i) {
        if ($callback) {
          $list = $callback($this->items[$i][0], $this->items[$i][1]);
        }
        else {
          $list .= "<dt>{$this->items[$i][0]}</dt><dd>{$this->items[$i][1]}</dd>";
        }
      }
      return "<dl{$this->expandTagAttributes($this->attributes)}>{$list}</dl>";
    }
    return $this->noItemsText;
  }

  /**
   * Render this list as simple HTML list.
   *
   * @param string $type [optional]
   *   Determines the list type. Defaults to <code>"ul"</code>.
   * @param callable $callback [optional]
   *   The callback will be called with each list item.
   * @return string
   *   The list as HTML ordered list or the no items text, if no items are present.
   */
  public function toHtmlList($type = "ul", $callback = null) {
    if (($c = count($this->items))) {
      $list = "";
      for ($i = 0; $i < $c; ++$i) {
        if ($callback) {
          $list .= $callback($this->items[$i]);
        }
        elseif (is_array($this->items[$i])) {
          $list .= "<li{$this->expandTagAttributes($this->items[$i][1])}>{$this->items[$i][0]}</li>";
        }
        else {
          $list .= "<li>{$this->items[$i]}</li>";
        }
      }
      return "<{$type}{$this->expandTagAttributes($this->attributes)}>{$list}</{$type}>";
    }
    return $this->noItemsText;
  }

}