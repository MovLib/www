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
namespace MovLib\Test\Data\Image;

use \MovLib\Data\Image\Style;

/**
 * @coversDefaultClass \MovLib\Data\Image\Style
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class StyleTest extends \MovLib\Test\TestCase {

  /**
   * @covers ::__construct
   * @group Presentation
   */
  public function testConstruct() {
    $expected = [
      "alt"    => "PHPUnit",
      "src"    => "{$GLOBALS["movlib"]["static_domain"]}img/logo/vector.svg",
      "width"  => 42,
      "height" => 42,
      "route"  => "/",
    ];
    $style = new Style($expected["alt"], $expected["src"], $expected["width"], $expected["height"], $expected["route"]);
    foreach ($expected as $property => $value) {
      $this->assertObjectHasAttribute($property, $style);
      $this->assertAttributeEquals($value, $property, $style);
    }
  }

}
