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

use \MovLib\Data\User\User;

/**
 * Deletion related methods.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class DeletionRequest {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Deletion reason <b>other</b>.
   *
   * @var integer
   */
  const REASON_OTHER = 1;

  /**
   * Deletion reason <b>duplicate</b>.
   *
   * @var integer
   */
  const REASON_DUPLICATE = 2;

  /**
   * Deletion reason <b>spam</b>.
   *
   * @var integer
   */
  const REASON_SPAM = 3;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The deletion request's creation datetime as UNIX timestamp.
   *
   * @var integer
   */
  public $created;

  /**
   * The deletion request's unique identifier.
   *
   * @var integer
   */
  public $id;

  /**
   * The deletion request's additional information.
   *
   * The content of this value varies depending on the given reason. As of now we have two reasons that have additional
   * information:
   * <ul>
   *   <li>Duplicate: the info property contains the full URL of the already available content</li>
   *   <li>Other: user supplied HTML text containing an explanation for the deletion request, please note that the text
   *   is only available in the language the user has supplied</li>
   * </ul>
   *
   * @var null|string
   */
  public $info;

  /**
   * The deletion request's language code.
   *
   * Contains the ISO alpha-2 code of the language and is set according to the system language that was used to create
   * the deletion request.
   *
   * @var string
   */
  public $languageCode;

  /**
   * The deletion request's translated reason text.
   *
   * @var string
   */
  public $reason;

  /**
   * The deletion request's reason identifier.
   *
   * @var integer
   */
  public $reasonId;

  /**
   * The deletion request's deletion page from where the user requested the deletion.
   *
   * Associative array where the key is the system langauge code (ISO alpha-2) and the value the absolute URL to the
   * page. All links point directly to the deletion page of the content.
   *
   * @var array
   */
  public $routes;

  /**
   * The user who created the deletion request.
   *
   * @var \MovLib\Data\User\User
   */
  public $user;

  /**
   * The user's unique identifier.
   *
   * @var integer
   */
  protected $userId;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new Deletion Request.
   *
   * @global \MovLib\Data\Database $db
   * @param integer $id
   *   The deletion requests unique identifier.
   * @throws \OutOfBoundsException
   */
  public function __construct($id = null) {
    global $db;

    // Only attempt to load the deletion request from the database if an identifier was passed to the constructor.
    if ($id) {
      $this->id = $id;
      $stmt     = $db->query(
        "SELECT
          `user_id`,
          UNIX_TIMESTAMP(`created`),
          `language_code`,
          `reason_id`,
          `routes`,
          `info`
        FROM `deletion_requests`
        WHERE `id` = ?
        LIMIT 1",
        "d",
        [ $this->id ]
      );
      $stmt->bind_result($this->userId, $this->created, $this->languageCode, $this->reasonId, $this->routes, $this->info);
      if (!$stmt->fetch()) {
        throw new \OutOfBoundsException("Couldn't find deletion request for ID '{$this->id}'");
      }
      $stmt->close();
    }

    // If we have an identifier at this point, either passed to the constructor or set by PHP, load additional data
    // that corresponds to this deletion request.
    if ($this->id) {
      $this->user   = new User(User::FROM_ID, $this->userId);
      $this->routes = unserialize($this->routes);
      $this->reason = self::getTypes()[$this->reasonId];
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  protected static function checkReasonAndLanguage($reasonId, $languageCode, &$query, &$types, &$params) {
    if ($reasonId && $languageCode) {
      $query .= " WHERE `reason_id` = ? AND `language_code` = ?";
      $types  = "is";
      $params = [ $reasonId, $languageCode ];
    }
    elseif ($reasonId) {
      $query .= " WHERE `reason_id` = ?";
      $types  = "i";
      $params = [ $reasonId ];
    }
    elseif ($languageCode) {
      $query .= " WHERE `language_code` = ?";
      $types  = "s";
      $params = [ $languageCode ];
    }
  }

  /**
   * Get total deletion request count for the current language.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @param integer $reasonId [optional]
   *   The result will be filtered by this reason if passed.
   * @param string $languageCode [optional]
   *   The result will be filtered by this language if passed.
   * @return integer
   *   The total deletion request count for the current language.
   * @throws \MovLib\Exception\DatabaseException
   */
  public static function getCount($reasonId = null, $languageCode = null) {
    global $db;
    $query = "SELECT COUNT(*) FROM `deletion_requests`";
    self::checkReasonAndLanguage($reasonId, $languageCode, $query, $types, $params);
    return $db->query($query, $types, $params)->get_result()->fetch_row()[0];
  }

  /**
   * Get all deletion requests ordered by creation date, alternatively filtered by reason or language.
   *
   * @global \MovLib\Data\Database $db
   * @param integer $offset
   *   The offset, usually provided by the pagination trait.
   * @param integer $rowCount
   *   The row count, usually provided by the pagination trait.
   * @param integer $reasonId [optional]
   *   The result will be filtered by this reason if passed.
   * @param string $languageCode [optional]
   *   The result will be filtered by this language if passed.
   * @return \mysqli_result
   *   The mysqli result of the query.
   * @throws \MovLib\Exception\DatabaseException
   */
  public static function getResult($offset, $rowCount, $reasonId = null, $languageCode = null) {
    global $db;
    $query =
      "SELECT
        `id`,
        `user_id` AS `userId`,
        UNIX_TIMESTAMP(`created`) AS `created`,
        `language_code` AS `languageCode`,
        `reason_id` AS `reasonId`,
        `routes`,
        `info`
      FROM `deletion_requests`"
    ;
    self::checkReasonAndLanguage($reasonId, $languageCode, $query, $types, $params);
    $params[] = $rowCount;
    $params[] = $offset;
    return $db->query("{$query} ORDER BY `created` DESC LIMIT ? OFFSET ?", "{$types}ii", $params)->get_result();
  }

  /**
   * Get all available deletion types.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @staticvar array $types
   *   Caching variable.
   * @return array
   *   Associative array where the key is the deletion type's unique identifier and the value the translated name of
   *   the deletion type.
   */
  public static function getTypes() {
    global $i18n, $kernel;
    static $types = null;
    if (!$types) {
      $types = require "{$kernel->pathTranslations}/deletion_reasons/{$i18n->locale}.php";
    }
    return $types;
  }

  /**
   * Request deletion of content.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\User\Session $session
   * @param integer $reasonId
   *   The deletion request's unique reason identifier, use the class constants.
   * @param null|string $info
   *   The user supplied information.
   * @param array $languageLinks
   *   The language links array containing all translated routes for this deletion request.
   * @return integer
   *   The unique
   * @throws \MovLib\Exception\DatabaseException
   */
  public static function request($reasonId, $info, $languageLinks) {
    global $db, $i18n, $session;
    return $db->query(
      "INSERT INTO `deletion_requests` (`user_id`, `info`, `language_code`, `reason_id`, `routes`) VALUES (?, ?, ?, ?, ?)",
      "dssss",
      [ $session->userId, $info, $i18n->languageCode, $reasonId, serialize($languageLinks) ]
    )->insert_id;
  }

}
