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
namespace MovLib\Data;

use \DateTimeZone;
use \IntlDateFormatter;
use \Locale;
use \MovLib\Data\Collator;
use \MovLib\Data\SystemLanguages;
use \MovLib\Data\I18n;

/**
 * @coversDefaultClass \MovLib\Data\I18n
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013-present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class I18nTest extends \MovLib\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /** @var \MovLib\Data\I18n */
  protected $i18n;


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  protected function setUp() {
    $this->i18n = new I18n(Locale::getDefault());
  }

  public static function tearDownAfterClass() {
    $queries = "";
    foreach ([ "message", "route" ] as $context) {
      $queries .= "DELETE FROM `{$context}s` WHERE `{$context}` LIKE '%PHPUnit%';";
    }
    (new \MovLib\Tool\Database())->queries($queries);
  }


  // ------------------------------------------------------------------------------------------------------------------- Data Providers


  public static function dataProviderTestConstructAcceptLanguageHeaderValid() {
    $args = [];
    foreach (new SystemLanguages() as $locale => $systemLanguage) {
      $args[] = [ $locale, $locale, $systemLanguage->languageCode ];
      $args[] = [ $systemLanguage->languageCode, $locale, $systemLanguage->languageCode ];
    }
    return $args;
  }

  public static function dataProviderTestConstructLanguageCodeValid() {
    $args = [];
    foreach (new SystemLanguages() as $locale => $systemLanguage) {
      $args[] = [ $systemLanguage->languageCode, $locale, $systemLanguage->languageCode ];
    }
    return $args;
  }


  // ------------------------------------------------------------------------------------------------------------------- Tests


  /**
   * @covers ::__construct
   */
  public function testConstructInvalidHTTPAcceptLanguageCode() {
    unset($_SERVER["LANGUAGE_CODE"]);
    $_SERVER["HTTP_ACCEPT_LANGUAGE"] = "xx-XX";
    $defaultLocale                   = Locale::getDefault();
    $defaultLanguageCode             = "{$defaultLocale[0]}{$defaultLocale[1]}";
    $i18n                            = new I18n();
    $this->assertEquals($defaultLocale, $i18n->locale);
    $this->assertEquals($defaultLanguageCode, $i18n->languageCode);
  }

  /**
   * @covers ::__construct
   */
  public function testConstructNoLanguageCodeNoHTTPAcceptLanguageCode() {
    unset($_SERVER["LANGUAGE_CODE"]);
    $_SERVER["HTTP_ACCEPT_LANGUAGE"] = null;
    $defaultLocale                   = Locale::getDefault();
    $defaultLanguageCode             = "{$defaultLocale[0]}{$defaultLocale[1]}";
    $i18n                            = new I18n();
    $this->assertEquals($defaultLocale, $i18n->locale);
    $this->assertEquals($defaultLanguageCode, $i18n->languageCode);
  }

  /**
   * @param string $acceptLanguage
   *   The Accept-Language HTTP header.
   * @param string $expectedLocale
   *   The expected locale.
   * @param string $expectedLanguageCode
   *   The expected language code.
   * @covers ::__construct
   * @dataProvider dataProviderTestConstructAcceptLanguageHeaderValid
   */
  public function testConstructAcceptLanguageHeaderValid($acceptLanguage, $expectedLocale, $expectedLanguageCode) {
    // Unset the language code offset to ensure the Accept-Language header is used for determining the locale.
    $serverLanguageCode = $_SERVER["LANGUAGE_CODE"];
    unset($_SERVER["LANGUAGE_CODE"]);

    $_SERVER["HTTP_ACCEPT_LANGUAGE"] = $acceptLanguage;
    $i18n                            = new I18n();
    $this->assertEquals($expectedLocale, $i18n->locale);
    $this->assertEquals($expectedLanguageCode, $i18n->languageCode);

    $_SERVER["LANGUAGE_CODE"] = $serverLanguageCode;
  }

  /**
   * Please note that unsupported language codes will not be tested, since nginx already handles them.
   */
  public function testConstructLanguageCodeInvalid() {
    // Set HTTP_ACCEPT_HEADER to "xx_XX" to verify the weight order of locale retrievals.
    $acceptLanguage                  = isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]) ? $_SERVER["HTTP_ACCEPT_LANGUAGE"] : null;
    $_SERVER["HTTP_ACCEPT_LANGUAGE"] = "xx_XX";

    $defaultLocale       = Locale::getDefault();
    $defaultLanguageCode = "{$defaultLocale[0]}{$defaultLocale[1]}";

    $i18n = new I18n();
    $this->assertEquals($defaultLocale, $i18n->locale);
    $this->assertEquals($defaultLanguageCode, $i18n->languageCode);

    $_SERVER["HTTP_ACCEPT_LANGUAGE"] = $acceptLanguage;
  }

  /**
   * @param string $languageCode
   *   The language code.
   * @param string $expectedLocale
   *   The expected locale.
   * @param string $expectedLanguageCode
   *   The expected language code.
   * @covers ::__construct
   * @dataProvider dataProviderTestConstructLanguageCodeValid
   */
  public function testConstructLanguageCodeValid($languageCode, $expectedLocale, $expectedLanguageCode) {
    // Set HTTP_ACCEPT_HEADER to "xx_XX" to verify the weight order of locale retrievals.
    $acceptLanguage                  = isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]) ? $_SERVER["HTTP_ACCEPT_LANGUAGE"] : null;
    $_SERVER["HTTP_ACCEPT_LANGUAGE"] = "xx_XX";

    $_SERVER["LANGUAGE_CODE"] = $languageCode;
    $i18n                     = new I18n();
    $this->assertEquals($expectedLocale, $i18n->locale);
    $this->assertEquals($expectedLanguageCode, $i18n->languageCode);

    $_SERVER["HTTP_ACCEPT_LANGUAGE"] = $acceptLanguage;
  }

  /**
   * Test the constuctor with supplied locale.
   *
   * @covers ::__construct
   */
  public function testConstructLocale() {
    // Set the language code and HTTP_ACCEPT_HEADER in order to prove that the supplied locale will be used.
    $serverLanguageCode              = $_SERVER["LANGUAGE_CODE"];
    $_SERVER["LANGUAGE_CODE"]        = "en";
    $acceptLanguage                  = isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]) ? $_SERVER["HTTP_ACCEPT_LANGUAGE"] : null;
    $_SERVER["HTTP_ACCEPT_LANGUAGE"] = "en_US";

    $i18n = new I18n("xx_XX");
    $this->assertEquals("xx_XX", $i18n->locale);
    $this->assertEquals("xx", $i18n->languageCode);

    $_SERVER["LANGUAGE_CODE"]        = $serverLanguageCode;
    $_SERVER["HTTP_ACCEPT_LANGUAGE"] = $acceptLanguage;
  }

  /**
   * @covers ::formatDate
   * @expectedException \Exception
   */
  public function testFormatDateInvalid() {
    // Invalid timestamp values.
    $this->assertFalse($this->i18n->formatDate(null), "Formatting test null timestamp failed!");
    $this->assertFalse($this->i18n->formatDate("Invalid timestamp"), "Formatting test invalid timestamp failed!");
    $this->assertFalse($this->i18n->formatDate(-1), "Formatting test invalid timestamp failed!");
    // Invalid timezone (should throw Exception).
    $this->i18n->formatDate(time(), "PHPUnit");
  }

  /**
   * @covers ::formatDate
   */
  public function testFormatDateValid() {
    $timestamp = time();

    // No timezone supplied.
    $formatter = new IntlDateFormatter($this->i18n->locale, IntlDateFormatter::LONG, IntlDateFormatter::LONG, new DateTimeZone(ini_get("date.timezone")));
    $this->assertEquals($formatter->format($timestamp), $this->i18n->formatDate($timestamp));

    // Valid timezone supplied.
    $formatter = new IntlDateFormatter($this->i18n->locale, IntlDateFormatter::LONG, IntlDateFormatter::LONG, new DateTimeZone("Europe/Vienna"));
    $this->assertEquals($formatter->format($timestamp), $this->i18n->formatDate($timestamp, "Europe/Vienna"));
  }

  /**
   * @covers ::formatMessage
   * @expectedException \IntlException
   */
  public function testFormatMessageInvalidArgumentsFormat() {
    $this->i18n->formatMessage("message", "PHPUnit {0} PHPUnit {1}", "wrong args format");
  }

  /**
   * @covers ::formatMessage
   * @expectedException \MovLib\Exception\DatabaseException
   */
  public function testFormatMessageInvalidContext() {
    $this->i18n->languageCode = "xx";
    $this->i18n->formatMessage("PHPUnit", "PHPUnit {0} PHPUnit {1}", null);
  }

  /**
   * @covers ::formatMessage
   */
  public function testFormatMessageValid() {
    global $db;
    $args                         = [ "test", 42 ];
    $pattern                      = "PHPUnit {0} PHPUnit {1}";
    $patternFormatted             = "PHPUnit test PHPUnit 42";
    $patternGerman                = "{$pattern} Deutsch";
    $patternGermanFormatted       = "{$patternFormatted} Deutsch";
    $patternTestLanguage          = "{$pattern} XX";
    $patternTestLanguageFormatted = "{$patternFormatted} XX";
    foreach ([ "message", "route" ] as $context) {
      $db->query("INSERT INTO `{$context}s` (`{$context}`, `dyn_translations`) VALUES ('{$pattern}', COLUMN_CREATE('de', '{$patternGerman}', 'xx', '{$patternTestLanguage}'))");
    }
    // Set language code to German.
    $this->i18n->languageCode = "de";

    // No arguments.
    $this->assertEquals($patternTestLanguage, $this->i18n->formatMessage("message", $pattern, null, [ "language_code" => "xx" ]));
    $this->assertEquals($pattern, $this->i18n->formatMessage("message", $pattern, null, [ "language_code" => $this->i18n->defaultLanguageCode ]));
    $this->assertEquals($patternGerman, $this->i18n->formatMessage("message", $pattern, null));

    // With arguments.
    $this->assertEquals($patternTestLanguageFormatted, $this->i18n->formatMessage("message", $pattern, $args, [ "language_code" => "xx" ]));
    $this->assertEquals($patternFormatted, $this->i18n->formatMessage("message", $pattern, $args, [ "language_code" => $this->i18n->defaultLanguageCode ]));
    $this->assertEquals($patternGermanFormatted, $this->i18n->formatMessage("message", $pattern, $args));

    // Non-existent pattern.
    $patternNonExistent = "PHPUnit non-existent";
    $options            = [ "language_code" => "xx" ];
    $this->assertEquals($patternNonExistent, $this->i18n->formatMessage("message", $patternNonExistent, null, $options));

    $stack = $this->getStaticProperty("\\MovLib\\Data\\Delayed\\MethodCalls", "stack");
    $c     = count($stack);
    for ($i = 0; $i < $c; ++$i) {
      if ($stack[$i][0][0] instanceof I18n && $stack[$i][0][1] == "insertMessage") {
        $this->assertEquals($patternNonExistent, $stack[$i][1][0]);
        $this->assertEquals($options, $stack[$i][1][1]);
        $stack = true;
      }
    }
    $this->assertTrue($stack, "insertMessage() not found on DelayedMethodCalls stack!");
  }

  /**
   * @covers ::r
   * @covers ::t
   * @depends testFormatMessageValid
   */
  public function testRAndT() {
    $pattern = "PHPUnit {0} PHPUnit {1}";
    $args    = [ "test", 42 ];
    $options = [ "language_code" => "xx" ];

    // With args and options.
    $this->assertEquals($this->i18n->formatMessage("route", $pattern, $args, $options), $this->i18n->r($pattern, $args, $options));
    $this->assertEquals($this->i18n->formatMessage("message", $pattern, $args, $options), $this->i18n->t($pattern, $args, $options));

    // With args, without options.
    $this->assertEquals($this->i18n->formatMessage("route", $pattern, $args), $this->i18n->r($pattern, $args));
    $this->assertEquals($this->i18n->formatMessage("message", $pattern, $args), $this->i18n->t($pattern, $args));

    // Without args, with options.
    $this->assertEquals($this->i18n->formatMessage("route", $pattern, null, $options), $this->i18n->r($pattern, null, $options));
    $this->assertEquals($this->i18n->formatMessage("message", $pattern, null, $options), $this->i18n->t($pattern, null, $options));

    // Without args and options.
    $this->assertEquals($this->i18n->formatMessage("route", $pattern, null), $this->i18n->r($pattern, null));
    $this->assertEquals($this->i18n->formatMessage("message", $pattern, null), $this->i18n->t($pattern, null));
  }

  /**
   * @covers ::getCollator
   * @covers ::getCountries
   */
