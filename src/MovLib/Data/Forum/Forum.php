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
namespace MovLib\Data\Forum;

use \MovLib\Core\Database\Database;

/**
 * Defines the forum object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Forum extends AbstractBase {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Forum";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The forum's unique category identifier it belongs to.
   *
   * @var integer
   */
  public $categoryId;

  /**
   * The forum's description in the current locale.
   *
   * @var string
   */
  public $description;

  /**
   * The forum's icon.
   *
   * @var ?
   */
  public $icon;

  /**
   * The forum's last topic.
   *
   * @var \MovLib\Data\Forum\Topic|boolean
   */
  protected $lastTopic;

  /**
   * The forum's concrete storage object.
   *
   * @var \MovLib\Data\Forum\Storage\ForumInterface
   */
  protected $concreteForum;

  /**
   * The forum's title in the current locale.
   *
   * @var string
   */
  public $title;

  /**
   * The forum's total post count.
   *
   * @var integer
   */
  protected $totalPostCount;

  /**
   * The forum's total topic count.
   *
   * @var integer
   */
  protected $totalTopicCount;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new forum.
   *
   * @param \MovLib\Core\Container $container
   *   The dependency injection container.
   * @param integer $id [optional]
   *   The forum's unique identifier to instantiate, defaults to <code>NULL</code> and an empty instance is created.
   * @throws \MovLib\Exception\ClientException\NotFoundException
   *   If <var>$id</var> was passed and the forum doesn't exist.
   */
  public function __construct(\MovLib\Core\Container $container, $id = null) {
    parent::__construct($container);
    if ($id) {
      try {
        $concreteForumClass  = static::class . "s\\Forum{$id}";
        $this->concreteForum = new $concreteForumClass();
      }
      catch (\Exception $e) {
        throw new NotFoundException("Couldn't find forum for '{$id}'.", null, $e);
      }

      $this->id          = (integer) $id;
      $this->categoryId  = $this->concreteForum->getCategoryId();
      $this->description = $this->concreteForum->getDescription($this->intl);
      $this->title       = $this->concreteForum->getTitle($this->intl);
      $this->setRoute($this->intl, $this, "/forum/{0}");
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Static Methods


  /**
   * Get all forums.
   *
   * @staticvar array $forums
   *   Used to cache the forums.
   * @param \MovLib\Core\Container $container
   *   The dependency injection container.
   * @return array
   *   All forums.
   */
  public static function getAll(\MovLib\Core\Container $container) {
    static $forums = [];
    if (empty($forums[$container->intl->code])) {
      $forums[$container->intl->code] = [];
      foreach (new \RegexIterator(new \DirectoryIterator("dr://src/MovLib/Data/Forum/Forums"), "/Forum([0-9]+)\.php/", \RegexIterator::GET_MATCH) as $matches) {
        $forums[$container->intl->code][$matches[1]] = new static($container, $matches[1]);
      }
    }
    return $forums[$container->intl->code];
  }

  /**
   * Get all available forum categories.
   *
   * The forum categories have only one purpose, they are used to group the various forums on the index presentation. We
   * define the categories here in code, this makes sure that we have a proper history (git) for any changes, only devs
   * with access to the repository are able to create new categories, and we can use the static translation system.
   *
   * @param \MovLib\Core\Intl $intl
   *   The internationalization instance to translate the categories to.
   * @return array
   *   All available forum categories.
   */
  public static function getCategories(\MovLib\Core\Intl $intl) {
    return [
      1 => $intl->t("Site Discussions"),
      2 => $intl->t("Suggestions"),
      3 => $intl->t("General Discussions"),
      4 => $intl->t("Database Discussions"),
    ];
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Decrement the forum's total post count.
   *
   * @param boolean $force [optional]
   *   Whether to use the cache or not, defaults to <code>FALSE</code> and cache will be used.
   * @return this
   */
  public function decrementPostCount($force = false) {
    return ($this->totalPostCount = $this->container->getMemoryCache()->decrement("forum-{$this->id}-post-count", $this->getPostCount($force)));
  }

  /**
   * Decrement the forum's total topic count.
   *
   * @param boolean $force [optional]
   *   Whether to use the cache or not, defaults to <code>FALSE</code> and cache will be used.
   * @return this
   */
  public function decrementTopicCount($force = false) {
    return ($this->totalTopicCount = $this->container->getMemoryCache()->decrement("forum-{$this->id}-topic-count", $this->getTopicCount($force)));
  }

  /**
   * {@inheritdoc}
   */
  protected function getCacheKey($suffix) {
    return "forum-{$this->id}-{$suffix}";
  }

  /**
   * Get the forum's translated description.
   *
   * <b>NOTE</b><br>
   * If you need the description in the current locale simply use the public <var>$description</var> property.
   *
   * @param string $languageCode
   *   The system language's ISO 639-1 alpha-2 code to translate the description to.
   * @return string
   *   The forum's description.
   */
  public function getDescription($languageCode) {
    return $languageCode === $this->intl->locale ? $this->description : $this->concreteForum->getDescription($this->intl, $languageCode);
  }

  /**
   * Get the forum's last topic.
   *
   * @param boolean $force [optional]
   *   Whether to use the cache or not, defaults to <code>FALSE</code> and cache will be used.
   * @return \MovLib\Data\Forum\Topic|boolean
   *   The forum's last topic or <code>FALSE</code> if this forum has no last topic.
   */
  public function getLastTopic($force = false) {
    if ($this->lastTopic === null) {
      $cache = $this->container->getPersistentCache("forum-{$this->id}-last-topic");
      if ($force === true || ($this->lastTopic = $cache->get()) === null) {
        $result = Database::getConnection()->query(<<<SQL
SELECT `t`.`id`
FROM `{$this->intl->code}_topic` AS `t`
INNER JOIN `{$this->intl->code}_post` AS `p`
  ON `p`.`topic_id` = `t`.`id`
WHERE `t`.`forum_id` = {$this->id}
ORDER BY `p`.`created` ASC
LIMIT 1
SQL
        );
        if (($this->lastTopic = ($result->num_rows == 1)) === true) {
          $this->lastTopic = new Topic($this->container, $result->fetch_row()[0], $this);
        }
        $result->free();
        $cache->set($this->lastTopic);
      }
    }
    return $this->lastTopic;
  }

  /**
   * Get the forum's total post count.
   *
   * @param boolean $force [optional]
   *   Whether to use the cache or not, defaults to <code>FALSE</code> and cache will be used.
   * @return integer
   *   The forum's total post count.
   */
  public function getPostCount($force = false) {
    if ($this->totalPostCount === null) {
      $cache = $this->container->getMemoryCache("forum-{$this->id}-post-count");
      if ($force === true || ($this->totalPostCount = $cache->get()) === null) {
        $cache->set(($this->totalPostCount = (integer) Database::getConnection()->query(<<<SQL
SELECT COUNT(*)
FROM `{$this->intl->code}_post` AS `p`
INNER JOIN `{$this->intl->code}_topic` AS `t`
  ON `t`.`id` = `p`.`topic_id`
WHERE `t`.`forum_id` = {$this->id}
LIMIT 1
SQL
        )->fetch_all()[0][0]));
      }
    }
    return $this->totalPostCount;
  }

  /**
   * Get the forum's total topic count.
   *
   * @param boolean $force [optional]
   *   Whether to use the cache or not, defaults to <code>FALSE</code> and cache will be used.
   * @return integer
   *   The forum's total topic count.
   */
  public function getTopicCount($force = false) {
    if ($this->totalTopicCount === null) {
      $cache = $this->container->getMemoryCache("forum-{$this->id}-topic-count");
      if ($force === true || ($this->totalTopicCount = $cache->get()) === null) {
        $cache->set(($this->totalTopicCount = (integer) Database::getConnection()->query(
          "SELECT COUNT(*) FROM `{$this->intl->code}_topic` WHERE `forum_id` = {$this->id} LIMIT 1"
        )->fetch_all()[0][0]));
      }
    }
    return $this->totalTopicCount;
  }

  /**
   * Get the forum's translated title.
   *
   * <b>NOTE</b><br>
   * If you need the title in the current language simply use the public <var>$title</var> property.
   *
   * @param string $languageCode
   *   The system language's ISO 639-1 alpha-2 code to translate the title to.
   * @return string
   *   The forum's translated title.
   */
  public function getTitle($languageCode) {
    return $languageCode === $this->intl->code ? $this->title : $this->concreteForum->getTitle($this->intl, $languageCode);
  }

  /**
   * Increment the forum's total post count.
   *
   * @param boolean $force [optional]
   *   Whether to use the cache or not, defaults to <code>FALSE</code> and cache will be used.
   * @return this
   */
  public function incrementPostCount($force = false) {
    return ($this->totalPostCount = $this->container->getMemoryCache()->increment("forum-{$this->id}-post-count", $this->getPostCount($force)));
  }

  /**
   * Increment the forum's total topic count.
   *
   * @param boolean $force [optional]
   *   Whether to use the cache or not, defaults to <code>FALSE</code> and cache will be used.
   * @return this
   */
  public function incrementTopicCount($force = false) {
    return ($this->totalTopicCount = $this->container->getMemoryCache()->increment("forum-{$this->id}-topic-count", $this->getTopicCount($force)));
  }

  /**
   * Set the forum's last topic.
   *
   * @param \MovLib\Data\Forum\Topic $lastTopic
   *   The last topic to set.
   * @return this
   * @throws \InvalidArgumentException
   *   If the topic doesn't belong to this forum.
   */
  public function setLastTopic(\MovLib\Data\Forum\Topic $lastTopic) {
    // @devStart
    if ($lastTopic->forum->id !== $this->id) {
      throw new \InvalidArgumentException("The topic doesn't belong to this forum.");
    }
    // @devEnd
    $this->container->getPersistentCache()->set(($this->lastTopic = $lastTopic), "forum-{$this->id}-last-topic");
    return $this;
  }

}
