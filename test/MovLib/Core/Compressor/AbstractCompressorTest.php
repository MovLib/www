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
namespace MovLib\Core\Compressor;

/**
 * Base class for all compressor tests.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractCompressorTest extends \MovLib\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * @var \MovLib\Core\Compressor\CompressorInterface
   */
  protected $compressor;


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $class = substr(static::class, 0, -4);
    $this->compressor = new $class();
  }


  // ------------------------------------------------------------------------------------------------------------------- Abstract Methods


  abstract protected function doCompressData($level);


  // ------------------------------------------------------------------------------------------------------------------- Helpers


  protected function getData() {
    static $data = null;
    if (null === $data) {
      $data = serialize($this);
    }
    return $data;
  }

  protected function getCompressedData($level = CompressorInterface::LEVEL_BEST) {
    static $compressed = [];
    if (false === isset($compressed[$level])) {
      $compressed[$level] = $this->doCompressData($this->invoke($this->compressor, "getLevels")[$level]);
    }
    return $compressed[$level];
  }


  // ------------------------------------------------------------------------------------------------------------------- Data Provider


  public function dataProviderValidLevel() {
    return [ [ CompressorInterface::LEVEL_LOW ], [ CompressorInterface::LEVEL_AVG ], [ CompressorInterface::LEVEL_BEST ] ];
  }

  public function dataProviderInvalidLevel() {
    return [ [ -1 ], [ 10 ] ];
  }


  // ------------------------------------------------------------------------------------------------------------------- Tests


  /**
   * @covers ::compress
   */
  public function testCompress() {
    $this->assertEquals($this->getCompressedData(), $this->compressor->compress($this->getData()));
  }

  /**
   * @covers ::compress
   * @covers ::setLevel
   * @dataProvider dataProviderValidLevel
   */
  public function testCompressCustomLevel($validLevel) {
    $this->assertEquals($this->getCompressedData($validLevel), $this->compressor->setLevel($validLevel)->compress($this->getData()));
  }

  /**
   * @covers ::decompress
   */
  public function testDecompress() {
    $this->assertEquals($this->getData(), $this->compressor->decompress($this->getCompressedData()));
  }

  /**
   * Tests that a custom compression level has no effect on decompression.
   * @covers ::setLevel
   * @covers ::decompress
   * @dataProvider dataProviderValidLevel
   */
  public function testDecompressCustomLevel($validLevel) {
    $this->assertEquals($this->getData(), $this->compressor->setLevel($validLevel)->decompress($this->getCompressedData()));
  }

  /**
   * @covers ::setLevel
   * @covers ::getLevel
   * @dataProvider dataProviderValidLevel
   */
  public function testSetGetLevel($validLevel) {
    $this->assertEquals($validLevel, $this->compressor->setLevel($validLevel)->getLevel());
  }

  /**
   * @covers ::setLevel
   * @dataProvider dataProviderInvalidLevel
   * @expectedException \InvalidArgumentException
   */
  public function testSetInvalidLevel($invalidLevel) {
    $this->compressor->setLevel($invalidLevel);
  }

}
