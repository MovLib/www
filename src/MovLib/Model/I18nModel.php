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

use \MovLib\Exception\ErrorException;
use \MovLib\Model\AbstractModel;
use \MovLib\Utility\DelayedLogger;
use \MovLib\Utility\DelayedMethodCalls;

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
   * @param string $message
   *   The message which acts as key.
   * @param string $languageCode
   *   ISO 639-1 alpha-2 language code for which the translations should be fetched.
   * @param array $options
   *   Associative array to overwrite the default options used in this method. Possible keys are:
   *   <ul>
   *     <li><tt>comment</tt>: default is <tt>NULL</tt>.</li>
   *     <li><tt>old_message</tt>: default is <tt>NULL</tt>.</li>
   *   </ul>
   * @return string
   *   Message in desired language or <var>$message</var> if no translation exist.
   */
  public function getMessage($message, $languageCode, $options) {
    try {
      // We have to select the ID as well, otherwise we wouldn't know if the exception happened because of the message
      // being not present in the database or if we're simply missing the translation for it.
      return $this->query(
        "SELECT
          `message_id`,
          COLUMN_GET(`dyn_translations`, '{$languageCode}' AS BINARY) AS `translation`
        FROM `messages`
          WHERE `message` = ?
          LIMIT 1",
        "s",
        [ $message ]
      )[0]["translation"];
    } catch (ErrorException $e) {
      DelayedLogger::log("Could not find {$languageCode} translation for message: '{$message}'", E_NOTICE);
      DelayedMethodCalls::stack($this, "insertMessage", [ $message, $options ]);
      return $message;
    }
  }

  public function getRoute($route, $languageCode, $options) {
    //
    // @todo Create generic translation method based on the code from aboves getMessage() method and call that generic
    // method from here and the above one. Think DRY, generic, performant, and smart!
    //
    //return $this->method($route, $languageCode, $options);
    return $route;
  }

  /**
   * Insert a message for translation into the messages database table.
   *
   * @param string $message
   *   The message to insert.
   * @param array $options
   *   Associative array to overwrite the default options used in this method. Possible keys are:
   *   <ul>
   *     <li><tt>comment</tt>: default is <tt>NULL</tt>.</li>
   *     <li><tt>old_message</tt>: default is <tt>NULL</tt>.</li>
   *   </ul>
   * @return \MovLib\Model\I18nModel
   */
  public function insertMessage($message, $options) {
    $issetComment = isset($options["comment"]);
    self::$mysqli->affected_rows = 0;
    if (isset($options["old_message"])) {
      if ($issetComment) {
        $this->query("UPDATE `messages` SET `message` = ?, `comment` = ? WHERE `message` = ? LIMIT 1", "s", $options["old_message"]);
      } else {
        $this->query("UPDATE `messages` SET `message` = ? WHERE `message` = ? LIMIT 1", "s", $options["old_message"]);
      }
    }
    if (self::$mysqli->affected_rows === 0) {
      if ($issetComment) {
        $this->query("INSERT INTO `messages` (`message`, `comment`) VALUES (?, ?)", "ss", [ $message, $options["comment"] ]);
      } else {
        $this->query("INSERT INTO `message` (`message`) VALUES (?)", "s", $message);
      }
    }
    return $this;
  }

}
