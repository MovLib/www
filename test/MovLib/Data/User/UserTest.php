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
namespace MovLib\Data\User;

use \MovLib\Data\User\User;
use \MovLib\Tool\Console\Command\Development\SeedImport;

/**
 * @coversDefaultClass \MovLib\Data\User\User
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class UserTest extends \MovLib\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /** @var \MovLib\Presentation\User\User */
  protected $user;


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  /**
   * Called before each test.
   */
  protected function setUp() {
    $this->user = new User(User::FROM_ID, 1);
  }


  // ------------------------------------------------------------------------------------------------------------------- Data Provider


  /**
   * @internal No ID test necessary, {@see UserTest::setUp()}!
   */
  public function dataProviderTestConstruct() {
    return [
      [ User::FROM_EMAIL, "richard@fussenegger.info" ],
      [ User::FROM_NAME, "Fleshgrinder" ],
      [ User::FROM_NAME, "fleshgrinder" ],
      [ User::FROM_NAME, "FlEsHgRiNdEr" ],
      [ User::FROM_NAME, "FLESHGRINDER" ],
    ];
  }

  public function dataProviderTestConstructException() {
    return [
      [ User::FROM_EMAIL, "phpunit@movlib.org" ],
      [ User::FROM_ID, -1 ],
      [ User::FROM_NAME, "PHPUnit" ],
    ];
  }

  public function dataProviderImageStyles() {
    return [
      [ User::STYLE_SPAN_01 ],
      [ User::STYLE_SPAN_02 ],
    ];
  }


  // ------------------------------------------------------------------------------------------------------------------- Tests


  /**
   * @covers ::commit
   * @global \MovLib\Tool\Database $db
   */
  public function testCommit() {
    global $db;

    // Setup
    $this->setProperty($this->user, "imageChanged", $_SERVER["REQUEST_TIME"]);
    $this->setProperty($this->user, "imageExtension", "tst");

    // Test
    $this->user->commit();
    $result = $db->query("SELECT UNIX_TIMESTAMP(`image_changed`), `image_extension` FROM `users` WHERE `id` = ? LIMIT 1", "d", [ $this->user->id ])->get_result()->fetch_row();
    $this->assertEquals($_SERVER["REQUEST_TIME"], $result[0]);
    $this->assertEquals("tst", $result[1]);

    // Teardown
    (new SeedImport())->databaseImport([ "users" ]);
  }

  /**
   * @covers ::__construct
   * @dataProvider dataProviderTestConstruct
   */
  public function testConstruct($from, $value) {
    $this->assertEquals(1, (new User($from, $value))->id);
  }

  /**
   * @covers ::__construct
   * @dataProvider dataProviderTestConstructException
   * @expectedException \DomainException
   */
  public function testConstructNoUser($from, $value) {
    new User($from, $value);
  }

  /**
   * @covers ::deleteImage
   */
  public function testDeleteImage() {
    $this->invoke($this->user, "deleteImage");
    foreach ([ User::STYLE_SPAN_01, User::STYLE_SPAN_02 ] as $style) {
      $this->assertFileNotExists($this->invoke($this->user, "getImagePath", [ $style ]));
    }
    $this->assertFalse($this->user->imageExists);
    $this->assertNull($this->getProperty($this->user, "imageChanged"));
    $this->assertNull($this->getProperty($this->user, "imageExtension"));

    // Teardown
    $seedImport = new SeedImport();
    $seedImport->databaseImport([ "users" ]);
    $seedImport->uploadImport([ "user" ]);
  }

  /**
   * @covers ::deleteImage
   */
  public function testDeleteImageFileMissing() {
    // Setup
    $paths = [
      $this->invoke($this->user, "getImagePath", [ User::STYLE_SPAN_01 ]),
      $this->invoke($this->user, "getImagePath", [ User::STYLE_SPAN_02 ]),
    ];
    foreach ($paths as $path) {
      unlink($path);
    }

    // Teardown
    $this->invoke($this->user, "deleteImage");
    foreach ($paths as $path) {
      $this->assertFileNotExists($path);
    }

    // Teardown
    $seedImport = new SeedImport();
    $seedImport->databaseImport([ "users" ]);
    $seedImport->uploadImport([ "user" ]);
  }

  /**
   * @covers ::getImageStyle
   * @dataProvider dataProviderImageStyles
   * @global \MovLib\TestKernel $kernel
   */
  public function testGetImageStyle($style) {
    global $kernel;
    $styleObj = $this->user->getStyle($style);
    $this->assertInstanceOf("\\MovLib\\Data\\Image\\Style", $styleObj);
    $this->assertEquals("Avatar image of Fleshgrinder.", $styleObj->alt);
    $this->assertEquals("//{$kernel->domainStatic}/upload/user/Fleshgrinder.{$style}.jpg?c=" . $this->getProperty($this->user, "imageChanged"), $styleObj->src);
    $this->assertEquals($style, $styleObj->height);
    $this->assertEquals($style, $styleObj->width);
    $this->assertEquals("/user/Fleshgrinder", $styleObj->route);
  }

  /**
   * @covers ::uploadImage
   * @global \MovLib\TestKernel $kernel
   */
  public function testUploadImage() {
    global $kernel;
    $source = tempnam(sys_get_temp_dir(), "phpunit");
    copy("{$kernel->documentRoot}/conf/seed/upload/user/{$this->getProperty($this->user, "imageName")}." . User::STYLE_SPAN_02 . ".jpg", $source);
    $this->invoke($this->user, "deleteImage");
    $this->user->upload($source, "jpg", 220, 220);
    foreach ([ User::STYLE_SPAN_01, User::STYLE_SPAN_02 ] as $style) {
      $this->assertFileExists($this->invoke($this->user, "getImagePath", [ $style ]));
    }
    $this->assertEquals(1, $this->getProperty($this->user, "imageExists"));
    $this->assertEquals($_SERVER["REQUEST_TIME"], $this->getProperty($this->user, "imageChanged"));
    $this->assertEquals("jpg", $this->getProperty($this->user, "imageExtension"));
  }

}
