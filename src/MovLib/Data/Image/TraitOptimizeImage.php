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

use \MovLib\Data\UnixShell as sh;

/**
 * Methods for image optimization.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
trait TraitOptimizeImage {

  /**
   * Optimize JPG image.
   *
   * @param string $jpg
   *   Absolute path to the JPG image.
   * @return this
   * @throws \RuntimeException
   */
  protected function optimizeJPG($jpg) {
    return $this;
  }

  /**
   * Optimize JPG images.
   *
   * @param array $jpgs
   *   Numeric array containing absolute paths to the JPG images that should be optimized.
   * @return this
   * @throws \RuntimeException
   */
  protected function optimizeJPGs(array $jpgs) {
    $c = count($jpgs);
    for ($i = 0; $i < $c; ++$i) {
      $this->optimizeJPG($jpgs[$i]);
    }
    return $this;
  }

  /**
   * Optimize PNG image.
   *
   * @param string $png
   *   Absolute path to the PNG image.
   * @return this
   * @throws \RuntimeException
   */
  protected function optimizePNG($png) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (!is_string($png)) {
      throw new \InvalidArgumentException("\$png must be of type string");
    }
    if (!is_file($png)) {
      throw new \InvalidArgumentException("\$png must be a valid file: '{$png}'");
    }
    if (substr($png, -3) != "png") {
      throw new \InvalidArgumentException("'{$png}' must have PNG extension");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    if (sh::execute("pngquant --ext '.png' --force --speed 1 {$png}") === false) {
      throw new \RuntimeException("Couldn't optimize '{$png}' with pngquant");
    }
    if (sh::execute("zopflipng {$png} {$png}") === false) {
      throw new \RuntimeException("Couldn't optimize '{$png}' with zopflipng");
    }
    return $this;
  }

  /**
   * Optimize PNG images.
   *
   * @param array $pngs
   *   Numeric array containing absolute paths to the PNG images that should be optimized.
   * @return this
   * @throws \RuntimeException
   */
  protected function optimizePNGs(array $pngs) {
    $c = count($pngs);
    for ($i = 0; $i < $c; ++$i) {
      $this->optimizePNG($pngs[$i]);
    }
    return $this;
  }


  /**
   * Optimize SVG image.
   *
   * @global \MovLib\Kernel $kernel
   * @param string $svg
   *   Absolute path to the SVG image.
   * @return this
   * @throws \RuntimeException
   */
  protected function optimizeSVG($svg) {
    global $kernel;
    if (sh::execute("svgo --input {$svg} --output {$svg}") === false) {
      throw new \RuntimeException("Couldn't minify '{$svg}'");
    }
    $kernel->compress($svg);
    return $this;
  }

  /**
   * Optimize SVG images.
   *
   * @param array $svgs
   *   Numeric array containing absolute paths to the SVG images that should be optimized.
   * @return this
   * @throws \RuntimeException
   */
  protected function optimizeSVGs(array $svgs) {
    $c = count($svgs);
    for ($i = 0; $i < $c; ++$i) {
      $this->optimizeSVG($svgs[$i]);
    }
    return $this;
  }

}
