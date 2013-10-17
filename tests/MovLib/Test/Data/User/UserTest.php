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
namespace MovLib\Test\Data\User;

use \MovLib\Data\User\User;

/**
 * @coversDefaultClass
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class UserTest extends \MovLib\Test\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Data Provider


  public static function dataProviderTestConstruct() {
    return [
      [ User::FROM_EMAIL, "richard@fussenegger.info" ],
      [ User::FROM_ID, 1 ],
      [ User::FROM_NAME, "Fleshgrinder" ],
      [ User::FROM_NAME, "fleshgrinder" ],
      [ User::FROM_NAME, "FlEsHgRiNdEr" ],
      [ User::FROM_NAME, "FLESHGRINDER" ],
    ];
  }

  public static function dataProviderTestConstructException() {
    return [
      [ User::FROM_EMAIL, "phpunit@movlib.org" ],
      [ User::FROM_ID, -1 ],
      [ User::FROM_NAME, "PHPUnit" ],
    ];
  }

  public static function dataProviderImageStyles() {
    return [
      [ User::IMAGE_STYLE_SPAN_01 ],
      [ User::IMAGE_STYLE_SPAN_02 ],
    ];
  }


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  public function tearDown() {
    $this->exec("movdev db -s users");
  }


  // ------------------------------------------------------------------------------------------------------------------- Tests


  /**
   * @covers ::__construct
   * @dataProvider dataProviderTestConstruct
    */
  public function testConstruct($from, $value) {
    $user = new User($from, $value);
    $this->assertEquals(1, $user->id);
    foreach ([ User::IMAGE_STYLE_SPAN_01, User::IMAGE_STYLE_SPAN_02 ] as $style) {
      $this->assertFileExists($this->invoke($user, "getImagePath", [ $style ]));
    }
    $this->assertEquals(1380707126, $this->getProperty($user, "imageChanged"));
    $this->assertEquals("jpg", $this->getProperty($user, "imageExtension"));
    $this->assertEquals(1, $this->getProperty($user, "imageExists"));
    $this->assertEquals("user", $this->getProperty($user, "imageDirectory"));
    $this->assertEquals("fleshgrinder", $this->getProperty($user, "imageName"));
    $this->assertEquals("Fleshgrinder", $user->name);
    $this->assertEquals("/user/fleshgrinder", $user->route);
    $this->assertEquals([ "user_id" => "d", "email" => "s", "name" => "s" ], $this->getProperty($user, "types"));
    return $user;
  }

  /**
   * @covers ::__construct
   * @dataProvider dataProviderTestConstructException
   * @expectedException \MovLib\Exception\UserException
    */
  public function testConstructNoUser($from, $value) {
    new User($from, $value);
  }

  /**
   * @covers ::deleteImage
     */
  public function testDeleteImage() {
    // Make sure the image exists before attempting to delete it.
    $user = $this->testConstruct(User::FROM_ID, 1);
    $this->invoke($user, "deleteImage");
    foreach ([ User::IMAGE_STYLE_SPAN_01, User::IMAGE_STYLE_SPAN_02 ] as $style) {
      $this->assertFileNotExists($this->invoke($user, "getImagePath", [ $style ]));
    }
    $this->assertFalse($this->getProperty($user, "imageExists"));
    $this->assertNull($this->getProperty($user, "imageChanged"));
    $this->assertNull($this->getProperty($user, "imageExtension"));
  }

  /**
   * @covers ::getImageStyle
   * @dataProvider dataProviderImageStyles
     */
  public function testGetImageStyle($style) {
    $styleObj = (new User(User::FROM_ID, 1))->getImageStyle($style);
    $this->assertInstanceOf("\\MovLib\\Data\\Image\\Style", $styleObj);
    $this->assertEquals("Avatar image of Fleshgrinder.", $styleObj->alt);
    $this->assertEquals("{$GLOBALS["movlib"]["static_domain"]}uploads/user/fleshgrinder.{$style}.jpg?c=1380707126", $styleObj->src);
    $this->assertEquals($style, $styleObj->height);
    $this->assertEquals($style, $styleObj->width);
    $this->assertEquals("/user/fleshgrinder", $styleObj->route);
  }

  /**
   * @covers ::uploadImage
      */
  public function testUploadImage() {
    $user   = new User(User::FROM_ID, 1);
    $source = tempnam(sys_get_temp_dir(), "phpunit");
    copy("{$_SERVER["DOCUMENT_ROOT"]}/db/seeds/uploads/user/fleshgrinder." . User::IMAGE_STYLE_SPAN_02 . ".jpg", $source);
    $this->invoke($user, "deleteImage");
    $user->uploadImage($source, "jpg", 220, 220);
    foreach ([ User::IMAGE_STYLE_SPAN_01, User::IMAGE_STYLE_SPAN_02 ] as $style) {
      $this->assertFileExists($this->invoke($user, "getImagePath", [ $style ]));
    }
    $this->assertEquals(1, $this->getProperty($user, "imageExists"));
    $this->assertEquals($_SERVER["REQUEST_TIME"], $this->getProperty($user, "imageChanged"));
    $this->assertEquals("jpg", $this->getProperty($user, "imageExtension"));
  }

}
