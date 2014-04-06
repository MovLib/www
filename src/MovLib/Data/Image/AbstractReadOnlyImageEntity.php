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

use \MovLib\Data\Image\ImageEffect;
use \MovLib\Data\Image\ImageStyle;
use \MovLib\Data\Image\ImageStylePlaceholder;

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

/**
 * Defines the base class for read only images.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class AbstractReadOnlyImageEntity extends \MovLib\Data\AbstractEntity {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The image's alternative text in the current locale.
   *
   * @var string
   */
  public $imageAlternativeText;

  /**
   * The image's cache buster MD5 hash.
   *
   * @var string
   */
  public $imageCacheBuster;

  /**
   * The image's directory URI.
   *
   * @var string
   */
  public $imageDirectory;

  /**
   * The image's extension without dot.
   *
   * @var string
   */
  public $imageExtension;

  /**
   * The image's filename.
   *
   * @var string
   */
  public $imageFilename;

  /**
   * The image's filesize in Bytes of the original.
   *
   * @var integer
   */
  public $imageFilesize;

  /**
   * The image's height in Pixel of the original.
   *
   * @var integer
   */
  public $imageHeight;

  /**
   * The image's placeholder URI.
   *
   * <b>NOTE</b><br>
   * The placeholder URI must point to an asset and must be of type SVG.
   *
   * @var string
   */
  protected $imagePlaceholder = "asset://img/logo/vector.svg";

  /**
   * The image's styles.
   *
   * @var array
   */
  protected $imageStyles;

  /**
   * The image's styles cache.
   *
   * @var array
   */
  protected $imageStylesCache;

  /**
   * The image's width in Pixel of the original.
   *
   * @var integer
   */
  public $imageWidth;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Generate image style by applying given image effect.
   *
   * @param string $style
   *   The name of the image style.
   * @param \MovLib\Data\Image\ImageEffect $imageEffect
   *   The image effect to apply.
   * @return type
   * @throws \RuntimeException
   *   If applying of the image effect fails.
   */
  final protected function imageGenerateStyle($style, \MovLib\Data\Image\ImageEffect $imageEffect) {
    $source                            = $this->imageGetURI();
    $destination                       = $this->imageGetStyleURI($style);
    $imageEffect->apply($this->fs, $source, $destination);
    list($width, $height)              = getimagesize($destination);
    $this->imageStyles[$style]         = new ImageStyle($destination, $width, $height);
    $this->imageStyles[$style]->effect = $imageEffect;
    return $this->imageStyles[$style];
  }

  /**
   * Generate image styles.
   *
   * @return this
   * @throws \RuntimeException
   *   If applying of the image effect fails.
   */
  final protected function imageGenerateStyles() {
    foreach ($this->imageGetEffects() as $style => $imageEffect) {
      $this->imageGenerateStyle($style, $imageEffect);
    }
    $this->imageSaveStyles();
    return $this;
  }

  /**
   * Get the available image effects.
   *
   * @return array
   *   The available image effects.
   */
  protected function imageGetEffects() {
    return [
      "s1" => new ImageEffect(\MovLib\Data\Image\S01),
      "s2" => new ImageEffect(\MovLib\Data\Image\S02),
    ];
  }

  /**
   * Get the image's style.
   *
   * @param string $style [optional]
   *   The desired image style, defaults to <code>"s2"</code>.
   * @return \MovLib\Data\Image\ImageStyle
   *   The desired image's style.
   */
  final public function imageGetStyle($style = "s2") {
    // @devStart
    // @codeCoverageIgnoreStart
    $this->log->debug("Getting image style.", [ "style" => $style ]);
    // @codeCoverageIgnoreEnd
    // @devEnd
    // Nothing to do if we have this style already cached.
    if (isset($this->imageStylesCache[$style])) {
      return $this->imageStylesCache[$style];
    }

    // Check if the image exists.
    if ($this->imageCacheBuster) {
      // @devStart
      // @codeCoverageIgnoreStart
      $this->log->debug("Found cache buster string.");
      if (!is_file($this->imageGetStyleURI($style))) {
        $this->log->info("Generating all image styles because file is missing from storage!");
        $this->imageGenerateStyles();
        $this->imageSaveStyles();
      }
      // @codeCoverageIgnoreEnd
      // @devEnd

      // Check if we have data from the database for this style.
      if (isset($this->imageStyles[$style])) {
        // @devStart
        // @codeCoverageIgnoreStart
        $this->log->debug("Using existing image style from database.");
        // @codeCoverageIgnoreEnd
        // @devEnd
        $this->imageStylesCache[$style] = $this->imageStyles[$style];
      }
      // If not try to recover.
      //
      // @todo This shouldn't be necessary at all!
      else {
        $this->imageGenerateStyles();
        $this->log->info("Missing image style from persistent storage, re-applying image effect.");
        $this->imageStylesCache[$style] = $this->imageGenerateStyle($style, $this->imageGetEffects()[$style]);
        $this->imageSaveStyles();
      }
      $this->imageStylesCache[$style]->url = $this->fs->getExternalURL($this->imageGetStyleURI($style), $this->imageCacheBuster);
    }
    else {
      // @devStart
      // @codeCoverageIgnoreStart
      $this->log->info("Couldn't find image, using placeholder.");
      // @codeCoverageIgnoreEnd
      // @devEnd
      $this->imageStylesCache[$style]      = new ImageStylePlaceholder($this->imageGetEffects()[$style]->width);
      $this->imageStylesCache[$style]->url = $this->fs->getExternalURL($this->imagePlaceholder);
    }

    // Export dynamic properties to cached image style version and return.
    $this->imageStylesCache[$style]->alt   = $this->imageAlternativeText;
    $this->imageStylesCache[$style]->route = $this->route;
    return $this->imageStylesCache[$style];
  }

  /**
   * Get the URI of the image or image style.
   *
   * @param string $style [optional]
   *   The style to get the URI for, defaults to <code>NULL</code> and returns the URI of the non-resized image.
   * @return string
   *   The URI of the image or image style.
   */
  final protected function imageGetStyleURI($style = null) {
    return "{$this->imageDirectory}/{$this->imageFilename}.{$style}.{$this->imageExtension}";
  }

  /**
   * Get the URI of the original image.
   *
   * @return string
   *   The URI of the original image.
   */
  final protected function imageGetURI() {
    return str_replace("upload://", "dr://var/lib/uploads/", "{$this->imageDirectory}/{$this->imageFilename}.{$this->imageExtension}");
  }

  /**
   * Save the image styles to persistent storage.
   *
   * @return this
   */
  protected function imageSaveStyles() {
    $styles = serialize($this->imageStyles);
    $stmt   = $this->getMySQLi()->prepare("UPDATE `{$this->getPluralKey()}` SET `styles` = ? WHERE `id` = ?");
    $stmt->bind_param("sd", $styles, $this->id);
    $stmt->execute();
    $stmt->close();
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  protected function init() {
    // @devStart
    // @codeCoverageIgnoreStart
    foreach ([ "AlternativeText", "Directory" ] as $property) {
      $property = "image{$property}";
      assert(!empty($this->$property), "You must set the \${$property} property in your class " . static::class . ".");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    $this->imageStyles && ($this->imageStyles = unserialize($this->imageStyles));
    return parent::init();
  }

}
