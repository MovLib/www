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
namespace MovLib\Data\Help;

use \MovLib\Data\FileSystem;
use \MovLib\Presentation\Error\NotFound;

/**
 * Handling of one or more help categories.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class HelpCategory extends \MovLib\Data\Database {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The help category's description in the current display language.
   *
   * @var string
   */
  public $description;

  /**
   * The help category title's icon (e.g. ico-person).
   *
   * @var string
   */
  public $icon;

  /**
   * The help category's unique identifier.
   *
   * @var integer
   */
  public $id;

  /**
   * The translated route of this help category.
   *
   * @var string
   */
  public $route;

  /**
   * The route key of this help category.
   *
   * @var string
   */
  public $routeKey;

  /**
   * The help category's title in the current display language.
   *
   * @var string
   */
  public $title;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new help category.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @param integer $id
   *   The help category's unique identifier, omit to create empty instance.
   * @throws \MovLib\Presentation\Error\NotFound
   */
  public function __construct($id) {
    global $db, $i18n;

    $stmt = $db->query("
      SELECT
        `icon`,
        IFNULL(COLUMN_GET(`dyn_descriptions`, ? AS CHAR), COLUMN_GET(`dyn_descriptions`, '{$i18n->defaultLanguageCode}' AS CHAR)) AS `description`,
        `id`,
        IFNULL(COLUMN_GET(`dyn_titles`, ? AS CHAR), COLUMN_GET(`dyn_titles`, '{$i18n->defaultLanguageCode}' AS CHAR)) AS `title`
      FROM `help_categories`
      WHERE `id` = ?
      LIMIT 1",
      "ssd",
      [ $i18n->languageCode, $i18n->languageCode, $id ]
    );
    $stmt->bind_result(
      $this->icon,
      $this->description,
      $this->id,
      $this->title
    );
    if (!$stmt->fetch()) {
      throw new NotFound;
    }
    $stmt->close();
    if ($this->id) {
      $this->init();
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get all help category ids.
   *
   * @global \MovLib\Data\Database $db
   * @return \mysqli_result
   *   The query result.
   * @throws \MovLib\Exception\DatabaseException
   */
  public static function getHelpCategoryIds() {
    global $db;

    return $db->query("SELECT `id` FROM `help_categories`")->get_result();
  }

  /**
   * Initialize help category.
   *
   * @global type $i18n
   */
  protected function init() {
    global $i18n;

    $this->routeKey = "/help/{0}";
    $this->route    = $i18n->r($this->routeKey, [ FileSystem::sanitizeFilename($this->title) ]);
  }
}
