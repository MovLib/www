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
 * Create HTML description list: <code><dl></code>
 *
 * @link http://www.w3.org/TR/html-markup/dl.html
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Description extends \MovLib\Presentation\Partial\Lists\AbstractList {

  /**
   * @inheritdoc
   */
  protected function render() {
    if (empty($this->listItems)) {
      return $this->noItemsText;
    }

    $list = null;
    foreach ($this->listItems as $dt => $dd) {
      $dd    = is_array($dd) ? "<dd{$this->expandTagAttributes($dd[1])}>{$dd[0]}</dd>" : "<dd>{$dd}</dd>";
      $list .= "<dt>{$dt}</dt>{$dd}";
    }
    return "<dl{$this->expandTagAttributes($this->attributes)}>{$list}</dl>";
  }

}
