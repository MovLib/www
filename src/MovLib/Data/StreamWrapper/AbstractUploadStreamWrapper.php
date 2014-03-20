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
namespace MovLib\Data\StreamWrapper;

use \MovLib\Data\URL;

/**
 * Base class for stream wrappers that handle uploads.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractUploadStreamWrapper extends \MovLib\Data\StreamWrapper\AbstractLocalStreamWrapper {


  // ------------------------------------------------------------------------------------------------------------------- Abstract Methods


  /**
   * Get the stream wrappers external path.
   *
   * @return string
   *   The stream wrappers external path.
   */
  abstract public function getExternalPath();


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get the web accessible URL of the file.
   *
   * @global \MovLib\Kernel $kernel
   * @staticvar array $urls
   *   Used to cache already generated external URLs.
   * @return string
   *   The web accessible URL of the file.
   */
  public function getExternalURL() {
    static $externalPath = null, $urls = [];

    if (!$externalPath) {
      $externalPath = $this->getExternalPath();
    }

    if (isset($urls[$this->uri])) {
      return $urls[$this->uri];
    }

    global $kernel;
    $target           = URL::encodePath($this->getTarget());
    $urls[$this->uri] = "//{$kernel->domainStatic}{$externalPath}/{$target}";

    var_dump(static::class);
    var_dump($externalPath);
    var_dump($urls);

    return $urls[$this->uri];
  }

}
