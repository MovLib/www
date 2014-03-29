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
namespace MovLib\Partial\Listing;

/**
 * Defines the interface for listing partials.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
interface ListingInterface {

  /**
   * Every listing has to know how to convert itself to string.
   *
   * @return string
   *   The listing's string representation.
   */
  public function __toString();

  /**
   * Every listing has to implement a separate method that actually creates the individual list items and allow childs
   * to override that method.
   *
   * @param mixed $item
   *   The current loops item.
   * @param mixed $delta [optional]
   *   An optional delta, e.g. the key of an associative array.
   *
   *   If you have a multidimensional array don't pass a child as first parameter and the complete item as second
   *   parameter (yes, I saw that some times, I wouldn't write this otherwise). This is a waste of resources, simply
   *   pass the complete item to this method!
   * @return string
   *   The formatted item.
   */
  public function formatItem($item, $delta = null);

  /**
   * Get the listing.
   *
   * @param string $items
   *   The fully rendered items.
   * @return string
   *   The listing.
   */
  public function getListing($items);

}
