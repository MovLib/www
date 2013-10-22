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
namespace MovLib\Test\Presentation\Validation;

use \MovLib\Presentation\Validation\PlainText;

/**
 * Description of PlainTextTest
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class PlainTextTest {

  public static function dataProviderInvalid() {
    return [
      [ "movlib" ], // Valid ASCII
      [ "mov < lib" ], // Valid ASCII and usage of special HTML character
      [ "mov < lib > mov" ], // Valid ASCII and usage of special HTML characters
      [ "<>'\"&/" ], // Valid character sequence
      [ "\\\";alert('XSS');//" ], // Valid (standalone) character sequence
      [ "mov < > lib" ], // Valid as well
      [ "κόσμε" ], // Valid UTF-8
      [ "\xc3\xb1" ], // Valid 2 Octet Sequence
      [ "\xe2\x82\xa1" ], // Valid 3 Octet Sequence
      [ "\xf0\x90\x8c\xbc" ], // Valid 4 Octet Sequence
    ];
  }

  public static function dataProviderValid() {
    return [
      [ "" ], // Invalid empty string
      [ "\x00" ], // Invalid control character (NULL)
      [ "mov\nlib" ], // Invalid line feed
      [ "\xc3\x28" ], // Invalid 2 Octet Sequence
      [ "\xa0\xa1" ], // Invalid Sequence Identifier
      [ "\xe2\x28\xa1" ], // Invalid 3 Octet Sequence (in 2nd Octet)
      [ "\xe2\x82\x28" ], // Invalid 3 Octet Sequence (in 3rd Octet)
      [ "\xf0\x28\x8c\xbc" ], // Invalid 4 Octet Sequence (in 2nd Octet)
      [ "\xf0\x90\x28\xbc" ], // Invalid 4 Octet Sequence (in 3rd Octet)
      [ "\xf0\x28\x8c\x28" ], // Invalid 4 Octet Sequence (in 4th Octet)
      [ "\xf8\xa1\xa1\xa1\xa1" ], // Valid 5 Octet Sequence (but not Unicode!)
      [ "\xfc\xa1\xa1\xa1\xa1\xa1" ], // Valid 6 Octet Sequence (but not Unicode!)
      // ----------------------------- Invalid HTML usage
      [ "movlib<script></script>" ],
      [ "movlib&amp;lt;script>&amp;lt;/script>" ],
      // ----------------------------- OWASP XSS
      [ "'';!--\"<XSS>=&{()}" ],
      [ "<IMG SRC=javascript:alert('XSS')>" ],
      [ "<IMG SRC=JaVaScRiPt:alert('XSS')>" ],
      [ '<IMG SRC=javascript:alert("XSS")>' ],
      [ "<IMG onmouseover=\"alert('xxs')\">" ],
      [ '<<SCRIPT>alert("XSS");//<</SCRIPT>' ],
      [ "<IMG SRC=\"javascript:alert('XSS')\"" ],
      [ "<IMG SRC=\"javascript:alert('XSS');\">" ],
      [ "<IMG SRC= onmouseover=\"alert('xxs')\">" ],
      [ "<IMG SRC=\"jav	ascript:alert('XSS');\">" ],
      [ '<IMG """><SCRIPT>alert("XSS")</SCRIPT>">' ],
      [ "<IMG SRC=# onmouseover=\"alert('xxs')\">" ],
      [ "<IMG SRC=\"jav&#x0A;ascript:alert('XSS');\">" ],
      [ "<IMG SRC=\"jav&#x0D;ascript:alert('XSS');\">" ],
      [ "<IMG SRC=\"jav&#x09;ascript:alert('XSS');\">" ],
      [ "<SCRIPT SRC=http://ha.ckers.org/xss.js?< B >" ],
      [ "<IMG SRC=\" &#14;  javascript:alert('XSS');\">" ],
      [ "<SCRIPT SRC=http://ha.ckers.org/xss.js></SCRIPT>" ],
      [ "<iframe src=http://ha.ckers.org/scriptlet.html <" ],
      [ "<a onmouseover=alert(document.cookie)>xxs link</a>" ],
      [ "<IMG SRC=`javascript:alert(\"RSnake says, 'XSS'\")`>" ],
      [ '<a onmouseover="alert(document.cookie)">xxs link</a>' ],
      [ '<SCRIPT/XSS SRC="http://ha.ckers.org/xss.js"></SCRIPT>' ],
      [ "<IMG SRC=javascript:alert(String.fromCharCode(88,83,83))>" ],
      [ "<STYLE>li {list-style-image: url(\"javascript:alert('XSS')\");}</STYLE><UL><LI>XSS</br>" ],
      [ "<IMG SRC=&#x6A&#x61&#x76&#x61&#x73&#x63&#x72&#x69&#x70&#x74&#x3A&#x61&#x6C&#x65&#x72&#x74&#x28&#x27&#x58&#x53&#x53&#x27&#x29>" ],
      [ "<IMG SRC=&#106;&#97;&#118;&#97;&#115;&#99;&#114;&#105;&#112;&#116;&#58;&#97;&#108;&#101;&#114;&#116;&#40;&#39;&#88;&#83;&#83;&#39;&#41;>" ],
      [ "<IMG SRC=&#0000106&#0000097&#0000118&#0000097&#0000115&#0000099&#0000114&#0000105&#0000112&#0000116&#0000058&#0000097&#0000108&#0000101&#0000114&#0000116&#0000040&#0000039&#0000088&#0000083&#0000083&#0000039&#0000041>" ],
      [ "';alert(String.fromCharCode(88,83,83))//';alert(String.fromCharCode(88,83,83))//\";alert(String.fromCharCode(88,83,83))//\";alert(String.fromCharCode(88,83,83))//--></SCRIPT>\">'><SCRIPT>alert(String.fromCharCode(88,83,83))</SCRIPT>" ],
      // @todo include the rest of the tests (also check if useful: https://code.google.com/p/html-sanitizer-testbed/)
    ];
  }

  /**
   * @covers \MovLib\Presentation\Validation\PlainText::__construct
   * @covers \MovLib\Presentation\Validation\PlainText::validate
   * @dataProvider dataProviderInvalid
   * @expectedException \MovLib\Exception\ValidationException
   */
  public function testInvalid($str) {
    (new PlainText($str))->validate();
  }

  /**
   * @covers \MovLib\Presentation\Validation\PlainText::__construct
   * @covers \MovLib\Presentation\Validation\PlainText::validate
   * @dataProvider dataProviderValid
   */
  public function testValid($str) {
    $this->assertEquals($str, (new PlainText($str))->validate());
  }

}
