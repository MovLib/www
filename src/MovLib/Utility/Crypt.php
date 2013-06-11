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
namespace MovLib\Utility;

/**
 * Various crypting methods.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Crypt {

  /**
   * Get randomly generated hash.
   *
   * The collision probability of SHA1 is extremely, extremely low. There is absolutely no need to generate some special
   * hash based on environment values or anything else. It's impossible to guess the hash and nearly impossible that the
   * hash collides with another hash. If you still have concerns, read the following:
   * {@link http://stackoverflow.com/a/4014407/1251219}
   *
   * SHA1 collision probability = (10^9)^2 / 2^(160+1) = 4.32 * 10^-31
   *
   * @see uniqid()
   * @see mt_rand()
   * @see mt_getrandmax()
   * @see hash_algos()
   * @param string $algo
   *   [Optional] The algorithm that should be used to generate the random hash, defaults to SHA256. To retrieve a list
   *   of available hashing algorithms call <code>hash_algos()</code>.
   * @return string
   *   A randomly generated hash string.
   */
  public static function getRandomHash($algo = "sha1") {
    return hash($algo, uniqid() . mt_rand(0, mt_getrandmax()));
  }

}