//  public function testGetCountries() {
//    $countryId = 26;
//    $countryCode = "BL";
//    $countryName = "Saint Barthélemy";
//    $countryNameGerman = "St. Barthélemy";
//
//    // English locale.
//
//    // Key ID.
//    $countries = $this->i18n->getCountries();
//    $this->assertEquals($countryId, $countries[$countryId]["id"]);
//    $this->assertEquals($countryCode, $countries[$countryId]["code"]);
//    $this->assertEquals($countryName, $countries[$countryId]["name"]);
//    // Key code.
//    $countries = $this->i18n->getCountries(I18n::KEY_CODE);
//    $this->assertEquals($countryId, $countries[$countryCode]["id"]);
//    $this->assertEquals($countryCode, $countries[$countryCode]["code"]);
//    $this->assertEquals($countryName, $countries[$countryCode]["name"]);
//    // Key name.
//    $countries = $this->i18n->getCountries(I18n::KEY_NAME);
//    $this->assertEquals($countryId, $countries[$countryName]["id"]);
//    $this->assertEquals($countryCode, $countries[$countryName]["code"]);
//    $this->assertEquals($countryName, $countries[$countryName]["name"]);
//
//    // German locale.
//    $this->i18n = new I18n("de_AT");
//    $db = new Database();
//    $result = $db->select("SELECT `country_id` AS `id`, `iso_alpha-2` AS `code`, COLUMN_GET(`dyn_translations`, 'de' AS CHAR(255)) AS `name` FROM `countries`");
//    $testCountries = [];
//    foreach ($result as $country) {
//      $testCountries["code"][$country["code"]] = $country;
//      $testCountries["name"][$country["name"]] = $country;
//    }
//    ksort($testCountries["code"]);
//    $collator = new Collator("de_AT");
//    $collator->ksort($testCountries["name"]);
//
//    // Key ID.
//    $countries = $this->i18n->getCountries();
//    $this->assertEquals($countryId, $countries[$countryId]["id"]);
//    $this->assertEquals($countryCode, $countries[$countryId]["code"]);
//    $this->assertEquals($countryNameGerman, $countries[$countryId]["name"]);
//    // Key code.
//    $countries = $this->i18n->getCountries(I18n::KEY_CODE);
//    $this->assertEquals($countryId, $countries[$countryCode]["id"]);
//    $this->assertEquals($countryCode, $countries[$countryCode]["code"]);
//    $this->assertEquals($countryNameGerman, $countries[$countryCode]["name"]);
//    // Key code sort order.
//    $this->assertEquals($testCountries["code"], $countries);
//    // Key name.
//    $countries = $this->i18n->getCountries(I18n::KEY_NAME);
//    $this->assertEquals($countryId, $countries[$countryNameGerman]["id"]);
//    $this->assertEquals($countryCode, $countries[$countryNameGerman]["code"]);
//    $this->assertEquals($countryNameGerman, $countries[$countryNameGerman]["name"]);
//    // Key name sort order.
//    $this->assertEquals($testCountries["name"], $countries);
//  }

  /**
   * @covers ::getCollator
   * @covers ::getLanguageId
   * @covers ::getLanguages
   */
