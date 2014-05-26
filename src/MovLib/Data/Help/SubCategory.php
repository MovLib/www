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

use \MovLib\Data\Help\Category;
use \MovLib\Exception\ClientException\NotFoundException;

/**
 * Defines the help subcategory entity object.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class SubCategory extends \MovLib\Data\AbstractEntity {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The amount of articles in this subcategory.
   *
   * @var integer
   */
  public $articleCount;

  /**
   * The help subcategory this sub subcategory belongs to.
   *
   * @var mixed
   */
  public $category;

  /**
   * The help subcategory title's icon (e.g. ico-person).
   *
   * @var string
   */
  public $icon;

  /**
   * The help subcategory's unique identifier.
   *
   * @var integer
   */
  public $id;

  /**
   * The help subcategory's title in the current display language.
   *
   * @var string
   */
  public $title;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new help subcategory object.
   *
   * @param \MovLib\Core\Container $container
   *   {@inheritdoc}
   * @param integer $id [optional]
   *   The help subcategory's unique identifier to instantiate, defaults to <code>NULL</code> (no help subcategory will be loaded).
   * @throws \MovLib\Exception\ClientException\NotFoundException
   */
  public function __construct(\MovLib\Core\Container $container, $id = null) {
    parent::__construct($container);
    if ($id) {
      $stmt = $this->getMySQLi()->prepare(<<<SQL
SELECT
  `help_subcategories`.`help_category_id` AS `category`,
  `help_subcategories`.`id` AS `id`,
  `help_subcategories`.`changed` AS `changed`,
  `help_subcategories`.`created` AS `created`,
  `help_subcategories`.`deleted` AS `deleted`,
  `help_subcategories`.`icon` AS `icon`,
  `help_subcategories`.`title` AS `title`
FROM `help_subcategories`
WHERE `id` = ?
LIMIT 1
SQL
      );
      $stmt->bind_param("d", $id);
      $stmt->execute();
      $stmt->bind_result(
        $this->category,
        $this->id,
        $this->changed,
        $this->created,
        $this->deleted,
        $this->icon,
        $this->title
      );
      $found = $stmt->fetch();
      $stmt->close();
      if (!$found) {
        throw new NotFoundException("Couldn't find help subcategory {$id}");
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
  public function init() {
    $this->articleCount = $this->getCount("help_articles", "`deleted` = false AND `help_subcategory_id` = {$this->id}");
    if (isset($this->category) && !$this->category instanceof \stdClass) {
      $this->category = new Category($this->container, $this->category);
    }
    $this->tableName    = "help_subcategories";
    $this->pluralKey    = "categories";
    $this->routeKey     = "{$this->category->routeKey}/" . sanitize_filename($this->title);
    $this->route        = $this->intl->r($this->routeKey);
    $this->singularKey  = "category";
    return parent::init();
  }

}
