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
 * Defines the compressor interface.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
interface CompressorInterface {

  /**
   * The file extension for compressed files (including leading dot).
   *
   * @var string
   */
  const EXT = ".gz";

  /**
   * Compress data.
   *
   * @param mixed $data
   *   The data to compress.
   * @return mixed
   *   The compressed data.
   * @throws \MovLib\Core\Compressor\CompressorException
   *   If compression of data fails.
   */
  public function compress($data);

  /**
   * Compress file.
   *
   * @param string $uri
   *   Canonical absolute URI to the file that should be compressed.
   * @return string
   *   Canonical absoltue URI to the compressed file.
   * @throws \MovLib\Core\Compressor\CompressorException
   *   If compression of file fails.
   */
  public function compressFile($uri);

  /**
   * Decompress data.
   *
   * @param mixed $data
   *   The data to decompress.
   * @return mixed
   *   The decompressed data.
   * @throws \MovLib\Core\Compressor\CompressorException
   *   If decompression of data fails.
   */
  public function decompress($data);

  /**
   * Decompress file.
   *
   * @param string $uri
   *   Canonical absolute URI to the file that should be decompressed.
   * @return string
   *   Canonical absolute URI to the decompressed file.
   * @throws \MovLib\Core\Compressor\CompressorException
   *   If decompression of file fails.
   */
  public function decompressFile($uri);

  /**
   * Get the compression level.
   *
   * @return mixed
   *   The compression level.
   */
  public function getLevel();

  /**
   * Get URI for compressed or decompressed URI.
   *
   * <b>NOTE</b><br>
   * This method will return the compressed URI if <var>$uri</var> isn't in a compressed format and the decompressed
   * URI if <var>$uri</var> is in a compressed format. Which ever applies is determined by the URI's extension.
   *
   * <b>NOTE</b><br>
   * This method doesn't validate that <var>$uri</var> is actually a valid URI.
   *
   * @param string $uri
   *   The URI to get the compressed or decompressed URI for.
   * @return string
   *   URI for compressed or decompressed URI.
   */
  public function getURI($uri);

  /**
   * Set the compression level.
   *
   * @param mixed $level
   *   The compression level to set.
   * @return this
   * @throws \InvalidArgumentException
   *   If <var>$level</var> is invalid.
   */
  public function setLevel($level);

}
