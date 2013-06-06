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
use \MovLib\Model\DelayedModel;
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
    $comment = isset($options["comment"]) ? $options["comment"] : null;
    $oldMessage = isset($options["old_message"]) ? $options["old_message"] : null;
    try {
      $translation = $this->query(
        "SELECT
          COLUMN_GET(`dyn_translations`, '{$languageCode}' AS BINARY) AS `translation`
        FROM `messages`
          WHERE `message` = ?
          LIMIT 1",
        "s",
        [ $message ]
      )[0]["translation"];
    } catch (ErrorException $e) {
      DelayedLogger::log("Could not find {$languageCode} translation for message: '{$message}'", E_NOTICE);
      DelayedModel::stackCallback(function ($delayedCallback) use ($message, $comment, $oldMessage) {
        //
        // @todo How can we make this easier and DRY?
        //
        if ($oldMessage && $comment) {
          $delayedCallback->update("messages", "ss", [ "message" => $message, "comment" => $comment ], [ "message" => $oldMessage ]);
          if ($delayedCallback->getMySQLi()->affected_rows === 0) {
            $delayedCallback->insert("messages", "ss", [ "message" => $message, "comment" => $comment ]);
          }
        } elseif ($oldMessage) {
          $delayedCallback->update("messages", "s", [ "message" => $message ], [ "message" => $oldMessage ]);
          if ($delayedCallback->getMySQLi()->affected_rows === 0) {
            $delayedCallback->insert("messages", "s", [ "message" => $message ]);
          }
        } elseif ($comment) {
          $delayedCallback->insert("messages", "ss", [ "message" => $message, "comment" => $comment ]);
        } else {
          $delayedCallback->insert("messages", "s", [ "message" => $message ]);
        }
      });
    }
    if ($translation) {
      return $translation;
    }
    return $message;
  }

  public function getRoute($route, $languageCode, $options) {
    //
    // @todo Create generic translation method based on the code from aboves getMessage() method and call that generic
    // method from here and the above one. Think DRY, generic, performant, and smart!
    //
    //return $this->method($route, $languageCode, $options);
    return $route;
  }

}