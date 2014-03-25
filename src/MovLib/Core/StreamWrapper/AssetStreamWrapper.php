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
namespace MovLib\Core\StreamWrapper;

/**
 * Defines the asset stream wrapper for the <code>"asset://"</code> scheme.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class AssetStreamWrapper extends AbstractLocalStreamWrapper {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Associative array containing the cache busters for the various assets.
   *
   * <b>NOTE</b><br>
   * The cache buster checksums are generated offline for each release and directly inserted into this class definition.
   * Checksums are generated on the fly in a development environment.
   *
   * @var array
   */
  protected static $cacheBusters = [
    "css" => "{{ css_cache_buster }}",
    "jpg" => "{{ jpg_cache_buster }}",
    "js"  => "{{ js_cache_buster }}",
    "png" => "{{ png_cache_buster }}",
    "svg" => "{{ svg_cache_buster }}",
  ];


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get the external path of the given URI.
   *
   * @param string $uri [optional]
   *   The URI to get the external path for.
   * @return string
   *   The external path of the given URI.
   */
  public function getExternalPath($uri = null) {
    $target    = $this->getTarget($uri);
    $extension = pathinfo($target, PATHINFO_EXTENSION);

    // @devStart
    // @codeCoverageIgnoreStart
    if (!is_array(self::$cacheBusters[$extension])) {
      self::$cacheBusters[$extension] = [];
    }
    if (!isset(self::$cacheBusters[$extension][$target])) {
      self::$cacheBusters[$extension][$target] = md5_file($this->realpath());
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    return "/asset/{$target}?" . self::$cacheBusters[$extension][$target];
  }

  /**
   * @inheritdoc
   */
  public function getPath() {
    return "{$_SERVER["DOCUMENT_ROOT"]}/public/asset";
  }

}
