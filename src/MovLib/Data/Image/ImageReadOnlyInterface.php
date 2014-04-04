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
namespace MovLib\Data\Image;

/**
 * Defines the read only image interface.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
interface ImageReadOnlyInterface {

  /**
   * Check if the image exists or if a placeholder should be used instead.
   *
   * @return boolean
   *   <code>TRUE</code> if the image exists, <code>FALSE</code> otherwise.
   */
  public function imageExists();

  /**
   * Get the image's directory URI.
   *
   * @return string
   *   The image's directory URI.
   */
  public function getImageDirectory();

  /**
   * Get the image's URI.
   *
   * @return string
   *   The image's URI.
   */
  public function getImageURI();

  /**
   * Get the image's URL.
   *
   * @return string
   *   The image's URL.
   */
  public function getImageURL();

  /**
   * Get an image's style.
   *
   * @param integer|string $style
   *   The desired style, one of the CSS grid classes, or the image's width as integer.
   * @param integer|null $height [optional]
   *   The desired image's height, defaults to <code>NULL</code> (keep aspect ratio).
   * @param boolean $crop [optional]
   *   Whether to crop the image, defaults to <code>FALSE</code>.
   * @return \MovLib\Data\Image\ImageStyle
   *   The desired image style.
   */
  public function getImageStyle($style = "s2", $height = null, $crop = false);

  /**
   * Get the image style's URI.
   *
   * @param integer|string $style
   *   The image style to get the URI for.
   * @return string
   *   The image style's URI.
   */
  public function getImageStyleURI($style);

  /**
   * Get the image style's placeholder URL.
   *
   * @return string
   *   The image style's placeholder URL.
   */

  /**
   * Get the image style's URL.
   *
   * @param integer|string $style
   *   The image style to get the URL for.
   * @return string
   *   The image style's URl.
   */
  public function getImageStyleURL($style);

}
