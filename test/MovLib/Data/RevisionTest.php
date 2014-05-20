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

use \cogpowered\FineDiff\Diff;
use \cogpowered\FineDiff\Granularity\Character;

/**
 * @coversDefaultClass \MovLib\Data\Revision
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class RevisionTest extends \MovLib\TestCase {

  /**
   * The dependency injection container.
   *
   * @var \MovLib\Core\HTTP\DIContainerHTTP
   */
  protected $diContainerHTTP;


  /**
   * {@inheritdoc}
   */
  protected function getDIContainer() {
    static $diContainer = null;
    if (!$diContainer) {
      $diContainer = new \MovLib\Core\HTTP\DIContainerHTTP;
      $diContainer->config = new \MovLib\Core\Config();
      $diContainer->log = new \MovLib\Core\Log($diContainer->config, $diContainer->config->hostname, true);
  //    $diContainer->fs = new MovLib\Core\FileSystem("/var/www", diContainer->config->hostnameStatic);
      $diContainer->intl = new \MovLib\Core\Intl($diContainer->config);
    }
    return $diContainer;
  }

  /**
   * Data provider for various input combinations for the apply patch multibyte test.
   *
   * Please note that some characters may simply not exist in the font of your editor and therefore cannot be rendered.
   *
   * @return array
   */
  public function dataProviderTestApplyPatchMultibyte() {
    $diContainer = $this->getDIContainer();
    $data = [
      [ "PHPÜnitテスト", "PHPUnitテスト" ],
      [ "MovLib、無料ムービーライブラリ", "MovLib, η ελεύθερη βιβλιοθήκη ταινιών" ],
      [ "「MovLib、無料ムービーライブラリ」", "«MovLib, η ελεύθερη βιβλιοθήκη ταινιών»" ],
      [ "Йא؃伙", "֏ܔޗझ💩" ],
    ];

    $gr               = new Genre\GenreRevision($diContainer, 1);
    $gr->names        = [ "en" => "PHPUnit Test", "de" => "PHPUnit" ];
    $gr->descriptions = [ "en" => "PHPUnit", "de" => "PHPUnit", "ja" => "マルチバイトのテストのためのテストのジャンルの説明。" ];
    $old              = serialize($gr);
    $gr->names        = [
      "en" => "PHPUnit Test",
      "de" => "PHPUnit Test",
      "ja" => "PHPUnitテスト",
      "pa" => "PHPUnit ਟੈਸਟ",
      "th" => "PHPUnit ทดสอบ",
    ];
    $gr->descriptions = null;
    $gr->wikipedia["ja"] = "http://ja.wikipedia.org/wiki/アクション映画";
    $data[]           = [ $old, serialize($gr)];

    return $data;
  }

  /**
   * Data provider for various input combinations for the diff test.
   *
   * @return array
   */
  public function dataProviderTestDiff() {
    $diContainer = $this->getDIContainer();
    $data = [
      [ "string one", "string two" ],
      [ "string\none", "string\ntwo" ],
      [ (object) [ "foo" => "bar" ], (object) [ "foo" => "something else" ] ],
    ];
    $data[] = [ serialize($diContainer->intl), serialize($diContainer->intl->setLocale("de_AT")) ];

    $gr               = new Genre\GenreRevision($diContainer, 1);
    $gr->names        = [ "en" => "PHPUnit", "de" => "PHPUnit" ];
    $gr->descriptions = [ "en" => "PHPUnit", "de" => "PHPUnit" ];
    $old              = serialize($gr);
    $gr->names["ja"]  = "PHPUnit";
    $gr->descriptions = null;
    $data[]           = [ $old, serialize($gr)];

    return $data;
  }

  /**
   * @dataProvider dataProviderTestDiff
   * @param mixed $from
   *   The first string to compute the patch from.
   * @param mixed $to
   *   The second string to compute the patch from, will be restored by applying the patch to <code>$from</code>.
   */
  public function testApplyPatch($from, $to) {
    $from     = serialize($from);
    $to       = serialize($to);
    $revision = new Revision($this->getDIContainer(), "Genre", 1);
    $patch    = $revision->diff($from, $to);
    $this->assertEquals($to, $revision->applyPatch($from, $patch));
  }

  /**
   * @dataProvider dataProviderTestApplyPatchMultibyte
   * @param mixed $from
   *   The first string to compute the patch from.
   * @param mixed $to
   *   The second string to compute the patch from, will be restored by applying the patch to <code>$from</code>.
   */
  public function testApplyPatchMultibyte($from, $to) {
    $from     = serialize($from);
    $to       = serialize($to);
    $revision = new Revision($this->getDIContainer(), "Genre", 1);
    $patch    = $revision->diff($from, $to);
    $this->assertEquals($to, $revision->applyPatch($from, $patch));
  }

  /**
   * @dataProvider dataProviderTestDiff
   * @param mixed $from
   *   The first argument for the diff.
   * @param mixed $to
   *   The second argument for the diff.
   */
  public function testDiff($from, $to) {
    $from     = serialize($from);
    $to       = serialize($to);
    $fineDiff = new Diff(new Character());
    $revision = new Revision($this->getDIContainer(), "Genre", 1);
    $this->assertEquals((string) $fineDiff->getOpcodes($from, $to), $revision->diff($from, $to));
  }

}
