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

use \MovLib\Data\Image\ImageStyle;

/**
 * Defines the image effect object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class ImageResizeEffect extends \MovLib\Data\Image\AbstractImageEffect {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Whether this effect requires cropping or not.
   *
   * @var boolean
   */
  public $crop = false;

  /**
   * The image effects filter.
   *
   * @var string
   */
  public $filter = "Lanczos";

  /**
   * The image effect's height in Pixel.
   *
   * @var null|integer
   */
  public $height;

  /**
   * The image effect's quality.
   *
   * @var integer
   */
  public $quality = 80;

  /**
   * The image effect's width in Pixel.
   *
   * @var integer
   */
  public $width;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new image effect object.
   *
   * @param integer $width
   *   The image effect's width.
   * @param integer $height [optional]
   *   The image effect's height, defaults to <code>NULL</code>.
   * @param boolean $crop [optional]
   *   Whether this image effect requires cropping or not, defaults to <code>FALSE</code>.
   */
  public function __construct($width, $height = null, $crop = false) {
    $this->crop   = $crop;
    $this->height = $height;
    $this->width  = $width;
  }

  /**
   * {@inheritdoc}
   */
  public function __sleep() {
    return [ "crop", "filter", "height", "quality", "styleName", "width" ];
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  protected function convert(\MovLib\Core\Shell $shell, $source, $destination) {
    // Build the resize argument for ImageMagick.
    if ($this->crop) {
      $resize = "'{$this->width}x{$this->height}>^' -gravity 'Center' -crop '{$this->width}x{$this->height}+0+0' +repage";
    }
    else {
      $resize = "'{$this->width}x{$this->height}>'";
    }

    // Only generate the style if it doesn't exist yet or the existing style differs from us.
    if (!isset($this->image->imageStyles[$this->styleName]) || $this->image->imageStyles[$this->styleName]->effect != $this) {
      // Try to resize the source image.
      $shell->execute("convert {$source} -filter '{$this->filter}' -resize {$resize} -quality {$this->quality} {$destination}");

      // Export this image style to the concrete image's styles array for persistent storage. Note that we have to read
      // the actual width and height before creating the image style, because we won't upscale any images we can't be
      // certain that our own width and height were applied to the image (plus the height might be NULL).
      list($width, $height) = getimagesize($destination);
      $this->image->imageStyles[$this->styleName] = new ImageStyle($destination, $width, $height);
      $this->image->imageStyles[$this->styleName]->effect = $this;
    }

    return $this;
  }

}
