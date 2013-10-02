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


  /**
   * The image's name.
   *
   * @var string
   */
  protected $imageName;

  /**
   * The image's width.
   *
   * @var int
   */
  protected $imageWidth;

  /**
   * The image's height.
   *
   * @var int
   */
  protected $imageHeight;

  /**
   * The image's extension.
   *
   * @var string
   */
  protected $imageExtension;

  /**
   * The image's changed timestamp.
   *
   * @var int
   */
  protected $imageChanged;

  /**
   * The image's created timestamp.
   *
   * @var int
   */
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


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Convert source image to given width, height, optionally crop and use given style for storage.
   *
   * Only images that are big enough will be downscaled, if the source image is too small no conversion is performed.
   * Instead it's simply copied to the target location (specified by style). If you pass width and height the image will
   * keep its aspect ratio.
   *
   * @param string $source
   *   Absolute path to the source image for conversion.
   * @param mixed $style
   *   The style constant.
   * @param int $width
   *   The desired width of the converted image.
   * @param int $height [optional]
   *   The desired height of the converted image.
   * @param boolean $crop [optional]
   *   If set to <code>TRUE</code> the image will be resized first to width x height and then cropped to the center.
   * @return this
   */
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
   * Deletes the original image, all styles and the directory from the persistent storage.
   *
   * @return this
   */
  protected function deleteImageOriginalAndStyles() {
    $original = $this->getImagePath();
    if (is_file($original)) {
      unlink($original);
    }
    $originalDirectory = dirname($original);
    if (is_dir($originalDirectory)) {
      exec("rmdir -p {$originalDirectory}");
    }
    if (!is_array($this->imageStyles)) {
      $this->imageStyles = unserialize($this->imageStyles);
    }
    foreach ($this->imageStyles as $style => $info) {
      $style = $this->getImagePath($style);
      if (is_file($style)) {
        unlink($style);
      }
    }
    $styleDirectory = dirname($style);
    if (is_dir($styleDirectory)) {
      exec("rmdir -p {$styleDirectory}");
    }
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

  /**
   * Get the image style's attributes.
   *
   * This will add (or override) the width, height and src attributes in the attributes array you pass in.
   *
   * @param mixed $style
   *   The style for which you want the attributes.
   * @param array $attributes [optional]
   *   The attributes array you already have.
   * @return array
   *   The new or altered attributes array.
   */
  public function getImageStyleAttributes($style, array &$attributes = []) {
    if (!is_array($this->imageStyles)) {
      $this->imageStyles = unserialize($this->imageStyles);
    }
    return ($attributes += $this->imageStyles[$style]);
  }

  /**
   * Get the absolute path to the image.
   *
   * @param mixed $style [optional]
   *   The style for which you want the path, if no style is given (default) the path to the original file is returned.
   * @return string
   *   The absolute path to the image.
   */
  protected function getImagePath($style = null) {
    $root = "{$_SERVER["DOCUMENT_ROOT"]}/uploads/";
    if (!$style) {
      $root .= "originals/";
    }
    return $this->getImageURI($root, $style);
  }

  /**
   * Get the absolute (static) URL to the image.
   *
   * @param mixed $style [optional]
   *   The style for which you want the URL, if no style is given (default) the URL to the original file is returned.
   * @return string
   *   The absolute (static) URL to the image.
   */
  protected function getImageURL($style = null) {
    $root = $GLOBALS["movlib"]["static_domain"];
    if (!$style) {
      $root .= "originals/";
    }
    return $this->getImageURI($root, $style);
  }

  /**
   * Get a URI to the image from given root.
   *
   * @param string $root
   *   The URI root.
   * @param mixed $style [optional]
   *   The style for which you want the URI, if no style is given (default) the URI to the original file is returned.
   *   Note that you have to adapt your root!
   * @return string
   *   The absolute URI to the image.
   */
  private function getImageURI($root, $style = null) {
    if ($style) {
      $style = ".{$style}";
    }
    return "{$root}{$this->imageDirectory}/{$this->imageName}{$style}.{$this->imageExtension}";
  }

//  protected function moveUploadedImage($inputImage) {
//    exec(escapeshellcmd("convert {$inputImage->path} -strip +repage {$this->getImageURI("{$_SERVER["DOCUMENT_ROOT"]}/uplodas/originals/")} <&- 1<&- 2<&-"));
//    return $this;
//  }

}
