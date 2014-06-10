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
use \MovLib\Core\Routing\Route;

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
   * Active intl instance this forum was instantiated with.
   *
   * @var \MovLib\Core\Intl
   */
  protected $intl;

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
   * The forum's route.
   *
   * @var \MovLib\Core\Routing\Route
   */
  public $route;

  /**
   * The forum's table name.
   *
   * @var string
   */
  protected $tableName;

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
   * @param \MovLib\Core\Container $container
   *   The dependency injection container.
   * @param integer $id [optional]
   *   The forum's unique identifier to instantiate, defaults to <code>NULL</code> and an empty instance is created.
   * @throws \MovLib\Exception\ClientException\NotFoundException
   *   If <var>$id</var> was passed and the forum doesn't exist.
   */
  public function __construct(\MovLib\Core\Container $container, $id = null) {
    $this->intl      = $container->intl;
    $this->tableName = "{$this->intl->languageCode}_forum";

    if ($id) {
      static::getSelectQuery($container)->where("id", $id)->fetchInto($this);
    }

    if ($this->id) {
      $concreteForumClass  = static::class . $this->id;
      $this->concreteForum = new $concreteForumClass();
      $this->categoryId    = $this->concreteForum->getCategoryId();
      $this->description   = $this->concreteForum->getDescription($this->intl);
      $this->route         = new Route($this->intl, $this->concreteForum->getRoute());
      $this->title         = $this->concreteForum->getTitle($this->intl);
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Static Methods


  /**
   * Get all forums.
   *
   * @return array
   *   All forums.
   */
  public static function getAll(\MovLib\Core\Container $container) {
    return static::getSelectQuery($container)->fetchObjects(static::class, [ $container ]);
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

  /**
   * Get the select query to fetch forum(s) cache data from the database.
   *
   * @param \MovLib\Core\Container $container
   *   The dependency injection container.
   * @return \MovLib\Core\Database\Query\Select
   *   The select query to fetch forum(s) cache data from the database.
   */
  protected static function getSelectQuery(\MovLib\Core\Container $container) {
    $args = [ $container ];
    return (new Select(Database::getConnection(), "{$container->intl->languageCode}_forum"))
      ->select("id")
      ->select("count_posts")
      ->select("count_topics")

      ->addComposite("lastPost", "\\MovLib\\Data\\Forum\\Post", $args)
      ->select([ "last_post", "id" ])
      ->select([ "last_post", "created" ])
      ->select([ "last_post", "username" ])

      ->addComposite("lastTopic", "\\MovLib\\Data\\Forum\\Topic", $args)
      ->select([ "last_topic", "id" ])
      ->select([ "last_topic", "title" ])
    ;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get the forum's translated description.
   *
   * <b>NOTE</b><br>
   * If you need the description in the current locale simply use the public <var>$description</var> property.
   *
   * @param string $locale
   *   The locale to translate the description to.
   * @return string
   *   The forum's description.
   */
  public function getDescription($locale) {
    if ($locale === $this->intl->locale) {
      return $this->description;
    }
    return $this->concreteForum->getDescription($this->intl, $locale);
  }

  /**
   * Get the forum's translated title.
   *
   * <b>NOTE</b><br>
   * If you need the title in the current locale simply use the public <var>$title</var> property.
   *
   * @param string $locale
   *   The locale to translate the title to.
   * @return string
   *   The forum's title.
   */
  public function getTitle($locale) {
    if ($locale === $this->intl->locale) {
      return $this->title;
    }
    return $this->concreteForum->getTitle($this->intl, $locale);
  }

}
