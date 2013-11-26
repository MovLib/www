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
 * Create list of items separated by glue.
 *
 * <h2>Usage</h2>
 * <ul>
 *   <li>The item's parameter must be passed as numeric array with string values: <code>[ "item-1", "item-2" ]</code></li>
 *   <li>The closure must return the altered item and will be called with the following arguments:
 *   <code>$this->closure($item, $index, $total);</code></li>
 * </ul>
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class GlueSeparated extends \MovLib\Presentation\Partial\Lists\AbstractList {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The glue used to separate the items.
   *
   * @var string
   */
  public $glue;

  /**
   * String to insert before the list.
   *
   * @var string
   */
  public $listBefore;

  /**
   * String to insert after the list.
   *
   * @var string
   */
  public $listAfter;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new glue separated list.
   *
   * @param mixed $listItems
   *   The list's items.
   * @param string $noItemsText [optional]
   *   The list's translated text if no items are present, defaults to no text.
   * @param string $glue [optional]
   *   The glue to connect the list items with, defaults to <code>", "</code>
   * @param string $listBefore [optional]
   *   The translated text to display before the list, defaults to no text.
   * @param string $listAfter [optional]
   *   The translated text to display before the list, defaults to no text.
   */
  public function __construct($listItems, $noItemsText = null, $glue = ", ", $listBefore = null, $listAfter = null) {
    parent::__construct($listItems, $noItemsText);
    $this->glue       = $glue;
    $this->listBefore = $listBefore;
    $this->listAfter  = $listAfter;
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
      $list .= $list ? "{$this->glue}{$item}" : $item;
    }
    return "{$this->listBefore}{$list}{$this->listAfter}";
  }

}
