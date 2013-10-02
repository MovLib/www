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
namespace MovLib\Test\Data;

use \MovDev\Database;
use \MovLib\Presentation\Partial\FormElement\InputDate;
use \MovLib\Data\User;

/**
 * @coversDefaultClass \MovLib\Data\User
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class UserTest extends \PHPUnit_Framework_TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Data Provider


  public static function dataProviderTestConstruct() {
    return [
      [ User::FROM_EMAIL, "richard@fussenegger.info" ],
      [ User::FROM_ID, 1 ],
      [ User::FROM_NAME, "Fleshgrinder" ],
      [ User::FROM_NAME, "fleshgrinder" ],
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

  public static function dataProviderTestGetImageStyleAttributes() {
    return [
      [ User::IMAGE_STYLE_DEFAULT, [ "alt" => "PHPUnit" ] ],
      [ User::IMAGE_STYLE_SMALL, [ "alt" => "PHPUnit", "height" => 999, "width" => 999, "src" => "https://movlib.org/phpunit.jpg" ] ],
    ];
  }


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  public function tearDown() {
    exec("movdev db -s users");
  }


  // ------------------------------------------------------------------------------------------------------------------- Tests


  /**
   * @covers ::__construct
   * @covers \MovLib\Data\Database::selectAssoc
   * @dataProvider dataProviderTestConstruct
   * @group Database
   */
  public function testConstruct($from, $value) {
    $user = new User($from, $value);
    $this->assertEquals(1, $user->id);
    $this->assertEquals("Fleshgrinder", $user->name);
    $this->assertEquals("richard@fussenegger.info", $user->email);
    $this->assertTrue(is_bool($user->private));
    $this->assertTrue(is_bool($user->deactivated));
  }

  /**
   * @covers ::__construct
   * @covers \MovLib\Data\Database::selectAssoc
   * @dataProvider dataProviderTestConstructException
   * @expectedException \MovLib\Exception\UserException
   * @group Database
   * @group Uploads
   */
  public function testConstructException($from, $value) {
    new User($from, $value);
  }

  /**
   * @covers ::checkEmail
   * @covers \MovLib\Data\Database::selectAssoc
   * @group Database
   */
  public function testCheckEmail() {
    $user = new User();
    $this->assertTrue($user->checkEmail("richard@fussenegger.info"));
    $this->assertFalse($user->checkEmail("phpunit@movlib.org"));
  }

  /**
   * @covers ::checkName
   * @covers \MovLib\Data\Database::selectAssoc
   * @group Database
   */
  public function testCheckName() {
    $user = new User();
    $this->assertTrue($user->checkName("Fleshgrinder"));
    $this->assertFalse($user->checkName("PHPUnit"));
  }

  /**
   * @covers ::commit
   * @covers \MovLib\Data\Database::query
   * @group Database
   * @group FileSystem
   * @group Uploads
   */
  public function testCommit() {
    $user = new User(User::FROM_ID, 1);
    $user->birthday           = date(InputDate::RFC3339, $_SERVER["REQUEST_TIME"]);
    $user->countryId          = 1;
    $user->profile            = "PHPUnit";
    $user->private            = true;
    $user->realName           = "PHPUnit PHPUnit";
    $user->sex                = 10;
    $user->systemLanguageCode = "xx";
    $user->timeZoneId         = "PHPUnit/PHPUnit";
    $user->website            = "http://phpunit.net/";
    $user->commit();

    $user = new User(User::FROM_ID, 1);
    $this->assertEquals(date(InputDate::RFC3339, $_SERVER["REQUEST_TIME"]), $user->birthday);
    $this->assertEquals(1, $user->countryId);
    $this->assertEquals("PHPUnit", $user->profile);
    $this->assertTrue($user->private);
    $this->assertEquals("PHPUnit PHPUnit", $user->realName);
    $this->assertEquals(10, $user->sex);
    $this->assertEquals("xx", $user->systemLanguageCode);
    $this->assertEquals("PHPUnit/PHPUnit", $user->timeZoneId);
    $this->assertEquals("http://phpunit.net/", $user->website);
  }

  /**
   * @covers ::deactivate
   * @covers ::deleteImageOriginalAndStyles
   * @covers \MovLib\Data\Database::query
   * @covers \MovLib\Data\Session::getActiveSessions
   * @group Database
   * @group FileSystem
   * @group Uploads
   */
  public function testDeactivate() {
    $user = new User(User::FROM_ID, 1);
    $user->deactivate();

    $user = new User(User::FROM_ID, 1);
    $this->assertFalse(is_file(get_reflection_method($user, "getImagePath")->invokeArgs($user, [ User::IMAGE_STYLE_DEFAULT ])));
    $this->assertNull(get_reflection_property($user, "imageChanged")->getValue($user));
    $this->assertNull(get_reflection_property($user, "imageExtension")->getValue($user));
    $this->assertNull($user->birthday);
    $this->assertNull($user->countryId);
    $this->assertTrue($user->deactivated);
    $this->assertEmpty($user->profile);
    //$this->assertNull($user->facebook);
    //$this->assertNull($user->googlePlus);
    $this->assertFalse($user->private);
    $this->assertNull($user->realName);
    $this->assertEquals(0, $user->sex);
    $this->assertEquals(ini_get("date.timezone"), $user->timeZoneId);
    //$this->assertNull($user->twitter);
    $this->assertNull($user->website);

    return $user;
  }

  /**
   * @covers ::deleteImageOriginalAndStyles
   * @covers \MovLib\Data\Image\AbstractImage::getImagePath
   * @group FileSystem
   * @group Uploads
   */
  public function testDeleteImageOriginalAndStyles() {
    $user             = new User(User::FROM_ID, 1);
    $rmGetImagePath   = get_reflection_method($user, "getImagePath");
    $rpImageChanged   = get_reflection_property($user, "imageChanged");
    $rpImageExtension = get_reflection_property($user, "imageExtension");

    $this->assertTrue(is_file($rmGetImagePath->invokeArgs($user, [ User::IMAGE_STYLE_DEFAULT ])));
    $this->assertTrue(is_file($rmGetImagePath->invokeArgs($user, [ User::IMAGE_STYLE_SMALL ])));
    $this->assertTrue($user->imageExists);
    $this->assertNotEmpty($rpImageChanged->getValue($user));
    $this->assertNotEmpty($rpImageExtension->getValue($user));

    get_reflection_method($user, "deleteImageOriginalAndStyles")->invoke($user);
    $this->assertFalse(is_file($rmGetImagePath->invokeArgs($user, [ User::IMAGE_STYLE_DEFAULT ])));
    $this->assertFalse(is_file($rmGetImagePath->invokeArgs($user, [ User::IMAGE_STYLE_SMALL ])));
    $this->assertFalse($user->imageExists);
    $this->assertNull($rpImageChanged->getValue($user));
    $this->assertNull($rpImageExtension->getValue($user));
  }

  /**
   * @covers ::moveUploadedImage
   * @covers \MovLib\Data\Image\AbstractImage::convert
   * @covers \MovLib\Data\Image\AbstractImage::getImagePath
   * @group Database
   * @group FileSystem
   * @group Uploads
   */
  public function testMoveUploadedImage() {
    $user           = new User(User::FROM_ID, 1);
    $rmGetImagePath = get_reflection_method($user, "getImagePath");
    $source         = tempnam(sys_get_temp_dir(), "phpunit");
    copy("{$_SERVER["DOCUMENT_ROOT"]}/db/seeds/uploads/user/fleshgrinder.2.jpg", $source);
    get_reflection_method($user, "deleteImageOriginalAndStyles")->invoke($user);
    $user->moveUploadedImage($source, 220, 220, "jpg");
    $this->assertFalse(is_file($source));
    $this->assertTrue(is_file($rmGetImagePath->invokeArgs($user, [ User::IMAGE_STYLE_DEFAULT ])));
    $this->assertTrue(is_file($rmGetImagePath->invokeArgs($user, [ User::IMAGE_STYLE_SMALL ])));
    $this->assertTrue($user->imageExists);
    $this->assertEquals($_SERVER["REQUEST_TIME"], get_reflection_property($user, "imageChanged")->getValue($user));
    $this->assertEquals("jpg", get_reflection_property($user, "imageExtension")->getValue($user));
  }

  /**
   * @covers ::getImageStyleAttributes
   * @dataProvider dataProviderTestGetImageStyleAttributes
   * @group Presentation
   */
  public function testGetImageStyleAttributes($style, $dataProviderAttributes) {
    $user          = new User(User::FROM_ID, 1);
    $regExImageURL = preg_quote(get_reflection_method($user, "getImageURL")->invokeArgs($user, [ $style ]));
    $span          = get_reflection_property($user, "span")->getValue($user);
    $attributes    = $user->getImageStyleAttributes($style, $dataProviderAttributes);
    $this->assertEquals($span[$style], $attributes["height"]);
    $this->assertEquals($span[$style], $attributes["width"]);
    $this->assertRegExp("#^{$regExImageURL}\?c=[0-9]+$#", $attributes["src"]);
    $this->assertArrayHasKey("height", $dataProviderAttributes);
    $this->assertEquals($attributes["height"], $dataProviderAttributes["height"]);
    $this->assertArrayHasKey("width", $dataProviderAttributes);
    $this->assertEquals($attributes["width"], $dataProviderAttributes["width"]);
    $this->assertArrayHasKey("src", $dataProviderAttributes);
    $this->assertEquals($attributes["src"], $dataProviderAttributes["src"]);
  }

  /**
   * @covers ::getRandomPassword
   * @group Presentation
   */
  public function testGetRandomPassword() {
    $this->assertRegExp("/.{20}/", User::getRandomPassword());
  }

  /**
   * @covers ::prepareRegistration
   * @covers \MovLib\Data\Database::tmpSet
   * @covers \MovLib\Data\Database::tmpGetAndDelete
   * @covers \MovDev\Database::tmpGetAndDelete
   * @group Database
   */
  public function testPrepareRegistration() {
    $user        = new User();
    $user->name  = "PHPUnit";
    $user->email = "phpunit@movlib.org";
    $key = $user->prepareRegistration();
    $db          = new Database();
    $this->assertEquals([ "name" => "PHPUnit", "email" => "phpunit@movlib.org" ], $db->tmpGetAndDelete($key));
    $this->assertNull($db->tmpGetAndDelete($key));
  }

  /**
   * @covers ::reactivate
   * @covers \MovLib\Data\Database::selectAssoc
   * @covers \MovDev\Database::selectAssoc
   * @depends testDeactivate
   * @group Database
   */
  public function testReactivate($user) {
    $user->reactivate();
    $this->assertFalse($user->deactivated);
    $this->assertFalse((bool) (new Database())->selectAssoc("SELECT `deactivated` FROM `users` WHERE `user_id` = ?", "d", [ 1 ])["deactivated"]);
  }

  /**
   * @covers ::register
   * @covers \MovLib\Data\Database::query
   * @covers \MovLib\Data\Database::selectAssoc
   * @covers \MovDev\Database::selectAssoc
   * @group Database
   */
  public function testRegister() {
    global $i18n;
    $user = new User();

    $user->register("PHPUnit", "phpunit@movlib.org", "phpunitPassword");
    $this->assertEquals("PHPUnit", $user->name);
    $this->assertEquals("phpunit@movlib.org", $user->email);
    $this->assertEquals(get_reflection_property($user, "insertId")->getValue($user), $user->id);

    $userData = (new Database())->selectAssoc("SELECT * FROM `users` WHERE `user_id` = ?", "d", [ $user->id ]);
    $this->assertEmpty($userData["dyn_profile"]);
    $this->assertEquals("PHPUnit", $userData["name"]);
    $this->assertEquals("phpunit", $userData["avatar_name"]);
    $this->assertEquals("phpunit@movlib.org", $userData["email"]);
    $this->assertEquals($i18n->languageCode, $userData["system_language_code"]);
    $this->assertRegExp('/^\$[0-9][a-z]\$[0-9]{2}\$.*$/', $userData["password"]);
  }

  /**
   * @covers ::updateEmail
   * @covers \MovLib\Data\Database::query
   * @covers \MovLib\Data\Database::selectAssoc
   * @covers \MovDev\Database::selectAssoc
   * @group Database
   */
  public function testUpdateEmail() {
    $user = new User(User::FROM_ID, 1);
    $this->assertEquals("richard@fussenegger.info", $user->email);
    $user->updateEmail("phpunit@movlib.org");
    $this->assertEquals("phpunit@movlib.org", $user->email);
    $this->assertEquals("phpunit@movlib.org", (new Database())->selectAssoc("SELECT `email` FROM `users` WHERE `user_id` = ?", "d", [ 1 ])["email"]);
  }

  /**
   * @covers ::updatePassword
   * @covers \MovLib\Data\Database::query
   * @covers \MovLib\Data\Database::selectAssoc
   * @covers \MovDev\Database::selectAssoc
   * @covers \MovLib\Data\Session::authenticate
   * @group Database
   */
  public function testUpdatePassword() {
    $session = new \MovLib\Data\Session();
    $session->authenticate("richard@fussenegger.info", "test1234");
    $oldHash = $this->_testUpdatePassword();
    $user = new User(User::FROM_ID, 1);
    $user->updatePassword("phpunitPassword");
    $session->authenticate("richard@fussenegger.info", "phpunitPassword");
    $newHash = $this->_testUpdatePassword();
    $this->assertNotEquals($oldHash, $newHash);
  }

  private function _testUpdatePassword() {
    return (new Database())->selectAssoc("SELECT `password` FROM `users` WHERE `user_id` = ?", "d", [ 1 ])["password"];
  }

  /**
   * @covers ::validateToken
   * @covers ::prepareRegistration
   * @covers \MovLib\Data\Database::tmpSet
   * @covers \MovLib\Data\Database::tmpGetAndDelete
   * @group Database
   */
  public function testValidateToken() {
    $user        = new User();
    $user->name  = "PHPUnit";
    $user->email = "phpunit@movlib.org";
    $_GET["token"] = $user->prepareRegistration();
    $data = $user->validateToken($errors);
    $this->assertNull($errors);
    $this->assertEquals([ "name" => "PHPUnit", "email" => "phpunit@movlib.org" ], $data);
    return $_GET["token"];
  }

  /**
   * @covers ::validateToken
   * @covers ::prepareRegistration
   * @covers \MovLib\Data\Database::tmpSet
   * @group Database
   */
  public function testValidateTokenNotSet() {
    $user = new User();
    $this->assertNull($user->validateToken($errors));
    $this->assertNotEmpty($errors);
    $this->assertArrayHasKey(0, $errors);
    $this->assertContains("invalid", $errors[0]);
  }

  /**
   * @covers ::validateToken
   * @covers ::prepareRegistration
   * @covers \MovLib\Data\Database::tmpSet
   * @group Database
   */
  public function testValidateTokenEmpty() {
    $_GET["token"] = "";
    $this->testValidateTokenNotSet();
  }

  /**
   * @covers ::validateToken
   * @covers ::prepareRegistration
   * @covers \MovLib\Data\Database::tmpSet
   * @group Database
   */
  public function testValidateTokenToShort() {
    $_GET["token"] = "phpunitToken";
    $this->testValidateTokenNotSet();
  }

  /**
   * @covers ::validateToken
   * @covers ::prepareRegistration
   * @covers \MovLib\Data\Database::tmpSet
   * @covers \MovLib\Data\Database::tmpGetAndDelete
   * @depends testValidateToken
   * @group Database
   */
  public function testValidateTokenExpired($token) {
    $_GET["token"] = $token;
    $user          = new User();
    $this->assertNull($user->validateToken($errors));
    $this->assertNotEmpty($errors);
    $this->assertArrayHasKey(0, $errors);
    $this->assertContains("expired", $errors[0]);
  }

}
