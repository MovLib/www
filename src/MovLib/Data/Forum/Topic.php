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
 * Defines the forum topic object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Topic extends AbstractBase {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Topic";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Public Properties


  /**
   * Whether this topic is closed or not.
   *
   * @var boolean
   */
  public $closed = false;

  /**
   * The topic's date and time of the last edit.
   *
   * @var \MovLib\Component\DateTime|null
   */
  public $edited;

  /**
   * The topic's unique user identifier who last edited it.
   *
   * @var integer|null
   */
  public $editorId;

  /**
   * The topic's parent forum.
   *
   * @var \MovLib\Data\Forum\Forum
   */
  public $forum;

  /**
   * Whether the topic is sticky or not.
   *
   * @var boolean
   */
  public $sticky = false;

  /**
   * The topic's title.
   *
   * @var string
   */
  public $title;


  // ------------------------------------------------------------------------------------------------------------------- Protected Properties


  /**
   * The topic's first post.
   *
   * @var \MovLib\Data\Forum\Post
   */
  protected $firstPost;

  /**
   * The topic's last post.
   *
   * @var \MovLib\Data\Forum\Post
   */
  protected $lastPost;

  /**
   * The topic's posts.
   *
   * This is an associative array where they key is a combination of limit and offset, the value is an array where the
   * key is the post's unique identifier and the value the post itself. This allows you to load many posts, but always
   * with limit and offset.
   *
   * @see ::getPosts
   * @var array
   */
  protected $posts;

  /**
   * The topic's total post count.
   *
   * @var integer
   */
  protected $totalPostCount;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new forum topic.
   *
   * @param \MovLib\Core\Container $container
   *   The dependency injection container.
   * @param integer $id [optional]
   *   The topic's unique identifier to load, defaults to <code>NULL</code> and an empty topic is created.
   * @param \MovLib\Data\Forum\Forum $parentForum [internal]
   *   The topic's parent forum, defaults to <code>NULL</code> and the forum is loaded.
   * @throws \InvalidArgumentException
   *   If the given <var>$forum</var> isn't the actual parent forum of this topic.
   */
  public function __construct(\MovLib\Core\Container $container, $id = null, \MovLib\Data\Forum\Forum $parentForum = null) {
    parent::__construct($container);
    if ($id) {
      (new Select())
        ->select("id")
        ->select("closed")
        ->select("edited")
        ->select("editor_id")
        ->select("forum_id", [ "property" => "forum" ])
        ->select("sticky")
        ->select("title")
        ->from("{$this->intl->code}_topic")
        ->where("id", $id)
        ->fetchInto($this)
      ;
      // @devStart
      if (isset($parentForum) && $parentForum->id !== $this->forum) {
        throw new \InvalidArgumentException("The forum isn't the topic's parent.");
      }
      // @devEnd
    }
    if ($this->id) {
      $this->setForum($parentForum ?: new Forum($this->container, $this->forum));
      $this->setRoute($this->intl, $this->forum, "/forum/{0}/topic/{1}", [ $this->id ]);
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Public Methods


  /**
   * Decrement the topic's post count.
   *
   * @return integer
   *   The decremented topic's post count.
   */
  public function decrementTotalPostCount() {
    return ($this->totalPostCount = $this->container->getMemoryCache()->decrement($this->getCacheKey("post-count"), $this->getPostCount()));
  }

  /**
   * Get the topic's first post.
   *
   * @return \MovLib\Data\Forum\Post
   *   The topic's first post.
   */
  public function getFirstPost() {
    if ($this->firstPost === null) {
      $cache = $this->container->getPersistentCache($this->getCacheKey("first-post"));
      if (($this->firstPost = $cache->get()) === null) {
        $cache->set(($this->firstPost = $this->doGetPosts(1, null, "ASC")[0]));
      }
    }
    return $this->firstPost;
  }

  /**
   * Get the topic's last post.
   *
   * @return \MovLib\Data\Forum\Post
   *   The topic's last post.
   */
  public function getLastPost() {
    if ($this->lastPost === null) {
      $cache = $this->container->getPersistentCache($this->getCacheKey("last-post"));
      if (($this->lastPost = $cache->get()) === null) {
        // The last post is the first post if we only have a single post in this topic.
        $cache->set(($this->lastPost = $this->getTotalPostCount() === 1 ? $this->getFirstPost() : $this->doGetPosts(1, null, "DESC")[0]));
      }
    }
  }

  /**
   * Get the topic's posts.
   *
   * @param integer $limit
   *   The amount of posts that should be fetched starting from <var>$offset</var>.
   * @param integer $offset
   *   The offset at which to start fetching posts.
   * @return array
   *   The topic's posts.
   */
  public function getPosts($limit, $offset) {
    $key = "{$limit}-{$offset}";
    if (empty($this->posts[$key])) {
      $cache = $this->container->getPersistentCache($this->getCacheKey("posts-{$key}"));
      if (($this->posts[$key] = $cache->get()) === null) {
        $cache->set(($this->posts[$key] = $this->doGetPosts($limit, $offset, "DESC")));
      }
    }
    return $this->posts[$key];
  }

  /**
   * Get the topic's total post count.
   *
   * @return integer
   *   The topic's total post count.
   */
  public function getTotalPostCount() {
    if ($this->totalPostCount === null) {
      $cache = $this->container->getMemoryCache("forum-topic-{$this->id}-post-count");
      if (($this->totalPostCount = $cache->get()) === null) {
        $cache->set(($this->totalPostCount = (integer) Database::getConnection()->query(
          "SELECT COUNT(*) FROM `{$this->intl->code}_post` WHERE `topic_id` = {$this->id} LIMIT 1"
        )->fetch_all()[0][0]));
      }
    }
    return $this->totalPostCount;
  }

  /**
   * Increment the topic's post count.
   *
   * @return integer
   *   The incremented topic's post count.
   */
  public function incrementTotalPostCount() {
    return ($this->totalPostCount = $this->container->getMemoryCache()->increment($this->getCacheKey("post-count"), $this->getPostCount()));
  }

  /**
   * Set the topic's parent forum.
   *
   * @param \MovLib\Data\Forum\Forum $forum
   *   The topic's parent forum.
   * @return this
   */
  public function setForum(\MovLib\Data\Forum\Forum $forum) {
    $this->parents = [ ($this->forum = $forum) ];
    return $this;
  }


  // ------------------------------------------------------------------------------------------------------------------- Protected Methods


  /**
   * {@inheritdoc}
   */
  protected function getCacheKey($suffix) {
    return "forum-{$this->forum->id}-topic-{$this->id}-{$suffix}";
  }

  /**
   * Get posts from database for this topic.
   *
   * @param integer $limit
   *   The select's limit.
   * @param integer|null $offset
   *   The select's offset.
   * @param string|null $order
   *   The select's order.
   * @return array
   *   Posts from the database for this topic.
   */
  protected function doGetPosts($limit, $offset, $order) {
    return (new Select())
      ->select("id")
      ->select("created")
      ->select("creator_id")
      ->select("edited")
      ->select("editor_id")
      ->select("message")
      ->from("{$this->intl->code}_post")
      ->where("topic_id", $this->id)
      ->orderBy("created", $order)
      ->limit($limit, $offset)
      ->fetchObjects("\\MovLib\\Data\\Forum\\Post", [ $this->container, null, $this ])
    ;
  }

}