//  public function testGetLanguages() {
//    $languageId = 110;
//    $languageCode = "nb";
//    $languageName = "Norwegian Bokmål";
//    $languageNameGerman = "Norwegisch Bokmål";
//
//    // English locale.
//    $this->assertEquals(41, $this->i18n->getLanguageId());
//
//    // Key ID.
//    $languages = $this->i18n->getLanguages();
//    $this->assertEquals($languageId, $languages[$languageId]["id"]);
//    $this->assertEquals($languageCode, $languages[$languageId]["code"]);
//    $this->assertEquals($languageName, $languages[$languageId]["name"]);
//    // Key code.
//    $languages = $this->i18n->getLanguages(I18n::KEY_CODE);
//    $this->assertEquals($languageId, $languages[$languageCode]["id"]);
//    $this->assertEquals($languageCode, $languages[$languageCode]["code"]);
//    $this->assertEquals($languageName, $languages[$languageCode]["name"]);
//    // Key name.
//    $languages = $this->i18n->getLanguages(I18n::KEY_NAME);
//    $this->assertEquals($languageId, $languages[$languageName]["id"]);
//    $this->assertEquals($languageCode, $languages[$languageName]["code"]);
//    $this->assertEquals($languageName, $languages[$languageName]["name"]);
//
//    // German locale.
//    $this->i18n = new I18n("de_AT");
//    $this->assertEquals(52, $this->i18n->getLanguageId());
//    $testLanguages = [];
//    $db = new Database();
//    $result = $db->select("SELECT `language_id` AS `id`, `iso_alpha-2` AS `code`, COLUMN_GET(`dyn_translations`, 'de' AS CHAR(255)) AS `name` FROM `languages`");
//    foreach ($result as $lang) {
//      $testLanguages["code"][$lang["code"]] = $lang;
//      $testLanguages["name"][$lang["name"]] = $lang;
//    }
//    ksort($testLanguages["code"]);
//    $collator = new Collator("de_AT");
//    $collator->ksort($testLanguages["name"]);
//
//    // Key ID.
//    $languages = $this->i18n->getLanguages();
//    $this->assertEquals($languageId, $languages[$languageId]["id"]);
//    $this->assertEquals($languageCode, $languages[$languageId]["code"]);
//    $this->assertEquals($languageNameGerman, $languages[$languageId]["name"]);
//    // Key code.
//    $languages = $this->i18n->getLanguages(I18n::KEY_CODE);
//    $this->assertEquals($languageId, $languages[$languageCode]["id"]);
//    $this->assertEquals($languageCode, $languages[$languageCode]["code"]);
//    $this->assertEquals($languageNameGerman, $languages[$languageCode]["name"]);
//    // Key code sort order.
//    $this->assertEquals($testLanguages["code"], $languages);
//    // Key name.
//    $languages = $this->i18n->getLanguages(I18n::KEY_NAME);
//    $this->assertEquals($languageId, $languages[$languageNameGerman]["id"]);
//    $this->assertEquals($languageCode, $languages[$languageNameGerman]["code"]);
//    $this->assertEquals($languageNameGerman, $languages[$languageNameGerman]["name"]);
//    // Key name sort order.
//    $this->assertEquals($testLanguages["name"], $languages);
//  }

  /**
   * @covers ::getSystemLanguages
   */
  public function testGetSystemLanguages() {
    // English locale.
    $testLocale         = "en_US";
    $this->i18n->locale = $testLocale;
    $systemLanguages    = $this->i18n->getSystemLanguages();
    foreach ($GLOBALS["movlib"]["locales"] as $languageCode => $locale) {
      $this->assertEquals(Locale::getDisplayLanguage($languageCode, $testLocale), $systemLanguages[$languageCode]);
    }
    // German locale.
    $testLocale         = "de_AT";
    $this->i18n->locale = $testLocale;
    $systemLanguages    = $this->i18n->getSystemLanguages();
    foreach ($GLOBALS["movlib"]["locales"] as $languageCode => $locale) {
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
    $links                    = $this->i18n->getSystemLanguageLinks();
    $c                        = count($links);
    for ($i = 0; $i < $c; ++$i) {
      if ($links[$i][0] == "#") {
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
    $links                    = $this->i18n->getSystemLanguageLinks();
    $c                        = count($links);
    for ($i = 0; $i < $c; ++$i) {
      if ($links[$i][0] == "#") {
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
  public function testGetTimeZones() {
    $locale              = \Locale::getDefault();
    $this->i18n->locale  = $locale;
    $timeZones           = $this->i18n->getTimeZones();
    $timeZoneIdentifiers = DateTimeZone::listIdentifiers();
    $expectedTimeZones   = [ ];
    $c                   = count($timeZoneIdentifiers);
    for ($i = 0; $i < $c; ++$i) {
      $expectedTimeZones[$timeZoneIdentifiers[$i]] = strtr($timeZoneIdentifiers[$i], "_", " ");
    }
    $collator = new Collator($locale);
    $collator->asort($expectedTimeZones, Collator::SORT_STRING);
    $this->assertEquals($expectedTimeZones, $timeZones);
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
    $this->assertNotNull($db
        ->query("SELECT `message`, `comment` FROM `messages` WHERE `message` = ? AND `comment` = ? LIMIT 1", "ss", [ $message, $comment ])
        ->get_result()->fetch_all()
    );

    // Without comment.
    $message .= " without comment";
    $this->i18n->insertMessage($message);
    $this->assertNotNull($db
        ->query("SELECT `message`, `comment` FROM `messages` WHERE `message` = ? AND `comment` IS NULL LIMIT 1", "s", [ $message ])
        ->get_result()->fetch_all()
    );
  }

  /**
   * @covers ::insertRoute
   */
  public function testInsertRoute() {
    $route = "PHPUnit test route";
    $this->i18n->insertRoute($route);
    $this->assertNotNull((new Database())->query("SELECT `route` FROM `routes` WHERE `route` = ? LIMIT 1", "s", [ $route ])->get_result()->fetch_all());
  }

  /**
   * @covers ::insertOrUpdateTranslation
   * @depends testFormatMessageValid
   * @expectedException \MovLib\Exception\DatabaseException
   */
  public function testInsertOrUpdateTranslationInvalidContext() {
    $this->i18n->insertOrUpdateTranslation("PHPUnit", 1, "xx", "PHPUnit {0} PHPUnit {1} insertOrUpdate translated");
  }

  /**
   * @covers ::insertOrUpdateTranslation
   * @depends testFormatMessageValid
   */
  public function testInsertOrUpdateTranslationValid() {
    $db                = new Database();
    $pattern           = "PHPUnit {0} PHPUnit {1}";
    $languageCode      = "xx";
    $patternTranslated = "{$pattern} insertOrUpdate translated";

    // Message context.
    $id = $db->query("SELECT `message_id` FROM `messages` WHERE `message` = ? LIMIT 1", "s", [ $pattern ])->get_result()->fetch_row()[0];
    $this->i18n->insertOrUpdateTranslation("message", $id, $languageCode, $patternTranslated);
    $this->assertEquals($patternTranslated, $db
        ->query("SELECT COLUMN_GET(`dyn_translations`, ? AS CHAR(255)) AS `translation` FROM `messages` WHERE `message_id` = ? LIMIT 1", "si", [ $languageCode, $id ])
        ->get_result()->fetch_row()[0]
    );

    // Route context.
    $id = $db->query("SELECT `route_id` FROM `routes` WHERE `route` = ? LIMIT 1", "s", [ $pattern ])->get_result()->fetch_row()[0];
    $this->i18n->insertOrUpdateTranslation("route", $id, $languageCode, $patternTranslated);
    $this->assertEquals($patternTranslated, $db
        ->query("SELECT COLUMN_GET(`dyn_translations`, ? AS CHAR(255)) AS `translation` FROM `routes` WHERE `route_id` = ? LIMIT 1", "si", [ $languageCode, $id ])
        ->get_result()->fetch_row()[0]
    );
  }

  /**
   * @covers ::getCollator
   * @todo Implement getCollator
   */
  public function testGetCollator() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getLanguageId
   * @todo Implement getLanguageId
   */
  public function testGetLanguageId() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

}
