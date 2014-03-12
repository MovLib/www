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
namespace MovLib\Data;

/**
 * Description of Release
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Release extends \MovLib\Data\Database {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The release's unique identifier.
   *
   * @var integer
   */
  public $id;

  /**
   * The master release's unique identifier.
   *
   * @var integer
   */
  public $masterReleaseId;

  /**
   * Whether it is a cuted version or not.
   *
   * @var boolean
   */
  public $isCut;

  /**
   * The International Article Number (EAN).
   *
   * @var null|string
   */
  public $ean;

  /**
   * The length of this release without the credits.
   *
   * @var null|string
   */
  public $length;

  /**
   * The length of this release with the credits.
   *
   * @var null|string
   */
  public $lengthCredits;

  /**
   * The length of this release's bonus material.
   *
   * @var null|string
   */
  public $lengthBonus;

  /**
   * The release´s translatable extras free text field.
   *
   * @var string
   */
  public $dynExtras;

  /**
   * The translatable release notes for this release, if there is more than one release in the master release.
   *
   * @var string
   */
  public $dynNotes;

  /**
   * The unique ID of the release´s aspect ratio.
   *
   * @var integer
   */
  public $aspectRatioId;

  /**
   * The packaging´s unique ID this release is boxed in.
   *
   * @var integer
   */
  public $packagingId;

  /**
   * The release´s type in lowercase letters, e.g. dvd or bluray.
   *
   * @var string
   */
  public $type;

  /**
   * The release´s type specific fields in serialized igbinary format.
   *
   * @var string
   */
  public $binTypeData;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods
  

  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get random release identifier.
   *
   * @global \MovLib\Data\Database $db
   * @return integer|null
   *   Random release identifier, or <code>NULL</code> on failure.
   * @throws \MovLib\Exception\DatabaseException
   */
  public static function getRandomReleaseId() {
    global $db;
    $result = $db->query("SELECT `id` FROM `master_releases` ORDER BY RAND() LIMIT 1")->get_result()->fetch_row();
    if (isset($result[0])) {
      return $result[0];
    }
  }

}
