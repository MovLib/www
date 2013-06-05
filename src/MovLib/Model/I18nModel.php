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

namespace MovLib\Model;

use \Locale;
use \MovLib\Exception\ErrorException;
use \MovLib\Model\AbstractModel;
use \MovLib\Utility\DelayedLogger;

/**
 * Description of I18nModel
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Franz Torghele <ftorghele.mmt-m:2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class I18nModel extends AbstractModel {

  /**
   * Get a translated message from the database.
   *
   * @global \MovLib\Utility\I18n $i18n
   *   The global i18n instance.
   * @param string $message
   *   The message which acts as key.
   * @param string $languageCode
   *   The ISO 639-1 alpha-2 language code in which the message should be returned.
   * @return string
   *   Message in desired language or <var>$message</var> if no translation exist.
   */
  public function getMessage($message, $languageCode) {
    global $i18n;
    if ($languageCode === $i18n->getDefaultLanguageCode()) {
      return $message;
    }
    try {
      return $this->query(
        "SELECT
          COLUMN_GET(`dyn_translations`, '{$languageCode}' AS BINARY) AS `translation`
        FROM `messages`
          WHERE `message` = ?
        LIMIT 1", "s", [ $message ]
      )[0]["translation"];
    } catch (ErrorException $e) {
      $language = Locale::getDisplayName($languageCode, $i18n->getDefaultLanguageCode());
      DelayedLogger::log("Could not find {$language} translation for message: '{$message}'", DelayedLogger::LEVEL_INFO);
    }
    return $message;
  }

  /**
   * Get a translated route from the database.
   *
   * @global \MovLib\Utility\I18n $i18n
   *   The global i18n instance.
   * @param string $route
   *   The route which acts as key.
   * @param type $languageCode
   *   The ISO 639-1 alpha-2 language code in which the message should be returned.
   * @return string
   *   Route in desired language or <var>$route</var> if no translation exist.
   */
  public function getRoute($route, $languageCode = "en") {
    global $i18n;
    if ($languageCode === $i18n->getDefaultLanguageCode()) {
      return $route;
    }
    try {
      return $this->query(
        "SELECT
          COLUMN_GET(`dyn_translations`, '{$languageCode}' AS BINARY) AS `translation`
        FROM `routes`
          WHERE `route` = ?
        LIMIT 1", "s", [ $route ]
      )[0]["translation"];
    } catch (ErrorException $e) {
      $language = Locale::getDisplayName($languageCode, $i18n->getDefaultLanguageCode());
      DelayedLogger::log("Could not find {$language} translation for route: '{$route}'", DelayedLogger::LEVEL_INFO);
    }
  }

  /**
   * Set or update a message's translation(s).
   *
   * @param string $message
   *   The message which acts as key.
   * @param array $translations
   *  An array with translations of the message.
   * @param string $comment
   *   [Optional] Comment for translators to help them translating the message.
   * @param string $oldMessage
   *   [Optional] The old message of this translation if updating from old branch to new branch.
   * @return $this
   */
  public function setMessage($message, $translations, $comment = null, $oldMessage = null) {
    // @todo Wouldn't it be better to have sepearte methods? insertMessage(), insertMessages(), updateMessage(), updateMessages()
    try {
      $messageId = $this->query("SELECT `message_id` FROM `messages` WHERE `message` = ? LIMIT 1", "s", [ $message ])[0]["message_id"];
      // @todo Record exists: UPDATE
    } catch (ErrorException $e) {
      // @todo No record exists: INSERT
    }
    return $this;
  }

  /**
   * Set or update a route's translation(s).
   *
   * @param string $route
   *   The route which acts as key.
   * @param array $translations
   *  An array with translations of the route.
   * @param string $comment
   *   [Optional] Comment for translators to help them translating the route.
   * @param string $oldRoute
   *   [Optional] The old route of this translation if updating from old branch to new branch.
   * @return $this
   */
  public function setRoute($route, $translations, $comment = null, $oldRoute = null) {
    // @todo Wouldn't it be better to have sepearte methods? insertRoute(), insertRoute(), updateRoute(), updateRoute()
    try {
      $routeId = $this->query("SELECT `route_id` FROM `routes` WHERE `route` = ? LIMIT 1", "s", [ $route ])[0]["route_id"];
      // @todo Record exists: UPDATE
    } catch (ErrorException $e) {
      // @todo No record exists: INSERT
    }
    return $this;
  }

}