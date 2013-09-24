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
namespace MovLib\Test\Utility;

use \MovLib\Utility\Validator;
use \PHPUnit_Framework_TestCase;

/**
 * Test the validator utility class.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class ValidatorTest extends PHPUnit_Framework_TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Helper


  /**
   * We only want to know that these methods exist and get called correctly. Testing of the implementation follows. Note
   * that the boolean input method is tested seperately because it returns NULL.
   */
  public function testInputMethods() {
    foreach ([ "Number", "NumberFormatted", "Mail", "IpAddress", "Integer", "IntegerFormatted", "String", "Url" ] as $fn) {
      $this->assertFalse(Validator::{"input{$fn}"}("foobar"));
    }
  }

  public function testRemovePlusSign() {
    $data = "+1.01";
    $this->assertEquals("1.01", Validator::removePlusSign($data));
  }


  // ------------------------------------------------------------------------------------------------------------------- Boolean


  public function testBooleanTrue() {
    foreach ([ 1, "1", 1.0, true, "true", "on", "yes" ] as $data) {
      $this->assertTrue(Validator::boolean($data));
    }
  }

  public function testBooleanFalse() {
    foreach ([ 0, "0", 0.0, false, "false", "off", "no", "", "\n" ] as $data) {
      $this->assertFalse(Validator::boolean($data));
    }
  }

  public function testBooleanNull() {
    foreach ([ -1, "-1", -1.0, "-1.0", 2, "2", "y", "n", "0.0", 0.1, "0.1", "1.0", 1.1, "1.1", "MovLib" ] as $data) {
      $this->assertNull(Validator::boolean($data));
    }
  }

  public function testBooleanInput() {
    $this->assertNull(Validator::inputBoolean("foobar"));
  }


  // ------------------------------------------------------------------------------------------------------------------- Number


  public function testNumberValid() {
    foreach ([ 0, "0", -0, "-0", +0, "+0", 0.0, "0.0", -0.0, "-0.0", +0.0, "+0.0", 1.42e9, "1.42e9", 1.42e-9, "1.42e-9" ] as $data) {
      $this->assertEquals($data, Validator::number($data));
    }
  }

  public function testNumberInvalid() {
    foreach ([ "-----0", "+++++0", "*0", "/0", "%0", "1/2", NAN, INF, -INF, "MovLib", "\n", "" ] as $data) {
      $this->assertFalse(Validator::number($data));
    }
  }

  public function testNumberFormattedValidEnglish() {
    foreach ([ "1.1" => 1.1, "-1.1" => -1.1, "+1.1" => 1.1, "-1,000.01" => -1000.01, "+1,000.01" => 1000.01 ] as $data => $expected) {
      $this->assertEquals($expected, Validator::numberFormatted($data, [ "#locale" => "en" ]));
    }
  }

  public function testNumberFormattedValidGerman() {
    foreach ([ "1,1" => 1.1, "-1,1" => -1.1, "+1,1" => 1.1, "-1.000,01" => -1000.01, "+1.000,01" => 1000.01 ] as $data => $expected) {
      $this->assertEquals($expected, Validator::numberFormatted($data, [ "#locale" => "de" ]));
    }
  }

  public function testNumberFormattedInvalidEnglish() {
    foreach ([ "1,1", "-1,1", "+1,1", "-1.000,01", "+1.000,01" ] as $data) {
      $this->assertFalse(Validator::numberFormatted($data, [ "#locale" => "en" ]));
    }
  }

  public function testNumberFormattedInvalidGerman() {
    foreach ([ "1.1", "-1.1", "+1.1", "-1,000.01", "+1,000.01" ] as $data) {
      $this->assertFalse(Validator::numberFormatted($data, [ "#locale" => "de" ]));
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- IP Address


  public function testIPv4AddressValid() {
    foreach ([
      // ------------------------------ Valid
      "1.2.3.4",
      "8.8.8.8",          // Google
      "8.8.4.4",          // Google
      "194.232.104.141",  // ORF
      // ------------------------------ Private
      "10.255.255.254",
      "172.18.5.4",
      "192.168.1.0",
      // ------------------------------ Reserved
      "0.0.0.0",
      "127.0.0.1",
    ] as $data) {
      $this->assertEquals($data, Validator::ipAddress($data));
    }
  }

  public function testIPv4AddressInvalid() {
    foreach ([
      // ------------------------------ Port
      "1.2.3.4:5060",
      // ------------------------------ Private + Subnet
      "192.0.2.0/24",
      // ------------------------------ Invalid
      "1.256.3.4",
      "256.0.0.1",
      "..168.1.234",
      "10.x.1.234",
      "....",
      "...",
      "..",
      "8.8.4.4foobar",
      "8.8.4.4\0foo",
      "MovLib",
      "\n",
      "",
    ] as $data) {
      $this->assertFalse(Validator::ipAddress($data));
    }
  }

  public function testipv6AddressValid() {
    foreach ([
      "::1.2.3.4",
      "a:b:c::1.2.3.4",
      "a:b:c:d::1.2.3.4",
      "a:b:c:d:e::1.2.3.4",
      "a:b:c:d:e:f:1.2.3.4",
      "a:b:c:d:e:f:0:1",
      "a::c:d:e:f:0:1",
      "a::d:e:f:0:1",
      "a::e:f:0:1",
      "a::f:0:1",
      "a::0:1",
      "a::1",
      "a::",
      "::a:b:c:d:e:f",
      "::b:c:d:e:f",
      "::c:d:e:f",
      "::d:e:f",
      "::e:f",
      "::f",
      "a:b:c:d:e:f:0::",
      "a:b:c:d:e:f::",
      "b:c:d:e:f::",
      "c:d:e:f::",
      "d:e:f::",
      "e:f::",
      "f::",
      "a:b:c:d:e:f::0",
      "a:b:c:d:e:f::",
      "::ffff:5.6.7.8",
      "2002:cb0a:3cdd:1::1",
      "2001:4860:4860::8888",
      "2001:4860:4860::8844",
      "2001:0db8:0000:0000:0000:0000:1428:57ab",
      "2001:0DB8:0000:0000:0000:0000:1428:57AB",
      "2001:db8::1428:57ab",
      "2001:3::1",
      "2001:0000:4136:e378:8000:63bf:3fff:fdd2",
      "0:0:0:0:0:0:0:1",
      "0:0:0:0:0:0:0:0",
      "0:a:b:c:d:e:f::",
      "::0:a:b:c:d:e:f",
      "::ffff:c000:0280",
      "::ffff:192.0.2.12",
      "2001:0002:6c::430",
      "2001:10:240:ab::a",
      "2001:db8:8:4::2",
      "ff01:0:0:0:0:0:0:2",
      "fdf8:f53b:82e4::53",
      "fe80::200:5aee:feaa:20a2",
      "2001::1",
      "::",
      "::1",
    ] as $data) {
      $this->assertEquals($data, Validator::ipAddress($data));
    }
  }

  public function testipv6AddressInvalid() {
    foreach ([
      // ------------------------------ Network Resource Identifier Brackets
      "[fdf8:f53b:82e4::53]",
      "[fe80::200:5aee:feaa:20a2]",
      "[2001::1]",
      // ------------------------------ Port
      "[2001:0000:4136:e378:8000:63bf:3fff:fdd2]:5060",
      "2001:0000:4136:e378:8000:63bf:3fff:fdd2:5060",
      // ------------------------------ Invalid
      ":",
      ":::",
      "::::",
      ":::1",
      "::::1",
      "::256.0.0.1",
      "::01.02.03.04",
      "a:b:c:d:e:f:1.256.3.4",
      "a:b:c:d:e:f::1.2.3.4",
      "a:b:c:d:e:f:0:1:2",
      "a::b:c:d:e:f:0:1",
      "::0:1:a:b:c:d:e:f",
      "0:1:a:b:c:d:e:f::",
      "a:b:::e:f",
      "'::a:",
      "::a::",
      ":a::b",
      "a::b:",
      "::a:b::c",
      "abcde::f",
      ":10.0.0.1",
      "0:0:0:255.255.255.255",
      "1fff::a88:85a3::172.31.128.1",
      "a:b:c:d:e:f:0::1",
      "2001:00db8:0000:0000:0000:0000:1428:57ab",
      "2001:0db8:xxxx:0000:0000:0000:1428:57ab",
      "2001:db8::1428::57ab",
      "2001:dx0::1234",
      "2001:db0::12345",
      "2001::1::",
      "2001::1::/64",
      "fe80::200::abcd",
      "2001:3:1",
      "MovLib",
      "\n",
      "",
    ] as $data) {
      $this->assertFalse(Validator::ipAddress($data));
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Mail


  /**
   * @link https://github.com/iamcal/rfc822/blob/master/tests/tests.xml
   * @link http://codefool.tumblr.com/post/15288874550/list-of-valid-and-invalid-email-addresses
   */
  public function testMailValid() {
    foreach ([
      '""@movlib.org',
      "first.last@movlib.org",
      "1234567890123456789012345678901234567890123456789012345678901234@movlib.org",
      '"first\"last"@movlib.org',
      '"first@last"@movlib.org',
      '"first\\last"@movlib.org',
      "x@x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x2",
      "1234567890123456789012345678901234567890123456789012345678@12345678901234567890123456789012345678901234567890123456789.12345678901234567890123456789012345678901234567890123456789.123456789012345678901234567890123456789012345678901234567890123.movlib.org",
      "first.last@[12.34.56.78]",
      "first.last@[ipv6:::12.34.56.78]",
      "first.last@[ipv6:1111:2222:3333::4444:12.34.56.78]",
      "first.last@[ipv6:1111:2222:3333:4444:5555:6666:12.34.56.78]",
      "first.last@[ipv6:::1111:2222:3333:4444:5555:6666]",
      "first.last@[ipv6:1111:2222:3333::4444:5555:6666]",
      "first.last@[ipv6:1111:2222:3333:4444:5555:6666::]",
      "first.last@[ipv6:1111:2222:3333:4444:5555:6666:7777:8888]",
      "first.last@x23456789012345678901234567890123456789012345678901234567890123.movlib.org",
      "first.last@3com.com",
      "first.last@123.movlib.org",
      '"first\last"@movlib.org',
      '"abc\@def"@movlib.org',
      '"fred\ bloggs"@movlib.org',
      '"joe.\\blow"@movlib.org',
      '"abc@def"@movlib.org',
      "user+mailbox@movlib.org",
      "customer/department=shipping@movlib.org",
      '$a12345@movlib.org',
      "!def!xyz%abc@movlib.org",
      "_somename@movlib.org",
      "dclo@us.ibm.com",
      "peter.piper@movlib.org",
      "test+test@movlib.org",
      "test-test@movlib.org",
      "test*test@movlib.org",
      "+1~1+@movlib.org",
      "{_test_}@movlib.org",
      "test.test@movlib.org",
      '"test.test"@movlib.org',
      'test."test"@movlib.org',
      '"test@test"@movlib.org',
      "test@123.123.123.x123",
      "test@movlib.movlib.org",
      "test@movlib.movlib.movlib.org",
      "email@movlib.org",
      "firstname.lastname@movlib.org",
      "email@subdomain.movlib.org",
      "firstname+lastname@movlib.org",
      "email@[123.123.123.123]",
      '"email"@movlib.org',
      "1234567890@movlib.org",
      "email@movlib-one.com",
      "_______@movlib.org",
      "email@movlib.name",
      "email@movlib.museum",
      "email@movlib.co.jp",
      "firstname-lastname@movlib.org",
      'much."more\ unusual"@movlib.org',
      'very.unusual."@".unusual.com@movlib.org',
      'very."(),:;<>[]".very."very@\\\\\\ \"very".unusual@strange.movlib.org',
    ] as $data) {
      $this->assertEquals($data, Validator::mail($data));
    }
  }

  /**
   * @link https://github.com/iamcal/rfc822/blob/master/tests/tests.xml
   * @link http://codefool.tumblr.com/post/15288874550/list-of-valid-and-invalid-email-addresses
   */
  public function testMailInvalid() {
    foreach ([
      "first.last@movlib.org,com",
      "first\@last@movlib.org",
      "first.last",
      "123456789012345678901234567890123456789012345678901234567890@12345678901234567890123456789012345678901234567890123456789.12345678901234567890123456789012345678901234567890123456789.12345678901234567890123456789012345678901234567890123456789.12345.movlib.org",
      "12345678901234567890123456789012345678901234567890123456789012345@movlib.org",
      ".first.last@movlib.org",
      "first.last.@movlib.org",
      "first..last@movlib.org",
      '"first"last"@movlib.org',
      '"""@movlib.org',
      '"\"@movlib.org',
      'first\\@last@movlib.org',
      "first.last@",
      "x@x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456",
      "first.last@[.12.34.56.78]",
      "first.last@[12.34.56.789]",
      "first.last@[::12.34.56.78]",
      "first.last@[IPv5:::12.34.56.78]",
      "first.last@[ipv6:1111:2222:3333::4444:5555:12.34.56.78]",
      "first.last@[ipv6:1111:2222:3333:4444:5555:12.34.56.78]",
      "first.last@[ipv6:1111:2222:3333:4444:5555:6666:7777:12.34.56.78]",
      "first.last@[ipv6:1111:2222:3333:4444:5555:6666:7777]",
      "first.last@[ipv6:1111:2222:3333:4444:5555:6666:7777:8888:9999]",
      "first.last@[ipv6:1111:2222::3333::4444:5555:6666]",
      "first.last@[ipv6:1111:2222:3333::4444:5555:6666:7777]",
      "first.last@[ipv6:1111:2222:333x::4444:5555]",
      "first.last@[ipv6:1111:2222:33333::4444:5555]",
      "first.last@-movlib.com",
      "first.last@movlib-.com",
      "first.last@x234567890123456789012345678901234567890123456789012345678901234.movlib.org",
      "abc\@def@movlib.org",
      "abc\\@movlib.org",
      "doug\ \"ace\"\ lovell@movlib.org",
      "abc@def@movlib.org",
      "abc\\@def@movlib.org",
      "abc\@movlib.org",
      "@movlib.org",
      "doug@",
      '"qu@movlib.org',
      'ote"@movlib.org',
      ".dot@movlib.org",
      "dot.@movlib.org",
      "two..dot@movlib.org",
      '"doug "ace" l."@movlib.org',
      '"doug \"ace\" l."@movlib.org',
      'doug\ \"ace\"\ l\.@movlib.org',
      "hello world@movlib.org",
      "gatsby@f.sc.ot.t.f.i.tzg.era.l.d.",
      "test.movlib.org",
      '"[[ test ]]"@movlib.org',
      "first.last@movlib.123",
      "first.last@com",
      '"fred bloggs"@movlib.org',
      "plainaddress",
      "#@%^%#$@#$@#.com",
      "@movlib.org",
      "Joe Smith <email@movlib.org>",
      "email.movlib.org",
      "email@movlib@movlib.org",
      ".email@movlib.org",
      "email.@movlib.org",
      "email..email@movlib.org",
      "あいうえお@movlib.org",
      "email@movlib.org (Joe Smith)",
      "email@movlib",
      "email@-movlib.org",
      "email@111.222.333.44444",
      "email@movlib..org",
      "Abc..123@movlib.org",
      "email@123.123.123.123",
      '"(),:;<>[\]@movlib.org',
      'just"not"right@movlib.org',
      "this\ is\"really\"not\\\\allowed@movlib.org",
      " first.last@movlib.org ",
      "MovLib",
      "\n",
      "",
    ] as $data) {
      $this->assertFalse(Validator::mail($data));
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Integer


  public function testIntegerValid() {
    foreach ([ 0, "0", -0, "-0", +0, "+0", 0.0, -0.0, +0.0, 1.42e9 ] as $data) {
      $this->assertEquals($data, Validator::integer($data));
    }
  }

  public function testIntegerInvalid() {
    foreach ([ 0.1, "0.0", -0.1, "-0.0", +0.1, "+0.0", "1.42e9", 1.42e-9, "1.42e-9", "-----0", "+++++0", "*0", "/0", "%0", "1/2", NAN, INF, -INF, "MovLib", "\n", "" ] as $data) {
      $this->assertFalse(Validator::integer($data));
    }
  }

  public function testIntegerRange() {
    $range = [ "options" => [ "min_range" => -10, "max_range" => 10 ] ];
    $this->assertEquals(9, Validator::integer("9", $range));
    $this->assertEquals(-9, Validator::integer("-9", $range));
    $this->assertFalse(Validator::integer("11", $range));
    $this->assertFalse(Validator::integer("-11", $range));
  }

  public function testIntegerFormattedValidEnglish() {
    $this->assertEquals(1234, Validator::integerFormatted("1,234", [ "#locale" => "en" ]));
    $this->assertEquals("1,234", Validator::integerFormatted("1,234", [ "#locale" => "en", "#formatted" => true ]));
  }

  public function testIntegerFormattedValidGerman() {
    $this->assertEquals(1234, Validator::integerFormatted("1.234", [ "#locale" => "de" ]));
    $this->assertEquals("1.234", Validator::integerFormatted("1.234", [ "#locale" => "de", "#formatted" => true ]));
  }

  public function testIntegerFormattedInvalidEnglish() {
    foreach ([ "1.0", "-1", "-1.0", "-1,0", "1,1", "-1,1", "+1,1", "-1.000", "+1.000,01" ] as $data) {
      $this->assertFalse(Validator::integerFormatted($data, [ "#locale" => "en" ]));
    }
  }

  public function testIntegerFormattedInvalidGerman() {
    foreach ([ "1,0", "-1", "-1,0", "-1.0", "1.1", "-1.1", "+1.1", -1.000, "-1,000.01", "+1,000.01" ] as $data) {
      $this->assertFalse(Validator::integerFormatted($data, [ "#locale" => "de" ]));
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- String


  /**
   * @link http://www.php.net/manual/en/reference.pcre.pattern.modifiers.php#54805
   * @link https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet
   */
  public function testStringValid() {
    foreach ([
      "movlib",                     // Valid ASCII
      "mov < lib",                  // Valid ASCII and usage of special HTML character
      "mov < lib > mov",            // Valid ASCII and usage of special HTML characters
      "<>'\"&/",                    // Valid character sequence
      "\\\";alert('XSS');//",       // Valid character sequence
      "mov < > lib",                // Valid as well
      "κόσμε",                      // Valid UTF-8
      "\xc3\xb1",                   // Valid 2 Octet Sequence
      "\xe2\x82\xa1",               // Valid 3 Octet Sequence
      "\xf0\x90\x8c\xbc",           // Valid 4 Octet Sequence
    ] as $data) {
      $this->assertEquals($data, Validator::string($data));
    }
    $this->assertEquals("mov\nlib", Validator::string("mov\r\nlib", [ "#allow_lf" => true ]));
    $this->assertEquals("", Validator::string("", [ "#allow_empty" => true ]));
    // Not only does the following test make no sense at all, it would fail as well. Because the last action that we
    // perform is trim() and this function removes all kind of whitespace from the beginning and end of a string, this
    // includes line feeds.
    //$this->assertEquals("\n", Validator::string("\r\n", [ "#allow_empty" => true, "#allow_lf" => true ]));
  }

  /**
   * @link http://www.php.net/manual/en/reference.pcre.pattern.modifiers.php#54805
   * @link https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet
   */
  public function testStringInvalid() {
    foreach ([
      "",                           // Invalid empty string
      "mov\nlib",                   // Invalid line feed
      "\x00",                       // Invalid control character (NULL)
      "\xc3\x28",                   // Invalid 2 Octet Sequence
      "\xa0\xa1",                   // Invalid Sequence Identifier
      "\xe2\x28\xa1",               // Invalid 3 Octet Sequence (in 2nd Octet)
      "\xe2\x82\x28",               // Invalid 3 Octet Sequence (in 3rd Octet)
      "\xf0\x28\x8c\xbc",           // Invalid 4 Octet Sequence (in 2nd Octet)
      "\xf0\x90\x28\xbc",           // Invalid 4 Octet Sequence (in 3rd Octet)
      "\xf0\x28\x8c\x28",           // Invalid 4 Octet Sequence (in 4th Octet)
      "\xf8\xa1\xa1\xa1\xa1",       // Valid 5 Octet Sequence (but not Unicode!)
      "\xfc\xa1\xa1\xa1\xa1\xa1",   // Valid 6 Octet Sequence (but not Unicode!)
      // ----------------------------- Invalid HTML usage
      "movlib<script></script>",
      "movlib&amp;lt;script>&amp;lt;/script>",
      // ----------------------------- OWASP XSS
      "';alert(String.fromCharCode(88,83,83))//';alert(String.fromCharCode(88,83,83))//\";alert(String.fromCharCode(88,83,83))//\";alert(String.fromCharCode(88,83,83))//--></SCRIPT>\">'><SCRIPT>alert(String.fromCharCode(88,83,83))</SCRIPT>",
      "'';!--\"<XSS>=&{()}",
      "<SCRIPT SRC=http://ha.ckers.org/xss.js></SCRIPT>",
      "<IMG SRC=\"javascript:alert('XSS');\">",
      "<IMG SRC=javascript:alert('XSS')>",
      "<IMG SRC=JaVaScRiPt:alert('XSS')>",
      '<IMG SRC=javascript:alert("XSS")>',
      "<IMG SRC=`javascript:alert(\"RSnake says, 'XSS'\")`>",
      '<a onmouseover="alert(document.cookie)">xxs link</a>',
      "<a onmouseover=alert(document.cookie)>xxs link</a>",
      '<IMG """><SCRIPT>alert("XSS")</SCRIPT>">',
      "<IMG SRC=javascript:alert(String.fromCharCode(88,83,83))>",
      "<IMG SRC=# onmouseover=\"alert('xxs')\">",
      "<IMG SRC= onmouseover=\"alert('xxs')\">",
      "<IMG onmouseover=\"alert('xxs')\">",
      "<IMG SRC=&#106;&#97;&#118;&#97;&#115;&#99;&#114;&#105;&#112;&#116;&#58;&#97;&#108;&#101;&#114;&#116;&#40;&#39;&#88;&#83;&#83;&#39;&#41;>",
      "<IMG SRC=&#0000106&#0000097&#0000118&#0000097&#0000115&#0000099&#0000114&#0000105&#0000112&#0000116&#0000058&#0000097&#0000108&#0000101&#0000114&#0000116&#0000040&#0000039&#0000088&#0000083&#0000083&#0000039&#0000041>",
      "<IMG SRC=&#x6A&#x61&#x76&#x61&#x73&#x63&#x72&#x69&#x70&#x74&#x3A&#x61&#x6C&#x65&#x72&#x74&#x28&#x27&#x58&#x53&#x53&#x27&#x29>",
      "<IMG SRC=\"jav	ascript:alert('XSS');\">",
      "<IMG SRC=\"jav&#x09;ascript:alert('XSS');\">",
      "<IMG SRC=\"jav&#x0A;ascript:alert('XSS');\">",
      "<IMG SRC=\"jav&#x0D;ascript:alert('XSS');\">",
      "<IMG SRC=\" &#14;  javascript:alert('XSS');\">",
      '<SCRIPT/XSS SRC="http://ha.ckers.org/xss.js"></SCRIPT>',
      '<<SCRIPT>alert("XSS");//<</SCRIPT>',
      "<SCRIPT SRC=http://ha.ckers.org/xss.js?< B >",
      "<IMG SRC=\"javascript:alert('XSS')\"",
      "<iframe src=http://ha.ckers.org/scriptlet.html <",
      "<STYLE>li {list-style-image: url(\"javascript:alert('XSS')\");}</STYLE><UL><LI>XSS</br>",
      // @todo include the rest of the tests (also check if useful: https://code.google.com/p/html-sanitizer-testbed/)
    ] as $data) {
      $this->assertFalse(Validator::string($data));
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- URL


  public function testUrlValid() {
    // Normal URLs
    foreach ([
      "http://movlib.org/",
      "http://movlib.org",
      "https://movlib.org/",
      "https://movlib.org",
      "http://movlib.org/foo/bar/",
      "http://movlib.org/foo/bar",
      "https://movlib.org/foo/bar/",
      "https://movlib.org/foo/bar",
      "http://movlib.org/foo?bar=42",
      "https://movlib.org/foo?bar=42",
      "https://en.wikipedia.org/wiki//dev/random",
      "https://ja.wikipedia.org/wiki/Unix%E7%B3%BB",
      "http://www.youtube.com/watch?v=5gUKvmOEGCU",
    ] as $data) {
      $this->assertEquals($data, Validator::url($data));
    }
    // Special URLs
    foreach ([
      "https://ja.wikipedia.org/wiki/Unix%E7%B3%BB" => "https://ja.wikipedia.org/wiki/Unix系",
      "https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Component%21Utility%21Url.php/function/Url%3A%3AisValid/8" =>
        "https://api.drupal.org/api/drupal/core!lib!Drupal!Component!Utility!Url.php/function/Url%3A%3AisValid/8"
    ] as $expected => $data) {
      $this->assertEquals($expected, Validator::url($data));
    }
  }

  public function testUrlInvalid() {
    foreach ([
      "http://movlib.org:80/",
      "http://admin:1234@movlib.org/",
      "http://admin@movlib.org/",
      "ftp://movlib.org/",
      "ldap://movlib.org/",
      "mailto:user@movlib.org",
      "//movlib.org",
      "movlib.org",
      "www.movlib.org",
      "//movlib.org/foo/bar",
      "movlib.org/foo/bar",
      "www.movlib.org/foo/bar",
      "MovLib",
      "\n",
      "",
    ] as $data) {
      $this->assertFalse(Validator::url($data));
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- URL


  public function testUsernameValid() {
    foreach ([ "User", "\xf0\x9f\x92\xa9" ] as $data) {
      $this->assertEquals($data, Validator::username($data));
    }
  }

  /**
   * @expectedException \MovLib\Exception\ValidatorException
   */
  public function testUsernameInvalid() {
    foreach ([
      "Fleshgrinder",   // Already in use
      "MovLib",         // Blacklisted
      " User",          // Starts with space
      "User ",          // Ends with space
      "Us  er",         // More than two spaces in a row
      " Mov Lib ",      // Spaces, spaces, spaces
    ] as $data) {
      Validator::username($data);
    }
  }

  /**
   * @expectedException \MovLib\Exception\ValidatorException
   */
  public function testInputUsername() {
    Validator::inputUsername("foobar");
  }


}
