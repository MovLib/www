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
 * Defines the base class for image entities.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractImageEntity extends \MovLib\Data\Image\AbstractReadOnlyImageEntity {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Global minimum height for uploaded images.
   *
   * @var integer
   */
  const IMAGE_MIN_HEIGHT = \MovLib\Data\Image\S02;

  /**
   * Global minimum width for uploaded images.
   *
   * @var integer
   */
  const IMAGE_MIN_WIDTH = \MovLib\Data\Image\S02;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Delete the original image and all of its styles.
   *
   * @return this
   */
  final protected function imageDelete() {
    unlink($this->imageGetURI());
    $this->imageDeleteStyles();
    $this->imageCacheBuster = $this->imageExtension = $this->imageFilename = $this->imageFilesize = $this->imageHeight = $this->imageWidth = null;
    return $this;
  }

  /**
   * Delete image style.
   *
   * @return this
   */
  final protected function imageDeleteStyle($style) {
    if (isset($this->imageStyles[$style])) {
      unset($this->imageStyles[$style]);
    }
    if (isset($this->imageStylesCache[$style])) {
      unset($this->imageStylesCache[$style]);
    }
    unlink($this->imageGetStyleURI($style));
    return $this;
  }

  /**
   * Delete image styles.
   *
   * @return this
   */
  final protected function imageDeleteStyles() {
    foreach ($this->imageGetEffects() as $style => $imageEffect) {
      unlink($this->imageGetStyleURI($style));
    }
    $this->imageStylesCache = $this->imageStyles = null;
    return $this;
  }

}
