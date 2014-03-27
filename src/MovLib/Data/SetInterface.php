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
namespace MovLib\Data;

/**
 * Defines the interface for set objects.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
interface SetInterface {

  /**
   * Must return the total count of all available entities.
   *
   * @return integer
   *   The total count of all available entities.
   */
  public function getCount();

  /**
   * Must return the demanded amount of elements ordered by the given parameter.
   *
   * @param string $by
   *   The SQL queries <code>ORDER BY</code> content, e.g. <code>"`created` DESC"</code>.
   * @param integer $offset
   *   The offset, usually provided by the {@see \MovLib\Presentation\PaginationTrait}.
   * @param integer $rowCount
   *   The limit, usually provided by the {@see \MovLib\Presentation\PaginationTrait}.
   * @return \mysqli_result|array
   *   A {@see \mysqli_result} is the preferred type, but arrays are allowed as well.
   */
  public function getOrdered($by, $offset, $rowCount);

}
