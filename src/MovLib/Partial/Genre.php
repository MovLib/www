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
namespace MovLib\Partial;

/**
 * Defines generic listings with structured data.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Genre extends \MovLib\Core\Presentation\DependencyInjectionBase {

  /**
   * Format an array of genres as comma separated list.
   *
   * @param array $genres
   *   Array containing the genres to format.
   * @return string
   *   The genres formatted as comma separated list.
   */
  public function formatArray(array $genres) {
    $list = null;

    /// The "," is used to separate list items, please note the space after the comma!
    $comma = $this->intl->t(", ");

    /* @var $genre \MovLib\Data\Genre\Genre */
    foreach ($genres as $genre) {
      if ($list) {
        $list .= $comma;
      }
      $list .= "<a href='{$genre->route}' property='genre'>{$genre->name}</a>";
    }

    return $list;
  }

}
