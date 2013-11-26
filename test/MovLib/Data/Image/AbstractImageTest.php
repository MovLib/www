<?php

/* !
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
namespace MovLib\Data\Image;

use \MovLib\Data\UnixShell as sh;
use \MovLib\Data\Image\AbstractBaseImage as Image;

/**
 * @coversDefaultClass \MovLib\Data\Image\AbstractImage
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class AbstractImageTest extends \MovLib\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  private static $dirOriginal;
  private static $dirStyles;
  private static $tmpImage;

  /** @var \MovLib\Data\Image\AbstractBaseImage */
  private $image;


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  /**
   * @global \MovLib\TestKernel $kernel
   */
  public static function setUpBeforeClass() {
    global $kernel;
    self::$dirOriginal = "{$kernel->documentRoot}/uploads/originals/phpunit/";
    self::$dirStyles = "{$kernel->documentRoot}/uploads/phpunit/";
    self::$tmpImage = tempnam(ini_get("upload_tmp_dir"), "phpunit") . ".jpg";
    sh::execute("convert -size 500x500 xc: +noise Random " . self::$tmpImage);
    sh::execute("mkdir -p '" . self::$dirOriginal . "' '" . self::$dirStyles . "'");
  }

  protected function setUp() {
    $this->image = $this->getMockForAbstractClass("\\MovLib\\Data\\Image\\AbstractImage");
    foreach ([ "Changed" => $_SERVER["REQUEST_TIME"], "Directory" => "phpunit", "Extension" => "jpg", "Name" => "phpunit" ] as $property => $value) {
      $this->setProperty($this->image, "image{$property}", $value);
    }
  }

  protected function tearDown() {
    sh::execute("rm -r '" . self::$dirOriginal . "*' '" . self::$dirStyles . "*'");
  }

  public static function tearDownAfterClass() {
    sh::executeDetached("rm -r '" . self::$dirOriginal . "' '" . self::$dirStyles . "'");
  }


  // ------------------------------------------------------------------------------------------------------------------- Tests


  /**
   * @covers ::convertImage
   */
  public function testConvertImageNoDimensionsCropFalse() {
    $path = $this->invoke($this->image, "convertImage", [ self::$tmpImage, Image::STYLE_SPAN_02 ]);
    $this->assertTrue(is_file($path) && is_readable($path) && is_writable($path));
    list($width, $height) = getimagesize($path);
    foreach ([
    $width, $this->getProperty($this->image, "imageStyles")[Image::STYLE_SPAN_02]["width"],
    $height, $this->getProperty($this->image, "imageStyles")[Image::STYLE_SPAN_02]["height"]
    ] as $actual) {
      $this->assertEquals(Image::STYLE_SPAN_02, $actual);
    }
  }

  /**
   * @covers ::convertImage
   */
  public function testConvertImageDimensionsCropTrue() {
    $path = $this->invoke($this->image, "convertImage", [ self::$tmpImage, Image::STYLE_SPAN_02, Image::STYLE_SPAN_02, Image::STYLE_SPAN_01, true ]);
    $this->assertTrue(is_file($path) && is_readable($path) && is_writable($path));
    list($width, $height) = getimagesize($path);
    $this->assertEquals(Image::STYLE_SPAN_02, $width);
    $this->assertEquals(Image::STYLE_SPAN_02, $this->getProperty($this->image, "imageStyles")[Image::STYLE_SPAN_02]["width"]);
    $this->assertEquals(Image::STYLE_SPAN_01, $height);
    $this->assertEquals(Image::STYLE_SPAN_01, $this->getProperty($this->image, "imageStyles")[Image::STYLE_SPAN_02]["height"]);
  }

  /**
   * @covers ::convertImage
   * @expectedException \MovLib\Exception\ImageException
   */
  public function testConvertImageException() {
    $this->invoke($this->image, "convertImage", [ __FILE__, Image::STYLE_SPAN_02 ]);
  }

  /**
   * @covers ::deleteImage
   */
  public function testDeleteImage() {
    $this->image->exists = true;
    $imageName                = $this->getProperty($this->image, "imageName");
    $imageExtension           = $this->getProperty($this->image, "imageExtension");
    $imageStyles              = [ Image::STYLE_SPAN_01 => [ ], Image::STYLE_SPAN_02 => [ ] ];
    $imageOriginal            = self::$dirOriginal . "{$imageName}.{$imageExtension}";
    $imageStyle01             = self::$dirStyles . "{$imageName}." . Image::STYLE_SPAN_01 . ".{$imageExtension}";
    $imageStyle02             = self::$dirStyles . "{$imageName}." . Image::STYLE_SPAN_02 . ".{$imageExtension}";
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

  /**
   * @covers ::commit
   * @todo Implement commit
   */
  public function testCommit() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::generateImageStyles
   * @todo Implement generateImageStyles
   */
  public function testGenerateImageStyles() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getImageStyle
   * @todo Implement getImageStyle
   */
  public function testGetImageStyle() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getImagePath
   * @todo Implement getImagePath
   */
  public function testGetImagePath() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getImageURL
   * @todo Implement getImageURL
   */
  public function testGetImageURL() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::uploadImage
   * @todo Implement uploadImage
   */
  public function testUploadImage() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

}
