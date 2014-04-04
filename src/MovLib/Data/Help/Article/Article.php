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
namespace MovLib\Data\Help\Article;

use \MovLib\Data\FileSystem;
use \MovLib\Data\Help\Category\Category;
use \MovLib\Data\Help\SubCategory\SubCategory;
use \MovLib\Exception\ClientException\NotFoundException;

/**
 * Defines the help article entity object.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Article extends \MovLib\Data\AbstractEntity {
  use \MovLib\Data\Help\Article\ArticleTrait;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The help article's deletion state.
   *
   * @var boolean
   */
  public $deleted;

  /**
   * The help article category.
   *
   * @var integer
   */
  public $category;

/**
   * The timestamp on which this help article was changed.
   *
   * @var integer
   */
  public $changed;

  /**
   * The timestamp on which this help article was created.
   *
   * @var integer
   */
  public $created;

  /**
   * The help article's deletion state.
   *
   * @var boolean
   */
  public $deleted;

  /**
   * The help article's unique identifier.
   *
   * @var integer
   */
  public $id;

  /**
   * The translated route of this help article.
   *
   * @var string
   */
  public $route;

  /**
   * The route key of this help article.
   *
   * @var string
   */
  public $routeKey;

  /**
   * The help article sub category.
   *
   * @var mixed
   */
  public $subCategory;

  /**
   * The help article's text in the current display language.
   *
   * @var string
   */
  public $text;

  /**
   * The help article's title in the current display language.
   *
   * @var string
   */
  public $title;

  /**
   * The help article's view count.
   *
   * @var integer
   */
  public $viewCount;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new help article object.
   *
   * @param \MovLib\Core\DIContainer $diContainer
   *   {@inheritdoc}
   * @param integer $id [optional]
   *   The helb article's unique identifier to instantiate, defaults to <code>NULL</code> (no helb article will be loaded).
   * @throws \MovLib\Exception\ClientException\NotFoundException
   */
  public function __construct(\MovLib\Core\DIContainer $diContainer, $id = null) {
    parent::__construct($diContainer);
    if ($id) {
      $stmt = $this->getMySQLi()->prepare(<<<SQL
SELECT
  `help_articles`.`id` AS `id`,
  `help_articles`.`help_category_id` AS `category`,
  `help_articles`.`help_subcategory_id` AS `subCategory`,
  `help_articles`.`changed` AS `changed`,
  `help_articles`.`created` AS `created`,
  `help_articles`.`deleted` AS `deleted`,
  IFNULL(
    COLUMN_GET(`help_articles`.`dyn_texts`, ? AS CHAR),
    COLUMN_GET(`help_articles`.`dyn_texts`, '{$this->intl->defaultLanguageCode}' AS CHAR)'
  ) AS `text`,
  IFNULL(
    COLUMN_GET(`help_articles`.`dyn_titles`, ? AS CHAR),
    COLUMN_GET(`help_articles`.`dyn_titles`, '{$this->intl->defaultLanguageCode}' AS CHAR)'
  ) AS `title`,
  `help_articles`.`view_count` as `viewCount`
FROM `help_articles`
WHERE `id` = ?
LIMIT 1
SQL
      );
      $stmt->bind_param("ssd", $this->intl->languageCode, $this->intl->languageCode, $id);
      $stmt->execute();
      $stmt->bind_result(
        $this->id,
        $this->category,
        $this->subCategory,
        $this->changed,
        $this->created,
        $this->deleted,
        $this->text,
        $this->title,
        $this->viewCount
      );
      $found = $stmt->fetch();
      $stmt->close();
      if (!$found) {
        throw new NotFoundException("Couldn't find help article {$id}");
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
    $this->category = new Category($this->category);

    if (isset($this->subCategory)) {
      $this->subCategory = new SubCategory($this->subCategory);
      $this->route       = $this->intl->r("/help/{0}/{1}/{2}", [
        FileSystem::sanitizeFilename($this->category->title),
        FileSystem::sanitizeFilename($this->subCategory->title),
        FileSystem::sanitizeFilename($this->title)
      ]);
    }
    else {
      $this->route = $this->intl->r("/help/{0}/{1}", [
        FileSystem::sanitizeFilename($this->category->title),
        FileSystem::sanitizeFilename($this->title)
      ]);
    }
    return parent::init();
  }

}
