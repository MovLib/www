<?php

/*!
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

use \MovLib\Data\I18n;

/**
 * @coversDefaultClass \MovLib\Data\I18n
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class I18nTest extends \MovLib\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /** @var \MovLib\Data\I18n */
  protected $i18n;

  // Formatting parameters and test values.
  protected $args                         = [ "test", 42 ];
  protected $pattern                      = "PHPUnit {0} PHPUnit {1}";
  protected $patternFormatted             = "PHPUnit test PHPUnit 42";
  protected $patternGerman                = "PHPUnit {0} PHPUnit {1} Deutsch";
  protected $patternGermanFormatted       = "PHPUnit test PHPUnit 42 Deutsch";
  protected $patternTestLanguage          = "PHPUnit {0} PHPUnit {1} XX";
  protected $patternTestLanguageFormatted = "PHPUnit test PHPUnit 42 XX";


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  /**
   * Called before each test.
   */
  protected function setUp() {
    $this->i18n = new I18n(\Locale::getDefault());
  }

  /**
   * Called after all tests.
   */

  public static function tearDownAfterClass() {
    $queries = "";
    foreach ([ "message", "route" ] as $context) {
      $queries .= "DELETE FROM `{$context}s` WHERE `{$context}` LIKE '%PHPUnit%';";
    }
    (new \MovLib\Tool\Database())->queries($queries);
  }


  // ------------------------------------------------------------------------------------------------------------------- Helpers


  protected function helperInsertTestMessages() {
    global $db;
    foreach ([ "message", "route" ] as $context) {
      $db->query("INSERT INTO `{$context}s`
        (`{$context}`, `dyn_translations`)
        VALUES ('{$this->pattern}', COLUMN_CREATE('de', '{$this->patternGerman}', 'xx', '{$this->patternTestLanguage}'))
        ON DUPLICATE KEY UPDATE `dyn_translations`=VALUES(`dyn_translations`)"
      );
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Data Providers


  public function dataProviderTestConstructAcceptLanguageHeaderValid() {
    $args = [];
    foreach (new SystemLanguages() as $languageCode => $systemLanguage) {
      $args[] = [ $systemLanguage->locale, $systemLanguage->locale, $languageCode ];
      $args[] = [ $languageCode, $systemLanguage->locale, $languageCode ];
    }
    return $args;
  }

  public function dataProviderTestConstructLanguageCodeValid() {
    $args = [];
    foreach (new SystemLanguages() as $languageCode => $systemLanguage) {
      $args[] = [ $languageCode, $systemLanguage->locale, $languageCode ];
    }
    return $args;
  }

  public function dataProviderTestFormatDateInvalidTimestamp() {
    return [
      [ null ],
      [ "Invalid timestamp" ],
    ];
  }


  // ------------------------------------------------------------------------------------------------------------------- Tests


  /**
   * @covers ::__construct
   */
  public function testConstructInvalidHTTPAcceptLanguageCode() {
    unset($_SERVER["LANGUAGE_CODE"]);
    $_SERVER["HTTP_ACCEPT_LANGUAGE"] = "xx-XX";
    $defaultLocale                   = \Locale::getDefault();
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
    $defaultLocale                   = \Locale::getDefault();
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

    $defaultLocale       = \Locale::getDefault();
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
    $_SERVER["HTTP_ACCEPT_LANGUAGE"] = "xx_XX";

    $_SERVER["LANGUAGE_CODE"] = $languageCode;
    $i18n                     = new I18n();
    $this->assertEquals($expectedLocale, $i18n->locale);
    $this->assertEquals($expectedLanguageCode, $i18n->languageCode);
  }

  /**
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
   * @dataProvider dataProviderTestFormatDateInvalidTimestamp
   * @expectedException \IntlException
   */
  public function testFormatDateInvalidTimestamp($timestamp) {
    $this->i18n->formatDate($timestamp);
  }

  /**
   * @covers ::formatDate
   * @expectedException \Exception
   */
  public function testFormatDateInvalidTimeZone() {
    $this->i18n->formatDate(time(), "PHPUnit");
  }

  /**
   * @covers ::formatDate
   */
  public function testFormatDateValid() {
    $timestamp = time();

    // No timezone supplied.
    $formatter = new \IntlDateFormatter($this->i18n->locale, \IntlDateFormatter::LONG, \IntlDateFormatter::LONG, new \DateTimeZone(ini_get("date.timezone")));
    $this->assertEquals($formatter->format($timestamp), $this->i18n->formatDate($timestamp));

    // Valid timezone supplied.
    $formatter = new \IntlDateFormatter($this->i18n->locale, \IntlDateFormatter::LONG, \IntlDateFormatter::LONG, new \DateTimeZone("Europe/Vienna"));
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
  public function testFormatMessageValidNonExistentPattern() {
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
   * @covers ::formatMessage
   */
  public function testFormatMessageValidWithArguments() {
    $this->helperInsertTestMessages();
    // Set language code to German.
    $this->i18n->languageCode = "de";
    $this->assertEquals($this->patternTestLanguageFormatted, $this->i18n->formatMessage("message", $this->pattern, $this->args, [ "language_code" => "xx" ]));
    $this->assertEquals($this->patternFormatted, $this->i18n->formatMessage("message", $this->pattern, $this->args, [ "language_code" => $this->i18n->defaultLanguageCode ]));
    $this->assertEquals($this->patternGermanFormatted, $this->i18n->formatMessage("message", $this->pattern, $this->args));
  }

  /**
   * @covers ::formatMessage
   */
  public function testFormatMessageValidWithoutArguments() {
    $this->helperInsertTestMessages();
    // Set language code to German.
    $this->i18n->languageCode = "de";
    $this->assertEquals($this->patternTestLanguage, $this->i18n->formatMessage("message", $this->pattern, null, [ "language_code" => "xx" ]));
    $this->assertEquals($this->pattern, $this->i18n->formatMessage("message", $this->pattern, null, [ "language_code" => $this->i18n->defaultLanguageCode ]));
    $this->assertEquals($this->patternGerman, $this->i18n->formatMessage("message", $this->pattern, null));
  }

  /**
   * @covers ::getCollator
   */
  public function testGetCollator() {
    $collator = $this->i18n->getCollator();
    $this->assertInstanceOf("\\Collator", $collator);
    $this->assertEquals($collator, $this->i18n->getCollator());
  }

  /**
   * @covers ::getCollator
   */
  public function testGetCollatorInvalidLocale() {
    $this->i18n->locale = null;
    $this->assertInstanceOf("\\Collator", $this->i18n->getCollator());
  }

  /**
   * @covers ::insertMessage
   * @global \MovLib\Tool\Database $db
   */
  public function testInsertMessageWithComment() {
    global $db;
    $message = "PHPUnit test message";
    $comment = "PHPUnit comment";
    $this->i18n->insertMessage($message, [ "comment" => $comment ]);
    $this->assertNotNull($db
        ->query("SELECT `message`, `comment` FROM `messages` WHERE `message` = ? AND `comment` = ? LIMIT 1", "ss", [ $message, $comment ])
        ->get_result()->fetch_all()
    );
  }

  /**
   * @covers ::insertMessage
   * @global \MovLib\Tool\Database $db
   */
  public function testInsertMessageWithoutComment() {
    global $db;
    $message = "PHPUnit test message without comment";
    $this->i18n->insertMessage($message);
    $this->assertNotNull($db
        ->query("SELECT `message`, `comment` FROM `messages` WHERE `message` = ? AND `comment` IS NULL LIMIT 1", "s", [ $message ])
        ->get_result()->fetch_all()
    );
  }

  /**
   * @covers ::insertOrUpdateTranslation
   * @expectedException \MovLib\Exception\DatabaseException
   */
  public function testInsertOrUpdateTranslationInvalidContext() {
    $this->i18n->insertOrUpdateTranslation("PHPUnit", 1, "xx", "PHPUnit {0} PHPUnit {1} insertOrUpdate translated");
  }

  /**
   * @covers ::insertOrUpdateTranslation
   */
  public function testInsertOrUpdateTranslationValid() {
    global $db;
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
   * @covers ::insertRoute
   */
  public function testInsertRoute() {
    global $db;
    $route = "PHPUnit test route";
    $this->i18n->insertRoute($route);
    $this->assertNotNull($db->query("SELECT `route` FROM `routes` WHERE `route` = ? LIMIT 1", "s", [ $route ])->get_result()->fetch_all());
  }

  /**
   * @covers ::r
   */
  public function testR() {
    $options = [ "language_code" => "xx" ];
    $this->assertEquals($this->i18n->formatMessage("route", $this->pattern, $this->args, $options), $this->i18n->r($this->pattern, $this->args, $options));
    $this->assertEquals($this->i18n->formatMessage("route", $this->pattern, $this->args), $this->i18n->r($this->pattern, $this->args));
    $this->assertEquals($this->i18n->formatMessage("route", $this->pattern, null, $options), $this->i18n->r($this->pattern, null, $options));
    $this->assertEquals($this->i18n->formatMessage("route", $this->pattern, null), $this->i18n->r($this->pattern, null));
  }

  /**
   * @covers ::t
   */
  public function testT() {
    $options = [ "language_code" => "xx" ];
    $this->assertEquals($this->i18n->formatMessage("message", $this->pattern, $this->args, $options), $this->i18n->t($this->pattern, $this->args, $options));
    $this->assertEquals($this->i18n->formatMessage("message", $this->pattern, $this->args), $this->i18n->t($this->pattern, $this->args));
    $this->assertEquals($this->i18n->formatMessage("message", $this->pattern, null, $options), $this->i18n->t($this->pattern, null, $options));
    $this->assertEquals($this->i18n->formatMessage("message", $this->pattern, null), $this->i18n->t($this->pattern, null));
  }

}
