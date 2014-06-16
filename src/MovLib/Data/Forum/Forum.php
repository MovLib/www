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
use \MovLib\Core\Database\Query\Select;

/**
 * Defines the forum object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Forum implements \MovLib\Core\Routing\RoutingInterface {
  use \MovLib\Core\Routing\RoutingTrait;


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
   * The forum's concrete object that knows the category, description, and title.
   *
   * @var \MovLib\Data\Forum\ForumInterface
   */
  protected $concreteForum;

  /**
   * The forum's total post count.
   *
   * @var integer
   */
  public $countPosts;

  /**
   * The forum's total topic count.
   *
   * @var integer
   */
  public $countTopics;

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
   * The forum's unique identifier.
   *
   * @var integer
   */
  public $id;

  /**
   * The forum's ISO 639-1 language code.
   *
   * @see ::__construct
   * @var string
   */
  public $languageCode;

  /**
   * The forum's last post.
   *
   * @var \MovLib\Data\Forum\Post
   */
  public $lastPost;

  /**
   * The forum's last topic.
   *
   * @var \MovLib\Data\Forum\Topic
   */
  public $lastTopic;

  /**
   * The forum's parent entities.
   *
   * @var array
   */
  public $parents = [];

  /**
   * The forum's title in the current locale.
   *
   * @var string
   */
  public $title;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new forum.
   *
   * @param string $languageCode
   *   The forum's (system) language code. The language code of a forum is usually provided by the request.
   * @param integer $id [optional]
   *   The forum's unique identifier to instantiate, defaults to <code>NULL</code> and an empty instance is created.
   * @throws \MovLib\Exception\ClientException\NotFoundException
   *   If <var>$id</var> was passed and the forum doesn't exist.
   */
  public function __construct($languageCode, $id = null) {
    if ($id) {
      try {
        $concreteForumClass  = static::class . $id;
        $this->concreteForum = new $concreteForumClass();
      }
      catch (\Exception $e) {
        throw new NotFoundException("Couldn't find forum for '{$id}'.", null, $e);
      }

      $this->id          = (integer) $id;
      $this->categoryId  = $this->concreteForum->getCategoryId();
      $this->description = $this->concreteForum->getDescription();
      $this->title       = $this->concreteForum->getTitle();
      $this->countPosts  = $this->getPostCount();
      $this->countTopics = $this->getTopicCount();
      $this->setRoute($this->concreteForum->getRoute());
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Static Methods


  /**
   * Get all forums.
   *
   * @param \MovLib\Core\Container $container
   *   The dependency injection container.
   * @return array
   *   All forums.
   */
  public static function getAll(\MovLib\Core\Container $container) {
    return [
      1 => new Forum($container, 1),
    ];
  }

  /**
   * Get all available forum categories.
   *
   * The forum categories have only one purpose, they are used to group the various forums on the index presentation. We
   * define the categories here in code, this makes sure that we have a proper history (git) for any changes, only devs
   * with access to the repository are able to create new categories, and we can use the static translation system.
   *
   * @param \MovLib\Core\Intl $intl
   *   Intl instance used to translate the category titles.
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
   * Get the forum's translated description.
   *
   * <b>NOTE</b><br>
   * If you need the description in the current locale simply use the public <var>$description</var> property.
   *
   * @param string $languageCode
   *   The locale to translate the description to.
   * @return string
   *   The forum's description.
   */
  public function getDescription($languageCode) {
    return $languageCode === $this->intl->locale ? $this->description : $this->concreteForum->getDescription($this->intl, $languageCode);
  }

  /**
   * Get the forum's total post count.
   *
   * <b>NOTE</b><br>
   * This method will always execute and SQL query that counts the actual posts that belong to this forum. Usually you
   * simply want the count the instance has fetched from cache, use the public <var>$countPosts</var> property to do so.
   *
   * @return integer
   *   The forum's total post count.
   */
  public function getPostCount() {
    return (integer) Database::getConnection()->query(<<<SQL
SELECT COUNT(*)
FROM `{$this->languageCode}_post` AS `p`
INNER JOIN `{$this->languageCode}_topic` AS `t`
  ON `t`.`id` = `p`.`topic_id`
WHERE `t`.`forum_id` = {$this->id}
LIMIT 1
SQL
    )->fetch_all()[0][0];
  }

  /**
   * Get the forum's total topic count.
   *
   * <b>NOTE</b><br>
   * This method will always execute an SQL query that counts the actual topics that belong to this forum. Usually you
   * simply want the count the instance has fetched from the cache, use the public <var>$countTopics</var> property to
   * do so.
   *
   * @return integer
   *   The forum's total topic count.
   */
  public function getTopicCount() {
    return (integer) Database::getConnection()->query("SELECT COUNT(*) FROM `{$this->languageCode}_topic` WHERE `forum_id` = {$this->id} LIMIT 1")->fetch_all()[0][0];
  }

  /**
   * Get the forum's translated title.
   *
   * <b>NOTE</b><br>
   * If you need the title in the current language simply use the public <var>$title</var> property.
   *
   * @param string $languageCode
   *   The language code to translated the title to.
   * @return string
   *   The forum's translated title.
   */
  public function getTitle($languageCode) {
    return $languageCode === $this->languageCode ? $this->title : $this->concreteForum->getTitle($languageCode);
  }

}
