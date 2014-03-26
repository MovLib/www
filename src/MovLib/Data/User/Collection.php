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
namespace MovLib\Data\User;

/**
 * Represents the movie collection of an unser.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Collection extends \MovLib\Data\Database {
 

  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The user's unique id.
   *
   * @var integer
   */
  public $userId;
  
  
  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Handling of movie collections.
   * 
   */
  public function __construct() {
    $this->userId = $session->userId;
  }
  
  /**
   * Get all available media conditions.
   * 
   * @staticvar array $mediaConditions
   *   Associative array with translated media conditions.
   * @return array
   *   Array with translated media conditions.
   */
  public static function getMediaConditions() {
    static $mediaConditions = null;
    if (!isset($mediaConditions[$i18n->languageCode])) {
      $mediaConditions[$i18n->languageCode] = [
        $i18n->t("Mint (M)"),
        $i18n->t("Near Mint (NM or M-)"),
        $i18n->t("Very Good Plus (VG+)"),
        $i18n->t("Very Good (VG)"),
        $i18n->t("Good Plus (G+)"),
        $i18n->t("Good (G)"),
        $i18n->t("Fair (F)"),
        $i18n->t("Poor (P)")
      ];
    }
    return $mediaConditions[$i18n->languageCode];
  }
  
  /**
   * Get all available sleeve conditions.
   * 
   * @staticvar array $sleeveConditions
   *   Associative array with translated sleeve conditions.
   * @return array
   *   Array with translated sleeve conditions.
   */
  public static function getSleeveConditions() {
    static $sleeveConditions = null;
    if (!isset($sleeveConditions[$i18n->languageCode])) {
      $sleeveConditions[$i18n->languageCode] = [
        $i18n->t("Generic"),
        $i18n->t("No Cover"),
        $i18n->t("Mint (M)"),
        $i18n->t("Near Mint (NM or M-)"),
        $i18n->t("Very Good Plus (VG+)"),
        $i18n->t("Very Good (VG)"),
        $i18n->t("Good Plus (G+)"),
        $i18n->t("Good (G)"),
        $i18n->t("Fair (F)"),
        $i18n->t("Poor (P)")
      ];
    }
    return $sleeveConditions[$i18n->languageCode];
  }
}
