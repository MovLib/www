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

use \MovLib\Component\URL;

/**
 * Defines the asset stream wrapper for the <code>"asset://"</code> scheme.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class AssetStreamWrapper extends AbstractLocalStreamWrapper implements ExternalStreamWrapperInterface {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * {@inheritdoc}
   */
  const name = "AssetStreamWrapper";
  // @codingStandardsIgnoreEnd

  /**
   * {@inheritdoc}
   */
  const SCHEME = "asset";


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
    "css" => [ /* {{ css_cache_buster }} */ ],
    "jpg" => [ /* {{ jpg_cache_buster }} */ ],
    "js"  => [ /* {{ js_cache_buster }}  */ ],
    "png" => [ /* {{ png_cache_buster }} */ ],
    "svg" => [ /* {{ svg_cache_buster }} */ ],
  ];


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  public function getExternalURL($uri = null, $cacheBuster = null) {
    // We use the final target as cache buster key because it's shorter and still unique.
    $target = $this->getTarget($uri);

    if (!$cacheBuster) {
      $extension = pathinfo($target, PATHINFO_EXTENSION);
      // @devStart
      // @codeCoverageIgnoreStart
      if (empty(self::$cacheBusters[$extension][$target])) {
        self::$cacheBusters[$extension][$target] = md5_file($this->realpath($uri));
      }
      // @codeCoverageIgnoreEnd
      // @devEnd
      $cacheBuster = self::$cacheBusters[$extension][$target];
    }

    // An asset always has a cache buster, also note that this is the last place were we can properly encode the path.
    $target = URL::encodePath($target);
    return "/asset/{$target}?{$cacheBuster}";
  }

  /**
   * {@inheritdoc}
   * @staticvar string $path
   *   Used to cache the internal path after the first build because it will never change during a single request.
   */
  public function getPath() {
    static $path;
    return $path ?: ($path = "{$this::$fileSystem->documentRoot}/var/public/asset");
  }

}
