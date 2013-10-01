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
namespace MovLib\Data\Image;

use \MovLib\Data\Delayed\MethodCalls as DelayedMethodCalls;
use \MovLib\Exception\ImageException;

/**
 * Default implementation for image's.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractImage extends \MovLib\Data\Database {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The image's changed timestamp.
   *
   * @var int
   */
  protected $imageChanged;

  /**
   * Flag indicating whetever this image exists or not.
   *
   * @var boolean
   */
  public $imageExists = false;

  /**
   * The image's filename.
   *
   * @var string
   */
  protected $imageName;

  /**
   * All available image styles for the concrete image.
   *
   * @see \MovLib\Data\Image\AbstractImage::setImageStyles()
   * @var array
   */
  protected $imageStyles;

  /**
   * The image's type, one of the <var>IMAGETYPE_*</var> constants.
   *
   * @var int
   */
  protected $imageType;


  // ------------------------------------------------------------------------------------------------------------------- Private Properties



  /**
   * The relative path within the <code>uploads</code> directory without leading nor trailing slash.
   *
   * @var string
   */
  private $directory;

  /**
   * The image's styles.
   *
   * @var array
   */
  private $styles;

  /**
   * All available image widths.
   *
   * All are direct matches to the CSS grid classes, you should only use these widths for all of your styles, to ensure
   * that they always match the grid system. There are special occasions where images will not match the grid system,
   * they need special attention. For an example of this have a look at the stream images of the various image details
   * presentations.
   *
   * @var array
   */
  protected $span = [
     1 => 70,
     2 => 140,
     3 => 220,
     4 => 300,
     5 => 380,
     6 => 460,
     7 => 540,
     8 => 620,
     9 => 700,
    10 => 780,
    11 => 860,
    12 => 940,
  ];


  // ------------------------------------------------------------------------------------------------------------------- Abstract Methods


  /**
   * Generate all image styles for this concrete image.
   *
   * @return this
   */
  abstract public function generateImageStyles($source, $destination);

  /**
   * Move uploaded image to persistent storage.
   *
   * @param string $source
   *   Absolute path to the source image.
   * @param int $type
   *   PHP's <var>IMAGETYPE_*</var> constant for this image.
   * @param mixed $style [optional]
   *   A presentation class can pass along a style that should be generated right away and not delayed. This can be used
   *   if the subsequent page load needs the image for display. The generation of this style is optimized for speed and
   *   might look bad, but the file will be overriden by a better quality image as soon as all the delayed methods are
   *   executed.
   * @return this
   */
  abstract public function moveUploadedImage($source, $type, $style = null);


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Sanitizes a filename, replacing whitespace with dashes and transforming the string to lowercase.
   *
   * Removes special characters that are illegal in filenames on certain operating systems and special characters
   * requiring special escaping to manipulate at the command line. Replaces spaces and consecutive dashes with a single
   * dash. Trims period, dash und underscore from beginning and end of filename.
   *
   * @param string $filename
   *   The filename to be sanitized.
   * @return string
   *   The sanitized filename.
   */
  protected function filename($filename) {
    return mb_strtolower(trim(preg_replace("/[\s-]+/", "-", str_replace([ "?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", '"', "&", "$", "#", "*", "(", ")", "|", "~" ], "", $filename)), ".-_"));
  }

  /**
   * Instantiate new image.
   *
   * @param string $directory
   *   The relative path within the <code>uploads</code> directory without leading nor trailing slash of the original
   *   image.
   * @param string $name
   *   The image's name.
   * @param string $type
   *   The image's type, one of the <var>IMAGETYPE_*</var> constants.
   * @param array $styles
   *   Associative array containing all supported image styles.
   */
  protected function initImage($directory, $name, $type, array $styles) {
    $this->directory   = "uploads/{$directory}";
    $this->name        = $name;
    $this->styles      = $styles;
    $this->type        = $type;
  }

  /**
   * Get image attributes.
   *
   * @param string $style
   *   The desired image style's name, use the class constants of your concrete image.
   * @param array $attributes
   *   The (attributes) array containing other data to merge with the image style's attributes.
   * @return array
   *   Associative array containing all image information.
   */
  public function getImageStyle($style, array $attributes) {
    if (!isset($this->styles[$style])) {
      $this->styles = unserialize($this->styles);
    }
    return array_merge($attributes, $this->styles[$style]);
  }

  /**
   * Move uploaded image to persistent storage.
   *
   * @param string $source
   *   Absolute path to the source image.
   * @param int $type
   *   PHP's <var>IMAGETYPE_*</var> constant for this image.
   * @param string $destination
   *   The image's destination path.
   * @param mixed $style [optional]
   *   A presentation class can pass along a style that should be generated right away and not delayed. This can be used
   *   if the subsequent page load needs the image for display. The generation of this style is optimized for speed and
   *   might look bad, but the file will be overriden by a better quality image as soon as all the delayed methods are
   *   executed.
   * @return this
   */
  protected function moveUploaded($source, $type, $destination, $style = null) {
    $this->type = $type;
    if (!isset($this->imageStyles)) {
      $this->setImageStyles();
    }
    if (isset($style)) {
      exec("convert {$source} thumbnail {$this->imageStyles[$style]} {$destination} && chmod 777 {$destination}");
    }
    DelayedMethodCalls::stack($this, "generateImageStyles", [ $source, $destination ]);
    return $this;
  }

}
