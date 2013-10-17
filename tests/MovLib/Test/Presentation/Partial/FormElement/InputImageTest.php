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
namespace MovLib\Test\Presentation\Partial\FormElement;

use \MovLib\Data\UserExtended;
use \MovLib\Presentation\Partial\FormElement\InputImage;

/**
 * @coversDefaultClass \MovLib\Presentation\Partial\FormElement\InputImage
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class InputImageTest extends \PHPUnit_Framework_TestCase {

  /**
   * @covers ::__construct
   * @group Presentation
   */
  public function testConstruct() {
    $concreteImage = new UserExtended(UserExtended::FROM_ID, 1);
    $inputImage    = new InputImage("phpunit", "PHPUnit", $concreteImage, [ "foo" => "bar" ]);
    foreach ([ "accept", "data-max-filesize", "data-min-height", "data-min-width", "type", "foo" ] as $key) {
      $this->assertArrayHasKey($key, $inputImage->attributes);
    }
    $this->assertEquals("image/jpeg,image/png", $inputImage->attributes["accept"]);
    $this->assertEquals(ini_get("upload_max_filesize"), $inputImage->attributes["data-max-filesize"]);
    $this->assertEquals("file", $inputImage->attributes["type"]);
    $this->assertEquals($concreteImage, get_reflection_property($inputImage, "image")->getValue($inputImage));
    $this->assertEquals($concreteImage->imageHeight, $inputImage->attributes["data-min-height"]);
    $this->assertEquals($concreteImage->imageWidth, $inputImage->attributes["data-min-width"]);
    $this->assertEquals("bar", $inputImage->attributes["foo"]);
    $this->assertInstanceOf("\\MovLib\\Presentation\\Partial\\Help", get_reflection_property($inputImage, "help")->getValue($inputImage));
  }

  /**
   * @covers ::__construct
   * @group Presentation
   */
  public function testConstructGlobalDimensionConstraints() {
    $concreteImage = new UserExtended();
    $inputImage    = new InputImage("phpunit", "PHPUnit", $concreteImage);
    $this->assertEquals($GLOBALS["movlib"]["image_min_height"], $inputImage->attributes["data-min-height"]);
    $this->assertEquals($GLOBALS["movlib"]["image_min_width"], $inputImage->attributes["data-min-width"]);
  }

  /**
   * @covers ::__toString
   * @group Presentation
   */
  public function testToStringImageExists() {
    $concreteImage = new UserExtended(UserExtended::FROM_ID, 1);
    $inputImage    = (string) new InputImage("phpunit", "PHPUnit", $concreteImage);
    $this->assertRegExp("/<img[a-z0-9=' \.\?\/:]+>/", $inputImage);
    $this->assertContains("<label for='phpunit'>PHPUnit</label>", $inputImage);
    $this->assertRegExp("/<input[a-z0-9=' \/,-]+>/", $inputImage);
  }

  /**
   * @covers ::__toString
   * @group Presentation
   */
  public function testToStringNoImage() {
    $concreteImage = new UserExtended(UserExtended::FROM_ID, 1);
    $concreteImage->imageExists = false;
    $inputImage    = (string) new InputImage("phpunit", "PHPUnit", $concreteImage);
    $this->assertNotContains("<img", $inputImage);
    $this->assertContains("<label for='phpunit'>PHPUnit</label>", $inputImage);
    $this->assertRegExp("/<input[a-z0-9=' \/,-]+>/", $inputImage);
  }

  /**
   * @covers ::validate
   * @expectedException \MovLib\Exception\ValidationException
   * @expectedExceptionMessage mandatory
   * @group Validation
   */
  public function testValidateRequired() {
    (new InputImage("phpunit", "PHPUnit", new UserExtended(UserExtended::FROM_ID, 1), [ "required" ]))->validate();
  }

}
