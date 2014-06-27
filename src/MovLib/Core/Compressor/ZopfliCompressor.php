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
 * Defines the zopfli compressor.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class ZopfliCompressor extends Compressor {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * {@inheritdoc}
   */
  const name = "ZopfliCompressor";
  // @codingStandardsIgnoreEnd

  /**
   * The default compression level.
   *
   * @var integer
   */
  const DEFAULT_LEVEL = 15;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  public function compress($data) {
    if (($compressed = zopfli_encode($data)) === false) {
      throw new CompressorException("Couldn't compress data.");
    }
    return $compressed;
  }

  /**
   * {@inheritdoc}
   */
  public function decompress($data) {
    if (($decompressed = zopfli_decode($data)) === false) {
      throw new CompressorException("Couldn't decompress data.");
    }
    return $decompressed;
  }

  /**
   * {@inheritdoc}
   */
  public function setLevel($level) {
    // @devStart
    if ($level < 1) {
      throw new \InvalidArgumentException("Compression level cannot be less than 1: {$level}");
    }
    // @devEnd
    $this->level = (integer) $level;
    return $this;
  }

}
