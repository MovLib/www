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
 * Create HTML description list: <code><dl></code>
 *
 * <h2>Usage</h2>
 * <ul>
 *   <li>The item's parameter must be passed as numeric array in the following format:
 *     <pre>[
 *       [ "dt1" => "dd1" ],
 *       [ "dt2" => "dd2" ],
 *     ];</pre>
 *   </li>
 *   <li>The closure will be called with the following arguments: <code>$this->closure(&$dt, &$dd, $index, $total);</code></li>
 * </ul>
 *
 * @link http://www.w3.org/TR/html-markup/dl.html
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Description extends \MovLib\Presentation\Partial\Lists\AbstractList {

  /**
   * Get the string representation of the HTML description list.
   *
   * @return string
   *   The string representation of the HTML description list.
   */
  public function __toString() {
    if (($c = count($this->listItems))) {
      $list = null;
      for ($i = 0; $i < $c; ++$i) {
        if ($this->closure) {
          call_user_func_array($this->closure, [ &$this->listItems[$i][0], &$this->listItems[$i][1], $i, $c ]);
        }
        $list .= "<dt>{$this->listItems[$i][0]}</dt><dd>{$this->listItems[$i][1]}</dd>";
      }
      return "<dl{$this->expandTagAttributes($this->attributes)}>{$list}</dl>";
    }
    return $this->noItemsText;
  }

}
