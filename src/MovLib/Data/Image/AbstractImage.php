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

  protected $imageName;

  protected $imageWidth;

  protected $imageHeight;

  protected $imageExtension;

  protected $imageChanged;

  protected $imageCreated;

  /**
   * Flag indicating whetever this image exists or not.
   *
   * @var boolean
   */
  public $imageExists = false;

  /**
   * All available styles information.
   *
   * @var array
   */
  protected $imageStyles;


  // ------------------------------------------------------------------------------------------------------------------- Private Properties


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
   * Generate a single image style.
   *
   * @param string $source
   *   The absolute path to the source image.
   * @param int $type
   *   The PHP <var>IMAGETYPE_*</var> constant.
   * @return this
   */
//  abstract public function generateImageStyle($source, $type);

  /**
   * Move the uploaded image to it's persistent storage and generate all image styles.
   *
   * @param string $source
   *   The absolute path to the source image.
   * @param int $type
   *   The PHP <var>IMAGETYPE_*</var> constant.
   * @param int $width
   *   The source image's width.
   * @param int $height
   *   The source image's height.
   * @return this
   */
//  abstract public function moveUploadedImageAndGenerateStyles($source, $type, $width, $height);


  // ------------------------------------------------------------------------------------------------------------------- Methods


  protected function convert($source, $style, $width, $height = null, $crop = false) {
    if ($crop === true) {
      $args = "'{$width}x{$height}>^' -gravity 'Center' -crop '{$width}x{$height}+0+0' +repage";
    }
    else {
      $args = "'{$width}x{$height}>'";
    }
    exec("convert '{$source}' -define 'filter:support=2.5' -filter 'Lagrange' -quality 75 -resize {$args} '{$this->getImagePath($style)}'");
    return $this;
  }

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

  public function getImageStyleAttributes($style, &$attributes) {
    if (!is_array($this->imageStyles)) {
      $this->imageStyles = unserialize($this->imageStyles);
    }
    return ($attributes += $this->imageStyles[$style]);
  }

  protected function getImagePath($style = null) {
    return $this->getImageURI("{$_SERVER["DOCUMENT_ROOT"]}/uploads/", $style);
  }

  protected function getImageSrc($style = null) {
    return $this->getImageURI($GLOBALS["movlib"]["static_domain"], $style);
  }

  private function getImageURI($root, $style = null) {
    if ($style) {
      $style = ".{$style}";
    }
    return "{$root}{$this->imageDirectory}/{$this->imageName}{$style}{$this->imageExtension}";
  }

//  protected function moveUploadedImage($inputImage) {
//    exec(escapeshellcmd("convert {$inputImage->path} -strip +repage {$this->getImageURI("{$_SERVER["DOCUMENT_ROOT"]}/uplodas/originals/")} <&- 1<&- 2<&-"));
//    return $this;
//  }

}
