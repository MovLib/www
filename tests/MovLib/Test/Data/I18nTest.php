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

use \DateTimeZone;
use \Exception;
use \IntlDateFormatter;
use \IntlException;
use \Locale;
use \MovDev\Database;
use \MovLib\Data\I18n;
use \MovLib\Exception\DatabaseException;

/**
 * @coversDefaultClass \MovLib\Data\I18n
 * @group Database
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright Â© 2013â€“present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class I18nTest extends \PHPUnit_Framework_TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The I18n object under test.
   *
   * @var \MovLib\Data\I18n
   */
  public $i18n;


  // ------------------------------------------------------------------------------------------------------------------- Magic methods


  /**
   * @inheritdoc
   */
  public function setUp() {
    $this->i18n = new I18n("en_US");
  }

  /**
   * @inheritdoc
   */
  public static function tearDownAfterClass() {
    $db = new Database();
    foreach ([ "message", "route" ] as $context) {
      $db->query("DELETE FROM `{$context}s` WHERE `{$context}` LIKE '%PHPUnit%'");
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Data Providers


  /**
   * Data provider for testConstructAcceptLanguageHeader().
   *
   * @return array
   */
  public function dataProviderTestConstructAcceptLanguageHeader() {
    $defaultLocale = \Locale::getDefault();
    $defaultLanguageCode = "{$defaultLocale[0]}{$defaultLocale[1]}";
    $args = [
      [ null, $defaultLocale, $defaultLanguageCode ],
      [ "xx-XX", $defaultLocale, $defaultLanguageCode ]
    ];
    foreach ($GLOBALS["movlib"]["locales"] as $code => $locale) {
      $args[] = [ $locale, $locale, $code ];
      $args[] = [ $code, $locale, $code ];
    }
    return $args;
  }

  /**
   * Data provider for testConstructLanguageCode().
   * Please note that unsupported language codes will not be tested, since nginx already handles them.
   *
   * @return array
   */
  public function dataProviderTestConstructLanguageCode() {
    $defaultLocale = \Locale::getDefault();
    $defaultLanguageCode = "{$defaultLocale[0]}{$defaultLocale[1]}";
    $args = [
      [ null, $defaultLocale, $defaultLanguageCode ]
    ];
    foreach ($GLOBALS["movlib"]["locales"] as $code => $locale) {
      $args[] = [ $code, $locale, $code ];
    }
    return $args;
  }


  // ------------------------------------------------------------------------------------------------------------------- Constructor tests


  /**
   * @param string $acceptLanguage
   *   The Accept-Language HTTP header.
   * @param string $expectedLocale
   *   The expected locale.
   * @param string $expectedLanguageCode
   *   The expected language code.
   * @covers ::__construct
   * @dataProvider dataProviderTestConstructAcceptLanguageHeader
   */
  public function testConstructAcceptLanguageHeader($acceptLanguage, $expectedLocale, $expectedLanguageCode) {
    // Unset the language code offset to ensure the Accept-Language header is used for determining the locale.
    unset($_SERVER["LANGUAGE_CODE"]);

    $_SERVER["HTTP_ACCEPT_LANGUAGE"] = $acceptLanguage;
    $i18n = new I18n();
    $this->assertEquals($expectedLocale, $i18n->locale);
    $this->assertEquals($expectedLanguageCode, $i18n->languageCode);
  }

  /**
   * @param string $languageCode
   *   The language code.
   * @param string $expectedLocale
   *   The expected locale.
   * @param string $expectedLanguageCode
   *   The expected language code.
   * @covers ::__construct
   * @dataProvider dataProviderTestConstructLanguageCode
   */
  public function testConstructLanguageCode($languageCode, $expectedLocale, $expectedLanguageCode) {
    // Set HTTP_ACCEPT_HEADER to "xx_XX" to verify the weight order of locale retrievals.
    $_SERVER["HTTP_ACCEPT_LANGUAGE"] = "xx_XX";

    $_SERVER["LANGUAGE_CODE"] = $languageCode;
    $i18n = new I18n();
    $this->assertEquals($expectedLocale, $i18n->locale);
    $this->assertEquals($expectedLanguageCode, $i18n->languageCode);
  }

  /**
   * Test the constuctor with supplied locale.
   *
   * @covers ::__construct
   */
  public function testConstructLocale() {
    $i18n = new I18n("xx_XX");
    $this->assertEquals("xx_XX", $i18n->locale);
    $this->assertEquals("xx", $i18n->languageCode);
  }


  // ------------------------------------------------------------------------------------------------------------------- Method tests


  /**
   * @covers ::formatDate
   */
  public function testFormatDate() {
    $timestamp = time();

    // No timezone supplied.
    $formatter = new IntlDateFormatter($this->i18n->locale, IntlDateFormatter::LONG, IntlDateFormatter::LONG, new DateTimeZone(ini_get("date.timezone")));
    $this->assertEquals($formatter->format($timestamp), $this->i18n->formatDate($timestamp));

    // Valid timezone supplied.
    $formatter = new IntlDateFormatter($this->i18n->locale, IntlDateFormatter::LONG, IntlDateFormatter::LONG, new DateTimeZone("Europe/Vienna"));
    $this->assertEquals($formatter->format($timestamp), $this->i18n->formatDate($timestamp, "Europe/Vienna"));

    // Invalid timezone supplied.
    try {
      $this->i18n->formatDate($timestamp, "PHPUnit");
      $this->assertTrue(false, "Invalid timezone test failed!");
    } catch (Exception $e) {
      // Do nothing, test worked fine.
    }
  }

  /**
   * @covers ::formatMessage
   */
  public function testFormatMessage() {
    // Insert test translations.
    $args = [ "test", 42 ];
    $pattern = "PHPUnit {0} PHPUnit {1}";
    $patternFormatted = "PHPUnit test PHPUnit 42";
    $patternGerman = "{$pattern} Deutsch";
    $patternGermanFormatted = "{$patternFormatted} Deutsch";
    $patternTestLanguage = "{$pattern} XX";
    $patternTestLanguageFormatted = "{$patternFormatted} XX";
    $db = new Database();
    foreach ([ "message", "route" ] as $context) {
      $db->query("INSERT INTO `{$context}s` (`{$context}`, `dyn_translations`) VALUES ('{$pattern}', COLUMN_CREATE('de', '{$patternGerman}', 'xx', '{$patternTestLanguage}'))");
    }
    // Set language code to German.
    $this->i18n->languageCode = "de";

    // No arguments.
    $msg = $this->i18n->formatMessage("message", $pattern, null, [ "language_code" => "xx" ]);
    $this->assertEquals($patternTestLanguage, $msg);
    $msg = $this->i18n->formatMessage("message", $pattern, null, [ "language_code" => $this->i18n->defaultLanguageCode ]);
    $this->assertEquals($pattern, $msg);
    $msg = $msg = $this->i18n->formatMessage("message", $pattern, null);
    $this->assertEquals($patternGerman, $msg);

    // With arguments.
    $msg = $this->i18n->formatMessage("message", $pattern, $args, [ "language_code" => "xx" ]);
    $this->assertEquals($patternTestLanguageFormatted, $msg);
    $msg = $this->i18n->formatMessage("message", $pattern, $args, [ "language_code" => $this->i18n->defaultLanguageCode ]);
    $this->assertEquals($patternFormatted, $msg);
    $msg = $msg = $this->i18n->formatMessage("message", $pattern, $args);
    $this->assertEquals($patternGermanFormatted, $msg);

    // Non-existent pattern.
    $patternNonExistent = "PHPUnit non-existent";
    $options = [ "language_code" => "xx" ];
    $msg = $this->i18n->formatMessage("message", $patternNonExistent, null, $options);
    $this->assertEquals($patternNonExistent, $msg);
    $stack = get_reflection_property("\MovLib\Data\Delayed\MethodCalls", "stack")->getValue(null);
    $c = count($stack);
    for ($i = 0; $i < $c; ++$i) {
      if ($stack[$i][0][0] instanceof I18n && $stack[$i][0][1] == "insertMessage" ) {
        $this->assertEquals($patternNonExistent, $stack[$i][1][0]);
        $this->assertEquals($options, $stack[$i][1][1]);
        $stack = true;
      }
    }
    $this->assertTrue($stack, "insertMessage() not found on DelayedMethodCalls stack!");

    // Wrong args format.
    try {
      $this->i18n->formatMessage("message", $pattern, "wrong args format");
      $this->assertTrue(false, "Wrong args format test failed!");
    }
    catch(IntlException $e) {
      // Do nothing, the test worked fine.
    }

    // Wrong context.
    try {
      $this->i18n->formatMessage("PHPUnit", $pattern, null);
      $this->assertTrue(false, "Wrong context test failed!");
    }
    catch (DatabaseException $e) {
      // Do nothing, the test worked fine.
    }
  }

  /**
   * @covers ::r
   * @covers ::t
   * @depends testFormatMessage
   */
  public function testRAndT() {
    $pattern = "PHPUnit {0} PHPUnit {1}";
    $args = [ "test", 42 ];
    $options = [ "language_code" => "xx" ];

    $this->assertEquals($this->i18n->formatMessage("route", $pattern, $args, $options), $this->i18n->r($pattern, $args, $options));
    $this->assertEquals($this->i18n->formatMessage("message", $pattern, $args, $options), $this->i18n->t($pattern, $args, $options));

    $this->assertEquals($this->i18n->formatMessage("route", $pattern, $args), $this->i18n->r($pattern, $args));
    $this->assertEquals($this->i18n->formatMessage("message", $pattern, $args), $this->i18n->t($pattern, $args));

    $this->assertEquals($this->i18n->formatMessage("route", $pattern, null, $options), $this->i18n->r($pattern, null, $options));
    $this->assertEquals($this->i18n->formatMessage("message", $pattern, null, $options), $this->i18n->t($pattern, null, $options));

    $this->assertEquals($this->i18n->formatMessage("route", $pattern, null), $this->i18n->r($pattern, null));
    $this->assertEquals($this->i18n->formatMessage("message", $pattern, null), $this->i18n->t($pattern, null));
  }

  /**
   * @covers ::getCollator
   * @covers ::getCountries
   */
  public function testGetCountries() {
    $countryId = 26;
    $countryCode = "BL";
    $countryName = "Saint Barthélemy";
    $countryNameGerman = "St. Barthélemy";

    // English locale.

    // Key ID.
    $countries = $this->i18n->getCountries();
    $this->assertEquals($countryId, $countries[$countryId]["id"]);
    $this->assertEquals($countryCode, $countries[$countryId]["code"]);
    $this->assertEquals($countryName, $countries[$countryId]["name"]);
    // Key code.
    $countries = $this->i18n->getCountries(I18n::KEY_CODE);
    $this->assertEquals($countryId, $countries[$countryCode]["id"]);
    $this->assertEquals($countryCode, $countries[$countryCode]["code"]);
    $this->assertEquals($countryName, $countries[$countryCode]["name"]);
    // Key name.
    $countries = $this->i18n->getCountries(I18n::KEY_NAME);
    $this->assertEquals($countryId, $countries[$countryName]["id"]);
    $this->assertEquals($countryCode, $countries[$countryName]["code"]);
    $this->assertEquals($countryName, $countries[$countryName]["name"]);

    // German locale.
    $this->i18n = new I18n("de_AT");

    // Key ID.
    $countries = $this->i18n->getCountries();
    $this->assertEquals($countryId, $countries[$countryId]["id"]);
    $this->assertEquals($countryCode, $countries[$countryId]["code"]);
    $this->assertEquals($countryNameGerman, $countries[$countryId]["name"]);
    // Key code.
    $countries = $this->i18n->getCountries(I18n::KEY_CODE);
    $this->assertEquals($countryId, $countries[$countryCode]["id"]);
    $this->assertEquals($countryCode, $countries[$countryCode]["code"]);
    $this->assertEquals($countryNameGerman, $countries[$countryCode]["name"]);
    // Key name.
    $countries = $this->i18n->getCountries(I18n::KEY_NAME);
    $this->assertEquals($countryId, $countries[$countryNameGerman]["id"]);
    $this->assertEquals($countryCode, $countries[$countryNameGerman]["code"]);
    $this->assertEquals($countryNameGerman, $countries[$countryNameGerman]["name"]);
  }

  /**
   * @covers ::getCollator
   * @covers ::getLanguageId
   * @covers ::getLanguages
   */
  public function testGetLanguages() {
    $languageId = 110;
    $languageCode = "nb";
    $languageName = "Norwegian Bokmål";
    $languageNameGerman = "Norwegisch Bokmål";

    // English locale.
    $this->assertEquals(41, $this->i18n->getLanguageId());

    // Key ID.
    $languages = $this->i18n->getLanguages();
    $this->assertEquals($languageId, $languages[$languageId]["id"]);
    $this->assertEquals($languageCode, $languages[$languageId]["code"]);
    $this->assertEquals($languageName, $languages[$languageId]["name"]);
    // Key code.
    $languages = $this->i18n->getLanguages(I18n::KEY_CODE);
    $this->assertEquals($languageId, $languages[$languageCode]["id"]);
    $this->assertEquals($languageCode, $languages[$languageCode]["code"]);
    $this->assertEquals($languageName, $languages[$languageCode]["name"]);
    // Key name.
    $languages = $this->i18n->getLanguages(I18n::KEY_NAME);
    $this->assertEquals($languageId, $languages[$languageName]["id"]);
    $this->assertEquals($languageCode, $languages[$languageName]["code"]);
    $this->assertEquals($languageName, $languages[$languageName]["name"]);

    // German locale.
    $this->i18n = new I18n("de_AT");
    $this->assertEquals(52, $this->i18n->getLanguageId());

    // Key ID.
    $languages = $this->i18n->getLanguages();
    $this->assertEquals($languageId, $languages[$languageId]["id"]);
    $this->assertEquals($languageCode, $languages[$languageId]["code"]);
    $this->assertEquals($languageNameGerman, $languages[$languageId]["name"]);
    // Key code.
    $languages = $this->i18n->getLanguages(I18n::KEY_CODE);
    $this->assertEquals($languageId, $languages[$languageCode]["id"]);
    $this->assertEquals($languageCode, $languages[$languageCode]["code"]);
    $this->assertEquals($languageNameGerman, $languages[$languageCode]["name"]);
    // Key name.
    $languages = $this->i18n->getLanguages(I18n::KEY_NAME);
    $this->assertEquals($languageId, $languages[$languageNameGerman]["id"]);
    $this->assertEquals($languageCode, $languages[$languageNameGerman]["code"]);
    $this->assertEquals($languageNameGerman, $languages[$languageNameGerman]["name"]);
  }

  /**
   * @covers ::getSystemLanguages
   */
  public function testGetSystemLanguages() {
    // English locale.
    $testLocale = "en_US";
    $systemLanguages = $this->i18n->getSystemLanguages();
    foreach($GLOBALS["movlib"]["locales"] as $languageCode => $locale) {
      $this->assertEquals(Locale::getDisplayLanguage($languageCode, $testLocale), $systemLanguages[$languageCode]);
    }
    // German locale.
    $testLocale = "de_AT";
    $this->i18n->locale = $testLocale;
    $systemLanguages = $this->i18n->getSystemLanguages();
    foreach($GLOBALS["movlib"]["locales"] as $languageCode => $locale) {
      $this->assertEquals(Locale::getDisplayLanguage($languageCode, $testLocale), $systemLanguages[$languageCode]);
    }
  }

  /**
   * @covers ::getSystemLanguageLinks
   * @depends testGetSystemLanguages
   */
  public function testGetSystemLanguageLinks() {
    $_SERVER["PATH_INFO"] = "";

    // English language code.
    $this->i18n->languageCode = "en";
    $links = $this->i18n->getSystemLanguageLinks();
    $c = count($links);
    for ($i = 0; $i < $c; ++$i) {
      if($links[$i][0] == "#") {
        $this->assertContains("English", $links[$i][1]);
        $this->assertArrayHasKey("class", $links[$i][2]);
        $this->assertContains("active", $links[$i][2]["class"]);
      }
      else {
        $this->assertContains($GLOBALS["movlib"]["default_domain"], $links[$i][0]);
      }
    }

    // German language code.
    $this->i18n->languageCode = "de";
    $links = $this->i18n->getSystemLanguageLinks();
    $c = count($links);
    for ($i = 0; $i < $c; ++$i) {
      if($links[$i][0] == "#") {
        $this->assertContains("Deutsch", $links[$i][1]);
        $this->assertArrayHasKey("class", $links[$i][2]);
        $this->assertContains("active", $links[$i][2]["class"]);
      }
      else {
        $this->assertContains($GLOBALS["movlib"]["default_domain"], $links[$i][0]);
      }
    }
  }

  /**
   * @covers ::getTimezones
   */
  public function testGetTimezones() {
    $timezones = $this->i18n->getTimeZones();
    $expectedTimezones = DateTimeZone::listIdentifiers();
    $c = count($expectedTimezones);
    for ($i = 0; $i < $c; ++$i) {
      $this->assertArrayHasKey($expectedTimezones[$i], $timezones);
      $this->assertEquals(strtr($expectedTimezones[$i], "_", " "), $timezones[$expectedTimezones[$i]]);
    }
  }

  /**
   * @covers ::insertMessage
   */
  public function testInsertMessage() {
    $db = new Database();

    // With comment.
    $message = "PHPUnit test message";
    $comment = "PHPUnit comment";
    $this->i18n->insertMessage($message, [ "comment" => $comment ]);
    $result = $db->select("SELECT `message`, `comment` FROM `messages` WHERE `message` = ? LIMIT 1", "s", [ $message ]);
    $this->assertNotEmpty($result);
    $this->assertEquals($message, $result[0]["message"]);
    $this->assertEquals($comment, $result[0]["comment"]);

    // Without comment.
    $message .= " without comment";
    $this->i18n->insertMessage($message);
    $result = $db->select("SELECT `message`, `comment` FROM `messages` WHERE `message` = ? LIMIT 1", "s", [ $message ]);
    $this->assertNotEmpty($result);
    $this->assertEquals($message, $result[0]["message"]);
    $this->assertEmpty($result[0]["comment"]);
  }

  /**
   * @covers ::insertRoute
   */
  public function testInsertRoute() {
    $db = new Database();

    // With comment.
    $route = "PHPUnit test route";
    $this->i18n->insertRoute($route);
    $result = $db->select("SELECT `route` FROM `routes` WHERE `route` = ? LIMIT 1", "s", [ $route ]);
    $this->assertNotEmpty($result);
    $this->assertEquals($route, $result[0]["route"]);
  }

  /**
   * @covers ::insertOrUpdateTranslation
   * @depends testFormatMessage
   */
  public function testInsertOrUpdateTranslation() {
    $db = new Database();
    $pattern = "PHPUnit {0} PHPUnit {1}";
    $languageCode = "xx";
    $patternTranslated = "{$pattern} insertOrUpdate translated";

    // Message context.
    $id = $db->select("SELECT `message_id` FROM `messages` WHERE `message` = ? LIMIT 1", "s", [ $pattern ])[0]["message_id"];
    $this->i18n->insertOrUpdateTranslation("message", $id, $languageCode, $patternTranslated);
    $result = $db->select("SELECT COLUMN_GET(`dyn_translations`, ? AS CHAR(255)) AS `translation` FROM `messages` WHERE `message_id` = ? LIMIT 1", "si", [ $languageCode, $id ]);
    $this->assertNotEmpty($result);
    $this->assertEquals($result[0]["translation"], $patternTranslated);

    // Route context.
    $id = $db->select("SELECT `route_id` FROM `routes` WHERE `route` = ? LIMIT 1", "s", [ $pattern ])[0]["route_id"];
    $this->i18n->insertOrUpdateTranslation("route", $id, $languageCode, $patternTranslated);
    $result = $db->select("SELECT COLUMN_GET(`dyn_translations`, ? AS CHAR(255)) AS `translation` FROM `routes` WHERE `route_id` = ? LIMIT 1", "si", [ $languageCode, $id ]);
    $this->assertNotEmpty($result);
    $this->assertEquals($result[0]["translation"], $patternTranslated);

    // Wrong context.
    try {
      $this->i18n->insertOrUpdateTranslation("PHPUnit", 1, $languageCode, $patternTranslated);
      $this->assertTrue(false, "Wrong context test failed!");
    } catch (DatabaseException $e) {
      // Do nothing, the test worked fine.
    }
  }

}
