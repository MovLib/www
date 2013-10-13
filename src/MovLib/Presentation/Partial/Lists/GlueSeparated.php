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
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class GlueSeparated extends \MovLib\Presentation\Partial\Lists\AbstractList {

  /**
   * The glue used to separate the items.
   *
   * @var string
   */
  public $glue = ", ";

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

  /**
   * Get the string representation of the glue separated list.
   *
   * @return string
   *   The string representation of the glue separated list.
   */
  public function __toString() {
    if (($c = count($this->listItems))) {
      $list = null;
      for ($i = 0; $i < $c; ++$i) {
        if ($this->closure) {
          $this->listItems[$i] = call_user_func_array($this->closure, [ $this->listItems[$i], $i, $c ]);
        }
        $list .= ($i !== 0) ? "{$this->glue}{$this->listItems[$i]}" : $this->listItems[$i];
      }
      return "{$this->listBefore}{$list}{$this->listAfter}";
    }
    return $this->noItemsText;
  }

}
