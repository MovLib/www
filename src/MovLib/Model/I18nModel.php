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

use \MovLib\Exception\DatabaseException;
use \MovLib\Model\AbstractModel;
use \MovLib\Utility\AsyncLogger;

/**
 * Description of I18nModel
 *
 * @author Franz Torghele <ftorghele.mmt-m:2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class I18nModel extends AbstractModel {

  /**
   *  Construct new I18n Model.
   */
  public function __construct() {
    parent::__construct();    
  }
  
  /**
   * Get a translated message from the database.
   * 
   * @param string $message
   *  The message which acts as key.
   * @param string $languageCode
   *  The language in which the message should be returned.
   * @return string
   *  Message in desired language or english if translation does not exist.
   */
  public function getMessage($message, $languageCode = "en") {
    if ($languageCode === "en") {
      return $message;
    }
    
    $result = $this->query("SELECT COLUMN_GET(dyn_translations, '{$languageCode}' as BINARY) AS translation
      FROM messages WHERE message = `?` LIMIT 1", "s", [ $message ]);
      
    if (empty($result)) {
      // If the above query returns no result, return message in english.
      AsyncLogger::log("Could not find translation ({$languageCode}) of message: '{$message}'", AsyncLogger::LEVEL_INFO);
      return $message;
    }
    return $result[0]["translation"];
  }
  
  /**
   * Get a translated route from the database.
   * 
   * @param string $route
   *  The route which acts as key.
   * @param type $languageCode
   *  The language in wihch the route should be returned.
   * @return string
   *  Route in desired language or english if translation does not exist.
   */
  public function getRoute($route, $languageCode = "en") {
    if ($languageCode === "en") {
      return $route;
    }
    
    $result = $this->query("SELECT COLUMN_GET(dyn_translations, '{$languageCode}' as BINARY) AS translation
      FROM routes WHERE route = `?` LIMIT 1", "s", [ $route ]);
    
    if(empty($result)) {
      // If the above query returns no result, return route in english
      AsyncLogger::log("Could not find translation ({$languageCode}) of route: '{$route}'", AsyncLogger::LEVEL_INFO);
      return $route;
    }
    return $result[0]["translation"];
  }
  
  /**
   * Set or update a message/translation.
   * 
   * @param string $message
   *  The message which acts as key.
   * @param string $comment
   *  A Comment describing the Message.
   * @param array $translations
   *  An array with translations of the message.
   */
  public function setMessage($message, $comment, array $translations) {
    $result = $this->query("SELECT id FROM messages WHERE message = `?` LIMIT 1", "s", [ $message ]);
    if (empty($result)) {
      // @todo insert
    } else {
      // @todo update
    }
  }
  
  /**
   * Set or update a route/translation.
   * 
   * @param string $route
   *  The route which acts as key.
   * @param array $translations
   *  An array with translations of the route.
   */
  public function setRoute($route, array $translations) {
    $result = $this->query("SELECT id FROM routes WHERE route = `?`LIMIT 1", "s", [ $route ]);
    if (empty($result)) {
      // @todo insert
    } else {
      // @todo update
    }
  }

}