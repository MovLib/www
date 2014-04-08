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

use \MovLib\Exception\ClientException\NotFoundException;

/**
 * Defines the help category entity object.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Category extends \MovLib\Data\AbstractEntity {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The amount of articles in this subcategory.
   *
   * @var integer
   */
  public $articleCount;

  /**
   * The timestamp on which this help category was changed.
   *
   * @var integer
   */
  public $changed;

  /**
   * The timestamp on which this help category was created.
   *
   * @var integer
   */
  public $created;

  /**
   * The help category's deletion state.
   *
   * @var boolean
   */
  public $deleted;

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
   * The route key in default language.
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
   * Instantiate new help category object.
   *
   * @param \MovLib\Core\DIContainer $diContainer
   *   {@inheritdoc}
   * @param integer $id [optional]
   *   The helb category's unique identifier to instantiate, defaults to <code>NULL</code> (no helb category will be loaded).
   * @throws \MovLib\Exception\ClientException\NotFoundException
   */
  public function __construct(\MovLib\Core\DIContainer $diContainer, $id = null) {
    parent::__construct($diContainer);
    if ($id) {
      $stmt = $this->getMySQLi()->prepare(<<<SQL
SELECT
  `help_categories`.`id` AS `id`,
  `help_categories`.`changed` AS `changed`,
  `help_categories`.`created` AS `created`,
  `help_categories`.`deleted` AS `deleted`,
  `help_categories`.`icon`,
  IFNULL(
    COLUMN_GET(`help_categories`.`dyn_descriptions`, ? AS CHAR),
    COLUMN_GET(`help_categories`.`dyn_descriptions`, '{$this->intl->defaultLanguageCode}' AS CHAR)
  ) AS `description`,
  IFNULL(
    COLUMN_GET(`help_categories`.`dyn_titles`, ? AS CHAR),
    COLUMN_GET(`help_categories`.`dyn_titles`, '{$this->intl->defaultLanguageCode}' AS CHAR)
  ) AS `title`,
  COLUMN_GET(`help_categories`.`dyn_titles`, '{$this->intl->defaultLanguageCode}' AS CHAR) AS `routeKey`
FROM `help_categories`
WHERE `id` = ?
LIMIT 1
SQL
      );
      $stmt->bind_param("ssd", $this->intl->languageCode, $this->intl->languageCode, $id);
      $stmt->execute();
      $stmt->bind_result(
        $this->id,
        $this->changed,
        $this->created,
        $this->deleted,
        $this->icon,
        $this->description,
        $this->title,
        $this->routeKey
      );
      $found = $stmt->fetch();
      $stmt->close();
      if (!$found) {
        throw new NotFoundException("Couldn't find help category {$id}");
      }
    }
    if ($this->id) {
      $this->init();
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  protected function init() {
    $this->articleCount = $this->getCount("help_articles", "`deleted` = false AND `help_category_id` = {$this->id} AND `help_subcategory_id` IS NULL");
    $this->pluralKey    = $this->tableName = "help_categories";
    $this->route        = $this->intl->r("/help/{0}", [ $this->fs->sanitizeFilename($this->title) ]);
    $this->routeKey     = "/help/{$this->fs->sanitizeFilename($this->routeKey)}";
    $this->singularKey  = "help_category";
    return parent::init();
  }

}
