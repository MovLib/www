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
 * All available image widths as namespace constants.
 *
 * All are direct matches to the CSS grid classes, you should only use these widths for all of your styles, to ensure
 * that they always match the grid system. There are special occasions where images will not match the grid system,
 * they need special attention. For an example of this have a look at the stream images of the various image details
 * presentations.
 *
 * @internal The zero prefixing ensures natural sorting in IDEs.
 */
// @codeCoverageIgnoreStart
const S01 = 60;
const S02 = 140;
const S03 = 220;
const S04 = 300;
const S05 = 380;
const S06 = 460;
const S07 = 540;
const S08 = 620;
const S09 = 700;
const S10 = 780;
const S11 = 860;
const S12 = 940;
// @codeCoverageIgnoreEnd

use \MovLib\Core\Shell;
use \MovLib\Data\Image\ImageStyle;

/**
 * Default implementation for concrete classes that implement the read only image interface.
 *
 * @see \MovLib\Data\Image\ImageReadOnlyInterface
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
trait ImageReadOnlyTrait {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The image's changed date and time.
   *
   * @var \DateTime
   */
  protected $imageChanged;

  /**
   * Whether this image exists or not.
   *
   * @var boolean
   */
  public $imageExists = false;

  /**
   * The image's extension.
   *
   * @var string
   */
  protected $imageExtension;

  /**
   * The image's filename.
   *
   * @var string
   */
  protected $imageFilename;

  /**
   * The image's filesize in Bytes of the original.
   *
   * @var integer
   */
  protected $imageFilesize;

  /**
   * The image's height in Pixel of the original.
   *
   * @var integer
   */
  protected $imageHeight;

  /**
   * The image's placeholder URI (must be an SVG in the asset path).
   *
   * @var string
   */
  protected $imagePlaceholder = "asset://img/logo/vector.svg";

  /**
   * All available default styles that are always available for any concrete image.
   *
   * @var array
   */
  protected static $imageStyleRegistry = [ "s1" => \MovLib\Data\Image\S01, "s2" => \MovLib\Data\Image\S02 ];

  /**
   * The image's styles.
   *
   * @var array
   */
  protected $imageStyles;

  /**
   * The image's width in Pixel of the original.
   *
   * @var integer
   */
  protected $imageWidth;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Check if the image exists or if a placeholder should be used instead.
   *
   * @return boolean
   *   <code>TRUE</code> if the image exists, <code>FALSE</code> otherwise.
   */
  final public function imageExists() {
    return $this->imageExists;
  }

  /**
   * Get the image's directory URI.
   *
   * @return string
   *   The image's directory URI.
   */
  public function getImageDirectory() {
    /* @var $this \MovLib\Data\AbstractEntity */
    return "upload://{$this->getSingularKey()}";
  }

  /**
   * Get the image's URI.
   *
   * @return string
   *   The image's URI.
   */
  public function getImageURI() {
    return "{$this->imageDirectory}/{$this->imageFilename}.{$this->imageExtension}";
  }

  /**
   * Get the image's URL.
   *
   * @return string
   *   The image's URL.
   */
  public function getImageURL() {
    return "//{$this->config->hostnameStatic}/uploads/{$this->imageDirectory}/{$this->imageFilename}.{$this->imageExtension}?{$this->imageChanged->getTimestamp()}";
  }

  /**
   * Get the CLI resize arguments.
   *
   * @param integer|string $style
   *   The desired image's style. Either one of the CSS grid classes that are available in the concrete image's style
   *   registry, or an integer value that should be used for the width. The integer has to be between <code>5</code>
   *   and <code>5000</code>.
   * @param null|integer $height
   *   The desired image's height.
   * @param boolean $crop
   *   Whether to crop the image or not.
   * @return string
   *   The CLI resize arguments.
   */
  final protected function getImageResizeArguments($style, $height, $crop) {
    // The style is either a named style or the desired width of the image.
    if (isset(static::$imageStyleRegistry[$style])) {
      $width = static::$imageStyleRegistry[$style];
    }
    else {
      // @devStart
      // @codeCoverageIgnoreStart
      assert(
        filter_var($style, FILTER_VALIDATE_INT, [ "options" => [ "min_range" => 5, "max_range" => 5000 ]]) != false,
        "If the style isn't a valid style identifier it must be a valid integer that defines the width of the image."
      );
      $width = $style;
    }

    // The resize arguments depend on the crop setting, height can stay NULL.
    if ($crop) {
      return "'{$width}x{$height}>^' -gravity 'Center' -crop '{$width}x{$height}+0+0' +repage";
    }
    return "'{$width}x{$height}>'";
  }

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
  public function getImageStyle($style = "s2", $height = null, $crop = false) {
    $resizeArguments = $this->getImageResizeArguments($style, $height, $crop);

    // Directly deliver the image style if we have a cached version and the arguments that were used for resizing match
    // the arguments that were initially used to convert the image style.
    if (isset($this->imageStyles[$style]) && $this->imageStyles[$style]->resizeArguments == $resizeArguments) {
      return $this->imageStyles[$style];
    }

    $imageStyleURI = $this->getImageStyleURI($style);
    if (is_file($imageStyleURI) === false) {
      (new Shell())->execute("convert '{$this->fs->realpath($this->getImageURI())}' -filter Lanczos -resize '{$resizeArguments}' -quality 80 '{$this->fs->realpath($imageStyleURI)}'");
    }
    list($width, $height) = getimagesize($imageStyleURI);

    return ($this->imageStyles[$style] = new ImageStyle($this->getImageStyleURL($style), "", $width, $height, "", "", []));
  }

  /**
   * Get the image style's URI.
   *
   * @param integer|string $style
   *   The image style to get the URI for.
   * @return string
   *   The image style's URI.
   */
  public function getImageStyleURI($style) {
    return "{$this->imageDirectory}/{$this->imageFilename}.{$style}.{$this->imageExtension}";
  }

  /**
   * Get the image style's URL.
   *
   * @staticvar array $urls
   *   Used to cache already generated URLs.
   * @param integer|string $style
   *   The image style to get the URL for.
   * @return string
   *   The image style's URl.
   */
  public function getImageStyleURL($style) {
    return $this->fs->getExternalURL($this->getImageStyleURI($style), $this->imageChanged->getTimestamp());
  }

}
