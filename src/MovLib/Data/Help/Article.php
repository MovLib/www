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

use \MovLib\Core\Database\Database;
use \MovLib\Core\Revision\OriginatorTrait;
use \MovLib\Core\Search\RevisionTrait;
use \MovLib\Data\Help\Category;
use \MovLib\Data\Help\SubCategory;
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
 final class Article extends \MovLib\Data\AbstractEntity implements \MovLib\Core\Revision\OriginatorInterface {
  use OriginatorTrait, RevisionTrait {
    RevisionTrait::postCommit insteadof OriginatorTrait;
    RevisionTrait::postCreate insteadof OriginatorTrait;
  }


  //-------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Article";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The help article category.
   *
   * @var integer
   */
  public $category;

  /**
   * The help article's title in default language.
   *
   * @var null|string
   */
  public $defaultTitle;

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

  /**
   * {@inheritdoc}
   */
  public $pluralKey = "help_articles";

  /**
   * {@inheritdoc}
   */
  public $singularKey = "help_article";


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new help article object.
   *
   * @param \MovLib\Core\Container $container
   *   {@inheritdoc}
   * @param integer $id [optional]
   *   The helb article's unique identifier to instantiate, defaults to <code>NULL</code> (no helb article will be loaded).
   * @throws \MovLib\Exception\ClientException\NotFoundException
   */
  public function __construct(\MovLib\Core\Container $container, $id = null) {
    parent::__construct($container);
    if ($id) {
      $connection = Database::getConnection();
      $stmt = $connection->prepare(<<<SQL
SELECT
  `id`,
  `help_category_id`,
  `help_subcategory_id`,
  `changed`,
  `created`,
  `deleted`,
  IFNULL(
    COLUMN_GET(`dyn_texts`, '{$this->intl->languageCode}' AS CHAR),
    COLUMN_GET(`dyn_texts`, '{$this->intl->defaultLanguageCode}' AS CHAR)
  ),
  COLUMN_GET(`dyn_titles`, '{$this->intl->defaultLanguageCode}' AS CHAR),
  IFNULL(
    COLUMN_GET(`dyn_titles`, '{$this->intl->languageCode}' AS CHAR),
    COLUMN_GET(`dyn_titles`, '{$this->intl->defaultLanguageCode}' AS CHAR)
  ),
  `view_count` as `viewCount`
FROM `help_articles`
WHERE `id` = ?
LIMIT 1
SQL
      );
      $stmt->bind_param("d", $id);
      $stmt->execute();
      $stmt->bind_result(
        $this->id,
        $this->category,
        $this->subCategory,
        $this->changed,
        $this->created,
        $this->deleted,
        $this->text,
        $this->defaultTitle,
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
  protected function defineSearchIndex(\MovLib\Core\Search\SearchIndexer $search, \MovLib\Core\Revision\RevisionInterface $revision) {
    return $search->indexSimpleSuggestion($revision->titles);
  }

  /**
   * {@inheritdoc}
   * @param \MovLib\Data\History\ArticleRevision $revision {@inheritdoc}
   * @return \MovLib\Data\History\ArticleRevision {@inheritdoc}
   */
  protected function doCreateRevision(\MovLib\Core\Revision\RevisionInterface $revision) {
    $this->setRevisionArrayValue($revision->texts, $this->text);
    $this->setRevisionArrayValue($revision->titles, $this->title);
    $revision->category    = $this->category;
    $revision->subCategory = $this->subCategory;

    // Don't forget that we might be a new help article and that we might have been created via a different system locale
    // than the default one, in which case the user was required to enter a default name. Of course we have to export that
    // as well to our revision.
    if (isset($this->defaultTitle)) {
      $revision->titles[$this->intl->defaultLanguageCode] = $this->defaultTitle;
    }

    return $revision;
  }

  /**
   * {@inheritdoc}
   * @param \MovLib\Data\Help\ArticleRevision $revision {@inheritdoc}
   * @return this {@inheritdoc}
   */
  protected function doSetRevision(\MovLib\Core\Revision\RevisionInterface $revision) {
    $this->text  = $this->getRevisionArrayValue($revision->texts);
    $this->title = $this->getRevisionArrayValue($revision->titles, $revision->titles[$this->intl->languageCode]);
    $revision->category    && $this->category = $revision->category;
    $revision->subCategory && $this->category = $revision->subCategory;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function init() {
    if (isset($this->category) && !$this->category instanceof \MovLib\Data\Help\Category) {
      $this->category = new Category($this->container, $this->category);
    }
    $this->routeArgs     = [ $this->id ];

    if (isset($this->subCategory)) {
      if (isset($this->subCategory) && !$this->subCategory instanceof \MovLib\Data\Help\SubCategory) {
        $this->subCategory = new SubCategory($this->container, $this->subCategory);
      }
      $this->routeKey = "{$this->subCategory->routeKey}/{0}";
    }
    else {
      $this->routeKey = "{$this->category->routeKey}/{0}";
    }
    $this->route    = $this->intl->r($this->routeKey, $this->routeArgs);
    return parent::init();
  }

}
