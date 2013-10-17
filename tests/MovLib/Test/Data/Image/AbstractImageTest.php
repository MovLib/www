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

use \MovLib\Data\Image\AbstractImage as Image;

/**
 * @coversDefaultClass \MovLib\Data\Image\AbstractImage
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class AbstractImageTest extends \MovLib\Test\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties

  private static $tmpImage;

  /**
   * @var \MovLib\Data\Image\AbstractImage
   */
  private $image;


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  public static function setUpBeforeClass() {
    self::$tmpImage = tempnam(sys_get_temp_dir(), "phpunit") . ".jpg";
    exec("convert -size 500x500 xc: +noise Random " . self::$tmpImage);
  }

  protected function setUp() {
    $this->image = $this->getMockForAbstractClass("\\MovLib\\Data\\Image\\AbstractImage");
    $this->setProperty($this->image, "imageName", "phpunit");
    $this->setProperty($this->image, "imageExtension", "jpg");
    $this->setProperty($this->image, "imageChanged", $_SERVER["REQUEST_TIME"]);
  }


  // ------------------------------------------------------------------------------------------------------------------- Tests


  /**
   * @covers ::convertImage
   * @group FileSystem
   * @group Presentation
   */
  public function testConvertImageNoDimensionsCropFalse() {
    $path = $this->invoke($this->image, "convertImage", [ self::$tmpImage, Image::IMAGE_STYLE_SPAN_02 ]);
    $this->assertTrue(is_file($path) && is_readable($path) && is_writable($path));
    list($width, $height) = getimagesize($path);
    $this->assertEquals(Image::IMAGE_STYLE_SPAN_02, $width);
    $this->assertEquals(Image::IMAGE_STYLE_SPAN_02, $this->getProperty($this->image, "imageStyles")[Image::IMAGE_STYLE_SPAN_02]["width"]);
    $this->assertEquals(Image::IMAGE_STYLE_SPAN_02, $height);
    $this->assertEquals(Image::IMAGE_STYLE_SPAN_02, $this->getProperty($this->image, "imageStyles")[Image::IMAGE_STYLE_SPAN_02]["height"]);
    unlink($path);
  }

  /**
   * @covers ::convertImage
   * @group FileSystem
   * @group Presentation
   */
  public function testConvertImageDimensionsCropTrue() {
    $path = $this->invoke($this->image, "convertImage", [ self::$tmpImage, Image::IMAGE_STYLE_SPAN_02, Image::IMAGE_STYLE_SPAN_02, Image::IMAGE_STYLE_SPAN_01, true ]);
    $this->assertTrue(is_file($path) && is_readable($path) && is_writable($path));
    list($width, $height) = getimagesize($path);
    $this->assertEquals(Image::IMAGE_STYLE_SPAN_02, $width);
    $this->assertEquals(Image::IMAGE_STYLE_SPAN_02, $this->getProperty($this->image, "imageStyles")[Image::IMAGE_STYLE_SPAN_02]["width"]);
    $this->assertEquals(Image::IMAGE_STYLE_SPAN_01, $height);
    $this->assertEquals(Image::IMAGE_STYLE_SPAN_01, $this->getProperty($this->image, "imageStyles")[Image::IMAGE_STYLE_SPAN_02]["height"]);
    unlink($path);
  }

  /**
   * @covers ::convertImage
   * @group FileSystem
   * @group Presentation
   * @expectedException \MovLib\Exception\ImageException
   */
  public function testConvertImageException() {
    $this->invoke($this->image, "convertImage", [ __FILE__, Image::IMAGE_STYLE_SPAN_02 ]);
  }

}
