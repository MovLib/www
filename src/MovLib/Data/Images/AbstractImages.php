<?php

/* !
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
namespace MovLib\Data\Images;

/**
 * Default implementation for images.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractImages extends \MovLib\Data\Database implements \MovLib\Data\InterfaceImageStyles {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The entities representing an image.
   *
   * @internal
   *   We don't call this property "images" because it would be confusing, they contain much more data from time to time.
   * @var array
   */
  public $entities;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get the image style's attributes.
   *
   * This will add (or override) the width, height and src attributes in the attributes array you pass in.
   *
   * @param mixed $offset
   *   The offset of the image.
   * @param mixed $style
   *   The style for which you want the attributes.
   * @return array
   *   The image attributes as associative array.
   */
  public function getImageStyleAttributes($offset, $style) {
    if (!is_array($this->entities[$offset]["styles"])) {
      $this->entities[$offset]["styles"] = unserialize($this->entities[$offset]["styles"]);
    }
    $this->entities[$offset]["styles"][$style]["alt"] = "";
    return $this->entities[$offset]["styles"][$style];
  }

}
