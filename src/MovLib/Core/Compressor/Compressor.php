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
 * Defines the default compressor object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Compressor implements CompressorInterface {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Compressor";
  // @codingStandardsIgnoreEnd

  /**
   * The default compression level.
   *
   * @var integer
   */
  const DEFAULT_LEVEL = 9;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The desired compression level.
   *
   * @var integer
   */
  protected $level = self::DEFAULT_LEVEL;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  public function compress($data) {
    if (($compressed = gzencode($data, $this->level)) === false) {
      throw new CompressorException("Couldn't compress data.");
    }
    return $compressed;
  }

  /**
   * {@inheritdoc}
   */
  public function compressFile($uri) {
    try {
      // Build canonical absolute URI to the compressed file.
      $target = $this->getURI($uri);

      // @devStart
      if ($target === $uri) {
        throw new \InvalidArgumentException("Target and source can't be equal, seems like this file is already compressed: {$uri}");
      }
      // @devEnd

      // Compress the file's content and create the compressed file.
      file_put_contents($target, $this->compress(file_get_contents($uri)));

      // Ensure compressed file and source have the same modification time (important for e.g. nginx).
      touch($target, filemtime($uri));

      return $target;
    }
    catch (\Exception $e) {
      throw new CompressorException("Couldn't compress file: {$uri}", null, $e);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function decompress($data) {
    if (($decompressed = gzdecode($data)) === false) {
      throw new CompressorException("Couldn't decompress data.");
    }
    return $decompressed;
  }

  /**
   * {@inheritdoc}
   */
  public function decompressFile($uri) {
    // @devStart
    if (file_exists($uri) === false) {
      throw new CompressorException("Compressed file doesn't exist ('{$uri}').");
    }
    // @devEnd
    try {
      // Build canonical absolute URI to the uncompressed file.
      $target = $this->getURI($uri);

      // @devStart
      if ($target === $uri) {
        throw new \InvalidArgumentException("Target and source can't be equal, seems like this file is already decompressed: {$uri}");
      }
      // @devEnd

      // Decompress the compressed file's content.
      file_put_contents($target, $this->decompress(file_get_contents($uri)));

      return $target;
    }
    catch (\Exception $e) {
      throw new CompressorException("Couldn't decompress file: {$uri}", null, $e);
    }
  }

  /**
   * Get the compression level.
   *
   * @return integer
   *   The compression level.
   */
  public function getLevel() {
    return $this->level;
  }

  /**
   * {@inheritdoc}
   */
  public function getURI($uri) {
    $extLen = strlen(self::EXT);
    if (substr($uri, $extLen) === self::EXT) {
      return substr($uri, 0, -$extLen);
    }
    return $uri . self::EXT;
  }

  /**
   * {@inheritdoc}
   */
  public function setLevel($level) {
    // @devStart
    if ($level < 0 || $level > 9) {
      throw new \InvalidArgumentException("Compression level must be between 0 and 9: {$level}");
    }
    // @devEnd
    $this->level = (integer) $level;
    return $this;
  }

}
