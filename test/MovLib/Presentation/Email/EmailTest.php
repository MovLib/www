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
namespace MovLib\Presentation\Email;

use \MovLib\Presentation\Email\Email;

/**
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class EmailTest extends \MovLib\TestCase {

  /**
   * @covers \MovLib\Presentation\Email\AbstractEmail::__construct
   * @covers \MovLib\Presentation\Email\Email::__construct
   */
  public function testConstruct() {
    $email = new Email("phpunit@movlib.org", "PHPUnit Email", "<p>PHPUnit HTML Email</p>", "PHPUnit Plain Text Email");
    $this->assertAttributeEquals("phpunit@movlib.org", "recipient", $email);
    $this->assertAttributeEquals("PHPUnit Email", "subject", $email);
    $this->assertAttributeEquals("<p>PHPUnit HTML Email</p>", "html", $email);
    $this->assertAttributeEquals("PHPUnit Plain Text Email", "text", $email);
  }

  /**
   * @covers \MovLib\Presentation\Email\AbstractEmail::__construct
   * @expectedException \MovLib\Exception\MailerException
   */
  public function testRecipient() {
    new Email("phpunit1@movlib.org, phpunit2@movlib.org", "", "", "");
  }

  public static function dataProviderWordwrap() {
    return [
      [ "âââ_ñññ_ëëë_ôôô_æææ_øøø_äää_üüü_ööö", "âââ_ñññ_ëëë_ôôô_æææ_øøø_äää_üüü_ööö", 10, false ],
      [ "âââ_ñññ_ëë\në_ôôô_æææ_\nøøø_äää_üü\nü_ööö", "âââ_ñññ_ëëë_ôôô_æææ_øøø_äää_üüü_ööö", 10, true ],
      [ "âââ ñññ\nëëë ôôô\næææ øøø\näää üüü\nööö", "âââ ñññ ëëë ôôô æææ øøø äää üüü ööö", 10, false ],
      [ "âââ ñññ\nëëë ôôô\næææ øøø\näää üüü\nööö", "âââ ñññ ëëë ôôô æææ øøø äää üüü ööö", 10, true ],
      [ "Iñtërnâtiônàlizætiøn_and_then_the_quick_brown_fox_jumped_overly_the_lazy_dog.", "Iñtërnâtiônàlizætiøn_and_then_the_quick_brown_fox_jumped_overly_the_lazy_dog.", 10, false ],
      [ "Iñtërnâtiô\nnàlizætiøn\n_and_then_\nthe_quick_\nbrown_fox_\njumped_ove\nrly_the_la\nzy_dog.", "Iñtërnâtiônàlizætiøn_and_then_the_quick_brown_fox_jumped_overly_the_lazy_dog.", 10, true ],
      [
        "Iñtërnâtiônàlizætiøn_and_then_the_quick_brown_fox_jumped_overly_the_lazy_dog\nand one\nday the\nlazy dog\nhumped the\npoor fox\ndown until\nshe died.",
        "Iñtërnâtiônàlizætiøn_and_then_the_quick_brown_fox_jumped_overly_the_lazy_dog and one day the lazy dog humped the poor fox down until she died.",
        10, false
      ],
      [
        "Iñtërnâtiô\nnàlizætiøn\n_and_then_\nthe_quick_\nbrown_fox_\njumped_ove\nrly_the_la\nzy_dog and\none day\nthe lazy\ndog humped\nthe poor\nfox down\nuntil she\ndied.",
        "Iñtërnâtiônàlizætiøn_and_then_the_quick_brown_fox_jumped_overly_the_lazy_dog and one day the lazy dog humped the poor fox down until she died.",
        10, true
      ],
    ];
  }

  /**
   * @covers \MovLib\Presentation\Email\AbstractEmail::wordwrap
   * @dataProvider dataProviderWordwrap
   */
  public function testWordwrap($expected, $string, $width, $cut) {
    $this->assertEquals($expected, (new Email("", "", "", ""))->wordwrap($string, $width, $cut));
  }

}
