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
 * Handling of one or more help articles.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class HelpArticle extends \MovLib\Data\Database {


  // ------------------------------------------------------------------------------------------------------------------- Properties


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
   * The help article's view count.
   *
   * @var integer
   */
  public $viewCount;

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


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new help article.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @param integer $id [optional]
   *   The help article's unique identifier, omit to create empty instance.
   * @throws \MovLib\Presentation\Error\NotFound
   */
  public function __construct($id = null) {
    global $db, $i18n;
    if ($id) {
      $query = self::getQuery();
      $stmt = $db->query("
        {$query}
        WHERE
          `id` = ?
        LIMIT 1",
        "ssd",
        [ $i18n->languageCode, $i18n->languageCode, $id ]
      );
      $stmt->bind_result(
        $this->category,
        $this->changed,
        $this->created,
        $this->viewCount,
        $this->deleted,
        $this->id,
        $this->subCategory,
        $this->text,
        $this->title
      );
      if (!$stmt->fetch()) {
        throw new NotFound;
      }
      $stmt->close();
      $this->id = $id;
    }
    if ($this->id) {
      $this->init();
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


 /**
   * Get all help articles.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @return \mysqli_result
   *   The query result.
   * @throws \MovLib\Exception\DatabaseException
   */
  public static function getHelpArticles() {
    global $db, $i18n;
    $query = self::getQuery();
    return $db->query("
        {$query}
        ORDER BY `category` DESC, `subCategory` DESC",
      "ss",
      [ $i18n->languageCode, $i18n->languageCode ]
    )->get_result();
  }

  /**
   * Get all help article ids.
   *
   * @global \MovLib\Data\Database $db
   * @return \mysqli_result
   *   The query result.
   * @throws \MovLib\Exception\DatabaseException
   */
  public static function getHelpArticleIds() {
    global $db;

    return $db->query("SELECT `id` FROM `help_articles`")->get_result();
  }

  /**
   * Get the default query.
   *
   * @global \MovLib\Data\I18n $i18n
   * @staticvar string $query
   *   Used to cache the default query.
   * @return string
   *   The default query.
   */
  public static function getQuery() {
    global $i18n;
    static $query = null;
    if (!$query) {
      $query =
        "SELECT
          `help_category_id` AS `category`,
          `changed`,
          `created`,
          `view_count` as `viewCount`,
          `deleted`,
          `id`,
          `help_subcategory_id` AS `subCategory`,
          IFNULL(COLUMN_GET(`dyn_texts`, ? AS CHAR), COLUMN_GET(`dyn_texts`, '{$i18n->defaultLanguageCode}' AS CHAR)) AS `text`,
          IFNULL(COLUMN_GET(`dyn_titles`, ? AS CHAR), COLUMN_GET(`dyn_titles`, '{$i18n->defaultLanguageCode}' AS CHAR)) AS `title`
        FROM `help_articles`"
      ;
    }
    return $query;
  }

  /**
   * Initialize award.
   *
   * @global type $i18n
   */
  protected function init() {
    global $i18n;

    $this->deleted  = (boolean) $this->deleted;
    $this->category = new HelpCategory($this->category);

    if (isset($this->subCategory)) {
      $this->subCategory = new HelpSubCategory($this->subCategory);
      $this->routeKey    = "/help/{0}/{1}/{2}";
      $this->route       = $i18n->r($this->routeKey, [
        FileSystem::sanitizeFilename($this->category->title),
        FileSystem::sanitizeFilename($this->subCategory->title),
        FileSystem::sanitizeFilename($this->title)
      ]);
    }
    else {
      $this->routeKey    = "/help/{0}/{1}";
      $this->route       = $i18n->r($this->routeKey, [
        FileSystem::sanitizeFilename($this->category->title),
        FileSystem::sanitizeFilename($this->title)
      ]);
    }
  }

}
