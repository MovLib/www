<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 **
 * Copyright © 2006 Google Inc.
 * Copyright © 2013 Daniil Skrobov <yetanotherape@gmail.com>
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
namespace MovLib\Core\Diff;

use \MovLib\Core\Diff\Diff;

/**
 * Please see the covered class ({@see \MovLib\Core\Diff\Diff}) for more information!
 *
 * @coversDefaultClass \MovLib\Core\Diff\Diff
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class DiffTest extends \MovLib\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * @var \MovLib\Core\Diff\Diff
   */
  protected $diff;


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->diff = new Diff();
  }


  // ------------------------------------------------------------------------------------------------------------------- Diff::bisect


  public function dataProviderBisect() {
    return [
      [ [
          (object) (object) [ "code" => Diff::DELETE, "text" => "c", "length" => 1 ],
          (object) (object) [ "code" => Diff::INSERT, "text" => "m", "length" => 1 ],
          (object) (object) [ "code" => Diff::COPY, "text" => "a", "length" => 1 ],
          (object) (object) [ "code" => Diff::DELETE, "text" => "t", "length" => 1 ],
          (object) (object) [ "code" => Diff::INSERT, "text" => "p", "length" => 1 ],
        ], "cat", "map", (float) PHP_INT_MAX
      ],
      [ [
          (object) (object) [ "code" => Diff::DELETE, "text" => "cat", "length" => 3 ],
          (object) (object) [ "code" => Diff::INSERT, "text" => "map", "length" => 3 ],
        ], "cat", "map", 0.0
      ],
    ];
  }

  /**
   * @covers Diff::bisect
   * @dataProvider dataProviderBisect
   * @param array $expected
   * @param string $text1
   * @param string $text2
   * @param float $deadline
   */
  public function testBisect($expected, $text1, $text2, $deadline) {
    $this->assertEquals($expected, $this->invoke($this->diff, "bisect", [ $text1, mb_strlen($text1), $text2, mb_strlen($text2), $deadline ]));
  }


  // ------------------------------------------------------------------------------------------------------------------- Diff::commonPrefix


  public function dataProviderCommonPrefix() {
    return [
      [ 0, "abc", "xyz" ],
      [ 4, "1234abcdef", "1234xyz" ],
      [ 4, "1234", "1234xyz" ],
      [ 3, "FOO💩FOO", "FOOyFOO" ],  // 4-byte UTF-8 as separator
      [ 1, "💩FOO", "💩BAR" ],        // 4-byte UTF-8 in common
    ];
  }

  /**
   * @covers Diff::commonPrefix
   * @dataProvider dataProviderCommonPrefix
   * @param integer $commonPrefixLength
   * @param string $text1
   * @param string $text2
   */
  public function testCommonPrefix($commonPrefixLength, $text1, $text2) {
    $this->assertEquals($commonPrefixLength, $this->invoke($this->diff, "commonPrefix", [ $text1, mb_strlen($text1), $text2, mb_strlen($text2) ]));
  }


  // ------------------------------------------------------------------------------------------------------------------- Diff::commonSuffix


  public function dataProviderCommonSuffix() {
    return [
      [ 0, "abc", "xyz" ],
      [ 4, "abcdef1234", "xyz1234" ],
      [ 4, "1234", "xyz1234" ],
      [ 3, "FOO💩FOO", "FOOyFOO" ],  // 4-byte UTF-8 as separator
      [ 1, "FOO💩", "BAR💩" ],        // 4-byte UTF-8 in common
    ];
  }

  /**
   * @covers Diff::commonSuffix
   * @dataProvider dataProviderCommonSuffix
   * @param integer $commonSuffixLength
   * @param string $text1
   * @param string $text2
   */
  public function testCommonSuffix($commonSuffixLength, $text1, $text2) {
    $this->assertEquals($commonSuffixLength, $this->invoke($this->diff, "commonSuffix", [ $text1, mb_strlen($text1), $text2, mb_strlen($text2) ]));
  }


  // ------------------------------------------------------------------------------------------------------------------- Diff::diff


  public function dataProviderDiff() {
    return [
      [ [], "", "" ],
      [ [(object) [ "code" => Diff::COPY, "text" => "abc", "length" => 3 ]], "abc", "abc" ], // This would return an empty array if we'd called getDiff()!
      [ [
        (object) [ "code" => Diff::COPY, "text" => "0", "length" => 1 ],
        (object) [ "code" => Diff::INSERT, "text" => "X", "length" => 1 ],
        (object) [ "code" => Diff::COPY, "text" => "12", "length" => 2 ],
        (object) [ "code" => Diff::INSERT, "text" => "X", "length" => 1 ],
        (object) [ "code" => Diff::COPY, "text" => "0", "length" => 1 ],
        (object) [ "code" => Diff::INSERT, "text" => "X", "length" => 1 ],
        (object) [ "code" => Diff::COPY, "text" => "34", "length" => 2 ],
        (object) [ "code" => Diff::INSERT, "text" => "X", "length" => 1 ],
        (object) [ "code" => Diff::COPY, "text" => "0", "length" => 1 ],
      ], "0120340", "0X12X0X34X0" ],
      [ [
        (object) [ "code" => Diff::COPY, "text" => "0", "length" => 1 ],
        (object) [ "code" => Diff::DELETE, "text" => "X", "length" => 1 ],
        (object) [ "code" => Diff::COPY, "text" => "12", "length" => 2 ],
        (object) [ "code" => Diff::DELETE, "text" => "X", "length" => 1 ],
        (object) [ "code" => Diff::COPY, "text" => "0", "length" => 1 ],
        (object) [ "code" => Diff::DELETE, "text" => "X", "length" => 1 ],
        (object) [ "code" => Diff::COPY, "text" => "34", "length" => 2 ],
        (object) [ "code" => Diff::DELETE, "text" => "X", "length" => 1 ],
        (object) [ "code" => Diff::COPY, "text" => "0", "length" => 1 ],
      ], "0X12X0X34X0", "0120340" ],
      [ [
        (object) [ "code" => Diff::DELETE, "text" => "Apple", "length" => 5 ],
        (object) [ "code" => Diff::INSERT, "text" => "Banana", "length" => 6 ],
        (object) [ "code" => Diff::COPY, "text" => "s are a", "length" => 7 ],
        (object) [ "code" => Diff::INSERT, "text" => "lso", "length" => 3 ],
        (object) [ "code" => Diff::COPY, "text" => " fruit.", "length" => 7 ],
      ], "Apples are a fruit.", "Bananas are also fruit." ],
      [ [
        (object) [ "code" => Diff::DELETE, "text" => "a", "length" => 1 ],
        (object) [ "code" => Diff::INSERT, "text" => "ڀ", "length" => 1 ],
        (object) [ "code" => Diff::COPY, "text" => "x", "length" => 1 ],
        (object) [ "code" => Diff::DELETE, "text" => "\t", "length" => 1 ],
        (object) [ "code" => Diff::INSERT, "text" => "\x00", "length" => 1 ],
      ], "ax\t", "ڀx\x00" ],
      [ [
        (object) [ "code" => Diff::DELETE, "text" => "1", "length" => 1 ],
        (object) [ "code" => Diff::COPY, "text" => "a", "length" => 1 ],
        (object) [ "code" => Diff::DELETE, "text" => "y", "length" => 1 ],
        (object) [ "code" => Diff::COPY, "text" => "b", "length" => 1 ],
        (object) [ "code" => Diff::DELETE, "text" => "2", "length" => 1 ],
        (object) [ "code" => Diff::INSERT, "text" => "xab", "length" => 3 ],
      ], "1ayb2", "abxab" ],
      [ [
        (object) [ "code" => Diff::INSERT, "text" => "xaxcx", "length" => 5 ],
        (object) [ "code" => Diff::COPY, "text" => "abc", "length" => 3 ],
        (object) [ "code" => Diff::DELETE, "text" => "y", "length" => 1 ],
      ], "abcy", "xaxcxabc" ],
      [ [
        (object) [ "code" => Diff::DELETE, "text" => "ABCD", "length" => 4 ],
        (object) [ "code" => Diff::COPY, "text" => "a", "length" => 1 ],
        (object) [ "code" => Diff::DELETE, "text" => "=", "length" => 1 ],
        (object) [ "code" => Diff::INSERT, "text" => "-", "length" => 1 ],
        (object) [ "code" => Diff::COPY, "text" => "bcd", "length" => 3 ],
        (object) [ "code" => Diff::DELETE, "text" => "=", "length" => 1 ],
        (object) [ "code" => Diff::INSERT, "text" => "-", "length" => 1 ],
        (object) [ "code" => Diff::COPY, "text" => "efghijklmnopqrs", "length" => 15 ],
        (object) [ "code" => Diff::DELETE, "text" => "EFGHIJKLMNOefg", "length" => 14 ],
      ], "ABCDa=bcd=efghijklmnopqrsEFGHIJKLMNOefg", "a-bcd-efghijklmnopqrs" ],
      [ [
        (object) [ "code" => Diff::INSERT, "text" => " ", "length" => 1 ],
        (object) [ "code" => Diff::COPY, "text" => "a", "length" => 1 ],
        (object) [ "code" => Diff::INSERT, "text" => "nd", "length" => 2 ],
        (object) [ "code" => Diff::COPY, "text" => " [[Pennsylvania]]", "length" => 17 ],
        (object) [ "code" => Diff::DELETE, "text" => " and [[New", "length" => 10 ],
      ], "a [[Pennsylvania]] and [[New", " and [[Pennsylvania]]" ],
    ];
  }

  /**
   * @covers Diff::diff
   * @dataProvider dataProviderDiff
   * @param array $expected
   * @param string $text1
   * @param string $text2
   */
  public function testDiff($expected, $text1, $text2) {
    $this->assertEquals($expected, $this->invoke($this->diff, "diff", [ $text1, mb_strlen($text1), $text2, mb_strlen($text2), (float) PHP_INT_MAX ]));
  }


  // ------------------------------------------------------------------------------------------------------------------- Diff::getDiff


  /**
   * @covers Diff::getDiff
   */
  public function testGetDiff() {
    $this->assertEquals([], $this->diff->getDiff("foo💩", "foo💩"));
  }

  /**
   * @see ::dataProviderDiff
   */
  public function dataProviderGetDiffDiff() {
    $data = $this->dataProviderDiff();
    $data[1][0] = [];
    return $data;
  }

  /**
   * @see DiffTest::testDiff
   * @covers Diff::getDiff
   * @dataProvider dataProviderGetDiffDiff
   * @param array $expected
   * @param string $text1
   * @param string $text2
   */
  public function testGetDiffDiff($expected, $text1, $text2) {
    $this->assertEquals($expected, $this->diff->getDiff($text1, $text2));
  }


  // ------------------------------------------------------------------------------------------------------------------- Diff::getDiffPatch




  // ------------------------------------------------------------------------------------------------------------------- Diff::halfMatch


  public function dataProviderHalfMatch() {
    return [
      [ null, "1234567890", "abcdef" ],
      [ null, "12345", "23" ],
      [ [ "12", "90", "a", "z", "345678", 6 ], "1234567890", "a345678z" ],
      [ [ "1234", "0", "abc", "z", "56789", 5 ], "1234567890", "abc56789z" ],
      [ [ "1", "7890", "a", "xyz", "23456", 5 ], "1234567890", "a23456xyz" ],
      [ [ "12123", "123121", "a", "z", "1234123451234", 13 ], "121231234123451234123121", "a1234123451234z" ],
      [ [ "", "-=-=-=-=-=", "x", "", "x-=-=-=-=-=-=-=", 15 ], "x-=-=-=-=-=-=-=-=-=-=-=-=", "xx-=-=-=-=-=-=-=" ],
      [ [ "-=-=-=-=-=", "", "", "y", "-=-=-=-=-=-=-=y", 15 ], "-=-=-=-=-=-=-=-=-=-=-=-=y", "-=-=-=-=-=-=-=yy" ],
      [ [ "qHillo", "w", "x", "Hulloy", "HelloHe", 7 ], "qHilloHelloHew", "xHelloHeHulloy" ],
      [ [ "💩12", "90💩", "💩a", "z💩", "345678", 6 ], "💩1234567890💩", "💩a345678z💩" ], // 4-byte UTF-8 character
    ];
  }

  /**
   * @covers Diff::halfMatch
   * @dataProvider dataProviderHalfMatch
   * @param mixed $expected
   * @param string $longText
   * @param string $shortText
   */
  public function testHalfMatch($expected, $longText, $shortText) {
    $this->assertEquals($expected, $this->invoke($this->diff, "halfMatch", [ $longText, mb_strlen($longText), $shortText, mb_strlen($shortText) ]));
  }


  // ------------------------------------------------------------------------------------------------------------------- End to end


  public function dataProviderEndToEnd() {
    return [
      [ "foo", "bar" ],
      [ "0", "1" ],
      [ 'O:21:"MovLib\Component\Date":1:{s:4:"date";s:19:"1997-01-01 00:00:00";}', 's:10:"1997-00-00";' ],
      [
        'O:35:"MovLib\Data\Company\CompanyRevision":13:{s:7:"deleted";b:0;s:12:"originatorId";i:1;s:2:"id";i:20140531161826;s:6:"userId";i:1;s:7:"aliases";a:1:{i:0;s:19:"Synapse Films, Inc.";}}}',
        'O:35:"MovLib\Data\Company\CompanyRevision":13:{s:7:"deleted";b:0;s:12:"originatorId";i:1;s:2:"id";i:20140531155037;s:6:"userId";i:3;s:7:"aliases";s:37:"a:1:{i:0;s:19:"Synapse Films, Inc.";}";}}'
      ],
      [
        'O:35:"MovLib\Data\Company\CompanyRevision":13:{s:7:"deleted";b:0;s:12:"originatorId";i:1;s:2:"id";i:20140531161826;s:6:"userId";i:1;s:7:"aliases";a:1:{i:0;s:19:"Synapse Films, Inc.";}s:4:"name";s:12:"ynapse Films";s:5:"links";a:2:{i:0;s:25:"http://synapse-films.com/";i:1;s:60:"https://www.facebook.com/pages/Synapse-Films/193094600718553";}s:12:"foundingDate";O:21:"MovLib\Component\Date":1:{s:4:"date";s:19:"1997-01-01 00:00:00";}s:11:"defunctDate";N;s:7:"placeId";i:97981472;s:12:"descriptions";a:2:{s:2:"de";s:2274:"&lt;p&gt;Synapse films ist ein US-amerikanisches DVD-Label welches von Don May, Jr. gegründet und betrieben wird. Mitgründer und Partner sind Jerry Chandler und Charles Fiedler. Gegründet wurde das Unternehmen 1997 mit dem Ziel Horrorfilme und Science Fiction Filme in perfekter digitaler Qualität zu präsentieren.&lt;/p&gt;&lt;p&gt;Der Hauptfokus liegt also in der Restaurierung und dem Video Transfer von Genre Filmen die lediglich eine sehr schlechte, oder gar keine, Video-Auswertung in der Vergangenheit erhielten. Don May, Jr. hatte in der Vergangenheit bei seinem vorigen Unternehmen Elite Entertainment – er war nur Teilhaber − schon sehr viel Erfahrung im Bereich der Restauration von Filmen gesammelt, Laserdisc Veröffentlichungen von Elite Entertainment galten damals als die Besten im Horrorbereich. Diese Erfahrung kommt ihm und seinem Unternehmen natürlich heute zugute.&lt;/p&gt;&lt;p&gt;Der Katalog von Synapse films reicht von Euro-Horror Filmen über Dokumentationen bis hin zu japanischen Exploitation Filmen aus den 1960er und 1970er.&lt;/p&gt;&lt;p&gt;2004 veröffentlichte Synapse films den kontroversen Thriller – ein unbarmherziger Film auf DVD. Die Veröffentlichung des Films war jedoch nicht weniger kontrovers wie der Film selbst. Der notorische Regisseur Bo Arne Vibenius verbreitete überall, dass Synapse films den Film von ihm einfach gestohlen habe und schrieb unter falschem Namen Drohbriefe, E-Mails und Faxe. 2002 kaufte Synapse films von Chrome Entertainment die weltweiten Vertriebsrechte für $10.000. Die Gründe weshalb Vibenius gegen das DVD-Label vorging sind unbekannt.&lt;/p&gt;&lt;p&gt;Synapse films arbeitet eng mit anderen kleinen US DVD-Unternehmen zusammen. Die Veröffentlichungen der Legends Of The Poisonous Seductress Serie entstanden zum Beispiel in Zusammenarbeit mit Panik House Entertainment. Des Weiteren hilft Synapse films Impulse pictures ihre DVD-Videos über Ryko Distribution auch in größere Einkaufsketten zu bringen.&lt;/p&gt;&lt;p&gt;Der aus Detroit stammende Filmemacher Nicholas Schlegel hat seine Dokumentation The Synapse Story aufgeteilt in 4 Teile bei YouTube veröffentlicht. Die Dokumentation bietet einen sehr guten Einblick in die Entstehungsgeschichte von Synapse films.&lt;/p&gt;";s:2:"en";s:1862:"&lt;p&gt;Synapse Films is a DVD/Blu-ray label owned and operated by Don May, Jr. and his business partners Jerry Chandler and Charles Fiedler. The company was started in 1997 and it specializes in cult horror, science fiction, and exploitation films. May graduated from Illinois State University in 199. He always had an interest in television and film. “I caught the laserdisc bug while working at a local laserdisc store while I was in college. I was selling laserdisc players and buying product and I pretty much spent every extra dollar I had on laserdiscs. I loved movies and the disc format and knew this was a business I wanted to be in.” May became a part owner of Elite Entertainment. This was after he quit his job at laserdisc retailer.&lt;/p&gt;&lt;p&gt;Synapse&amp;apos;s focus has been to provide quality restoration and video transfers to genre films usually neglected or granted shoddy release on home video. May has brought to the company an expertise in video mastering and film restoration gleaned from nearly a decade of experience in the LaserDisc industry, and personal enthusiasm for exploitation film of all stripe.&lt;/p&gt;&lt;p&gt;The Synapse catalog ranges from European horror touchstones like Vampyros Lesbos, and Castle of Blood, to important genre documentaries including Roy Frumkes&amp;apos; Document of the Dead, from drive-in favorites like The Brain That Wouldn&amp;apos;t Die to Leni Riefenstahl’s Nazi film Triumph of the Will.&lt;/p&gt;&lt;p&gt;In 2004, Synapse released a definitive edition of the controversial Thriller – A Cruel Picture, a DVD which was not without controversy itself.&lt;/p&gt;&lt;p&gt;Recently, Detroit film scholar Nicholas Schlegel released his documentary The Synapse Story in its entirety on YouTube. The documentary details the history and vision of the label and its founders.&lt;/p&gt;";}s:17:"imageDescriptions";a:2:{s:2:"de";s:375:"&lt;p&gt;Synapse Films | synapse-films.com&lt;/p&gt;&lt;p&gt;Das Logo besitzt nach deutschsprachigem Recht keine Schöpfungshöhe.&lt;/p&gt;&lt;p&gt;Das Logo besteht aus einem einfachen Schriftzug sowie ggf. einfachsten Formen und besitzt daher international keine Schöpfungshöhe.&lt;/p&gt;&lt;p&gt;Das Logo kann dem Marken- oder Gebrauchsmusterrecht unterliegen.&lt;/p&gt;";s:2:"en";s:355:"&lt;p&gt;Synapse Films | synapse-films.com&lt;/p&gt;&lt;p&gt;The image has no threshold of originality according to Austrian-law.&lt;/p&gt;&lt;p&gt;The image consists of a simple lettering and simplest forms and therefore has no threshold of originality according to international law.&lt;/p&gt;&lt;p&gt;Can be a registered trade mark or design.&lt;/p&gt;";}s:14:"wikipediaLinks";a:2:{s:2:"de";s:42:"http://de.wikipedia.org/wiki/Synapse_films";s:2:"en";s:42:"http://en.wikipedia.org/wiki/Synapse_Films";}}',
        'O:35:"MovLib\Data\Company\CompanyRevision":13:{s:7:"deleted";b:0;s:12:"originatorId";i:1;s:2:"id";i:20140531155037;s:6:"userId";i:3;s:7:"aliases";s:37:"a:1:{i:0;s:19:"Synapse Films, Inc.";}";s:4:"name";s:13:"Synapse Films";s:5:"links";s:115:"a:2:{i:0;s:25:"http://synapse-films.com/";i:1;s:60:"https://www.facebook.com/pages/Synapse-Films/193094600718553";}";s:12:"foundingDate";s:10:"1997-00-00";s:11:"defunctDate";N;s:7:"placeId";i:97981472;s:12:"descriptions";a:2:{s:2:"de";s:2274:"&lt;p&gt;Synapse films ist ein US-amerikanisches DVD-Label welches von Don May, Jr. gegründet und betrieben wird. Mitgründer und Partner sind Jerry Chandler und Charles Fiedler. Gegründet wurde das Unternehmen 1997 mit dem Ziel Horrorfilme und Science Fiction Filme in perfekter digitaler Qualität zu präsentieren.&lt;/p&gt;&lt;p&gt;Der Hauptfokus liegt also in der Restaurierung und dem Video Transfer von Genre Filmen die lediglich eine sehr schlechte, oder gar keine, Video-Auswertung in der Vergangenheit erhielten. Don May, Jr. hatte in der Vergangenheit bei seinem vorigen Unternehmen Elite Entertainment – er war nur Teilhaber − schon sehr viel Erfahrung im Bereich der Restauration von Filmen gesammelt, Laserdisc Veröffentlichungen von Elite Entertainment galten damals als die Besten im Horrorbereich. Diese Erfahrung kommt ihm und seinem Unternehmen natürlich heute zugute.&lt;/p&gt;&lt;p&gt;Der Katalog von Synapse films reicht von Euro-Horror Filmen über Dokumentationen bis hin zu japanischen Exploitation Filmen aus den 1960er und 1970er.&lt;/p&gt;&lt;p&gt;2004 veröffentlichte Synapse films den kontroversen Thriller – ein unbarmherziger Film auf DVD. Die Veröffentlichung des Films war jedoch nicht weniger kontrovers wie der Film selbst. Der notorische Regisseur Bo Arne Vibenius verbreitete überall, dass Synapse films den Film von ihm einfach gestohlen habe und schrieb unter falschem Namen Drohbriefe, E-Mails und Faxe. 2002 kaufte Synapse films von Chrome Entertainment die weltweiten Vertriebsrechte für $10.000. Die Gründe weshalb Vibenius gegen das DVD-Label vorging sind unbekannt.&lt;/p&gt;&lt;p&gt;Synapse films arbeitet eng mit anderen kleinen US DVD-Unternehmen zusammen. Die Veröffentlichungen der Legends Of The Poisonous Seductress Serie entstanden zum Beispiel in Zusammenarbeit mit Panik House Entertainment. Des Weiteren hilft Synapse films Impulse pictures ihre DVD-Videos über Ryko Distribution auch in größere Einkaufsketten zu bringen.&lt;/p&gt;&lt;p&gt;Der aus Detroit stammende Filmemacher Nicholas Schlegel hat seine Dokumentation The Synapse Story aufgeteilt in 4 Teile bei YouTube veröffentlicht. Die Dokumentation bietet einen sehr guten Einblick in die Entstehungsgeschichte von Synapse films.&lt;/p&gt;";s:2:"en";s:1850:"&lt;p&gt;Synapse Films is a DVD/Blu-ray label owned and operated by Don May, Jr. and his business partners Jerry Chandler and Charles Fiedler. The company was started in 1997 and it specializes in cult horror, science fiction, and exploitation films. May graduated from Illinois State University in 199. He always had an interest in television and film. “I caught the laserdisc bug while working at a local laserdisc store while I was in college. I was selling laserdisc players and buying product and I pretty much spent every extra dollar I had on laserdiscs. I loved movies and the disc format and knew this was a business I wanted to be in.” May became a part owner of Elite Entertainment. This was after he quit his job at laserdisc retailer.&lt;/p&gt;&lt;p&gt;Synapse&#039;s focus has been to provide quality restoration and video transfers to genre films usually neglected or granted shoddy release on home video. May has brought to the company an expertise in video mastering and film restoration gleaned from nearly a decade of experience in the LaserDisc industry, and personal enthusiasm for exploitation film of all stripe.&lt;/p&gt;&lt;p&gt;The Synapse catalog ranges from European horror touchstones like Vampyros Lesbos, and Castle of Blood, to important genre documentaries including Roy Frumkes&#039; Document of the Dead, from drive-in favorites like The Brain That Wouldn&#039;t Die to Leni Riefenstahl’s Nazi film Triumph of the Will.&lt;/p&gt;&lt;p&gt;In 2004, Synapse released a definitive edition of the controversial Thriller – A Cruel Picture, a DVD which was not without controversy itself.&lt;/p&gt;&lt;p&gt;Recently, Detroit film scholar Nicholas Schlegel released his documentary The Synapse Story in its entirety on YouTube. The documentary details the history and vision of the label and its founders.&lt;/p&gt;";}s:17:"imageDescriptions";a:2:{s:2:"de";s:375:"&lt;p&gt;Synapse Films | synapse-films.com&lt;/p&gt;&lt;p&gt;Das Logo besitzt nach deutschsprachigem Recht keine Schöpfungshöhe.&lt;/p&gt;&lt;p&gt;Das Logo besteht aus einem einfachen Schriftzug sowie ggf. einfachsten Formen und besitzt daher international keine Schöpfungshöhe.&lt;/p&gt;&lt;p&gt;Das Logo kann dem Marken- oder Gebrauchsmusterrecht unterliegen.&lt;/p&gt;";s:2:"en";s:355:"&lt;p&gt;Synapse Films | synapse-films.com&lt;/p&gt;&lt;p&gt;The image has no threshold of originality according to Austrian-law.&lt;/p&gt;&lt;p&gt;The image consists of a simple lettering and simplest forms and therefore has no threshold of originality according to international law.&lt;/p&gt;&lt;p&gt;Can be a registered trade mark or design.&lt;/p&gt;";}s:14:"wikipediaLinks";a:2:{s:2:"de";s:42:"http://de.wikipedia.org/wiki/Synapse_films";s:2:"en";s:42:"http://en.wikipedia.org/wiki/Synapse_Films";}}'
      ],
      [ "1234💩", "💩1234" ],
      [ "💩💩bar", "💩foo💩💩" ],
      [ "-0", "0" ],
    ];
  }

  /**
   * @dataProvider dataProviderEndToEnd
   * @param string $text1
   * @param string $text2
   */
  public function testEndToEnd($text1, $text2) {
    $patch = $this->diff->getDiffPatch($this->diff->getDiff($text1, $text2));
    $this->assertEquals($text2, $this->diff->applyPatch($text1, $patch), "Patch was '{$patch}'");
  }

}
