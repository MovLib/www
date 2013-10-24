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
namespace MovLib\Data\Image;

use \MovLib\Data\Image\AbstractImage as Image;

/**
 * @coversDefaultClass \MovLib\Data\Image\AbstractImage
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class AbstractImageTest extends \MovLib\TestCase {

  // ------------------------------------------------------------------------------------------------------------------- Properties


  private static $dirOriginal;
  private static $dirStyles;
  private static $tmpImage;

  /** @var \MovLib\Data\Image\AbstractImage */
  private $image;

  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  public static function setUpBeforeClass() {
    self::$dirOriginal = "{$_SERVER["DOCUMENT_ROOT"]}/uploads/originals/phpunit/";
    self::$dirStyles = "{$_SERVER["DOCUMENT_ROOT"]}/uploads/phpunit/";
    self::$tmpImage = tempnam(ini_get("upload_tmp_dir"), "phpunit") . ".jpg";
    self::exec("convert -size 500x500 xc: +noise Random " . self::$tmpImage);
    self::exec("mkdir -p '" . self::$dirOriginal . "' '" . self::$dirStyles . "'");
  }

  protected function setUp() {
    $this->image = $this->getMockForAbstractClass("\\MovLib\\Data\\Image\\AbstractImage");
    foreach ([ "Changed" => $_SERVER["REQUEST_TIME"], "Directory" => "phpunit", "Extension" => "jpg", "Name" => "phpunit" ] as $property => $value) {
      $this->setProperty($this->image, "image{$property}", $value);
    }
  }

  protected function tearDown() {
    $this->exec("rm -r '" . self::$dirOriginal . "*' '" . self::$dirStyles . "*'");
  }

  public static function tearDownAfterClass() {
    self::execDetached("rm -r '" . self::$dirOriginal . "' '" . self::$dirStyles . "'");
  }

  // ------------------------------------------------------------------------------------------------------------------- Tests

  /**
   * We need this for 100% coverage.
   */
  public function testConstants() {
    $this->assertNotEmpty(\MovLib\Data\Image\SPAN_01);
    $this->assertNotEmpty(\MovLib\Data\Image\SPAN_02);
    $this->assertNotEmpty(\MovLib\Data\Image\SPAN_03);
    $this->assertNotEmpty(\MovLib\Data\Image\SPAN_04);
    $this->assertNotEmpty(\MovLib\Data\Image\SPAN_05);
    $this->assertNotEmpty(\MovLib\Data\Image\SPAN_06);
    $this->assertNotEmpty(\MovLib\Data\Image\SPAN_07);
    $this->assertNotEmpty(\MovLib\Data\Image\SPAN_08);
    $this->assertNotEmpty(\MovLib\Data\Image\SPAN_09);
    $this->assertNotEmpty(\MovLib\Data\Image\SPAN_10);
    $this->assertNotEmpty(\MovLib\Data\Image\SPAN_11);
    $this->assertNotEmpty(\MovLib\Data\Image\SPAN_12);
  }

  /**
   * @covers ::convertImage
   */
  public function testConvertImageNoDimensionsCropFalse() {
    $path = $this->invoke($this->image, "convertImage", [ self::$tmpImage, Image::IMAGE_STYLE_SPAN_02 ]);
    $this->assertTrue(is_file($path) && is_readable($path) && is_writable($path));
    list($width, $height) = getimagesize($path);
    foreach ([
    $width, $this->getProperty($this->image, "imageStyles")[Image::IMAGE_STYLE_SPAN_02]["width"],
    $height, $this->getProperty($this->image, "imageStyles")[Image::IMAGE_STYLE_SPAN_02]["height"]
    ] as $actual) {
      $this->assertEquals(Image::IMAGE_STYLE_SPAN_02, $actual);
    }
  }

  /**
   * @covers ::convertImage
   */
  public function testConvertImageDimensionsCropTrue() {
    $path = $this->invoke($this->image, "convertImage", [ self::$tmpImage, Image::IMAGE_STYLE_SPAN_02, Image::IMAGE_STYLE_SPAN_02, Image::IMAGE_STYLE_SPAN_01, true ]);
    $this->assertTrue(is_file($path) && is_readable($path) && is_writable($path));
    list($width, $height) = getimagesize($path);
    $this->assertEquals(Image::IMAGE_STYLE_SPAN_02, $width);
    $this->assertEquals(Image::IMAGE_STYLE_SPAN_02, $this->getProperty($this->image, "imageStyles")[Image::IMAGE_STYLE_SPAN_02]["width"]);
    $this->assertEquals(Image::IMAGE_STYLE_SPAN_01, $height);
    $this->assertEquals(Image::IMAGE_STYLE_SPAN_01, $this->getProperty($this->image, "imageStyles")[Image::IMAGE_STYLE_SPAN_02]["height"]);
  }

  /**
   * @covers ::convertImage
   * @expectedException \MovLib\Exception\ImageException
   */
  public function testConvertImageException() {
    $this->invoke($this->image, "convertImage", [ __FILE__, Image::IMAGE_STYLE_SPAN_02 ]);
  }

  /**
   * @covers ::deleteImage
   */
  public function testDeleteImage() {
    $this->image->imageExists = true;
    $imageName                = $this->getProperty($this->image, "imageName");
    $imageExtension           = $this->getProperty($this->image, "imageExtension");
    $imageStyles              = [ Image::IMAGE_STYLE_SPAN_01 => [ ], Image::IMAGE_STYLE_SPAN_02 => [ ] ];
    $imageOriginal            = self::$dirOriginal . "{$imageName}.{$imageExtension}";
    $imageStyle01             = self::$dirStyles . "{$imageName}." . Image::IMAGE_STYLE_SPAN_01 . ".{$imageExtension}";
    $imageStyle02             = self::$dirStyles . "{$imageName}." . Image::IMAGE_STYLE_SPAN_02 . ".{$imageExtension}";
    $this->setProperty($this->image, "imageStyles", serialize($imageStyles));
    foreach ([ $imageOriginal, $imageStyle01, $imageStyle02 ] as $image) {
      touch($image);
    }
    $this->assertChaining($this->image, $this->invoke($this->image, "deleteImage"));
    $found = false;
    foreach ($this->getStaticProperty("\\MovLib\\Data\\Delayed\\MethodCalls", "stack") as $delayedMethod) {
      if ($delayedMethod[0] == [ $this->image, "commit" ]) {
        $found = true;
        break;
      }
    }
    $this->assertTrue($found, "Could not find commit() call in delayed methods stack!");
    foreach ([ $imageOriginal, $imageStyle01, $imageStyle02 ] as $image) {
      $this->assertFileNotExists($image);
    }
  }

}
