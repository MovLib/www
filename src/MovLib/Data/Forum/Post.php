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
 * Defines the forum post object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Post extends AbstractBase {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Post";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Public Properties


  /**
   * The post's creation date and time.
   *
   * @var \MovLib\Component\DateTime
   */
  public $created;

  /**
   * The post's unique user identifier who created this post.
   *
   * @var integer
   */
  public $creatorId;

  /**
   * The post's date and time of the last edit.
   *
   * This property is <code>NULL</code> if it was never edited.
   *
   * @see ::$editorId
   * @var \MovLib\Component\DateTime|null
   */
  public $edited;

  /**
   * The post's unique user identifier who last edited it.
   *
   * This property is <code>NULL</code> if it was never edited.
   *
   * @see ::$edited
   * @var integer|null
   */
  public $editorId;

  /**
   * The post's message.
   *
   * @var string
   */
  public $message;

  /**
   * The post's topic it belongs to.
   *
   * @var \MovLib\Data\Forum\Topic
   */
  public $topic;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new forum post.
   *
   * @param \MovLib\Core\Container $container
   *   The dependency injection container.
   * @param integer $id [optional]
   *   The post's unique identifier to load, defaults to <code>NULL</code> and an empty post is created.
   * @param \MovLib\Data\Forum\Topic $parentTopic [internal]
   *   The post's parent topic, defaults to <code>NULL</code> and the post is loaded.
   * @throws \InvalidArgumentException
   *   If <var>$topic</var> isn't the actual parent topic of this post.
   * @throws \MovLib\Exception\ClientException\NotFoundException
   *   If no post was found for <var>$id</var>.
   */
  public function __construct(\MovLib\Core\Container $container, $id = null, $parentTopic = null) {
    parent::__construct($container);
    if ($id) {
      (new Select())
        ->select("id")
        ->select("topic_id", [ "property" => "topic" ])
        ->select("created")
        ->select("creator_id")
        ->select("edited")
        ->select("editor_id")
        ->select("message")
        ->from("{$this->intl->code}_post")
        ->where("id", $id)
        ->fetchInto($this)
      ;
      // @devStart
      if (isset($parentTopic) && $parentTopic->id !== $this->topic) {
        throw new \InvalidArgumentException("The topic isn't the post's parent.");
      }
      // @devEnd
    }
    if ($this->id) {
      $this->setTopic($parentTopic ?: new Topic($this->container, $this->topic));
      $this->setRoute($this->intl, $this->topic->forum, "/forum/{0}/topic/{1}/post-{2}", [ $this->topic->id, $this->id ]);
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Public Methods


  /**
   * Create new post.
   *
   * @param \MovLib\Data\Forum\Topic $topic
   *   The topic this post belongs to.
   * @param \MovLib\Component\DateTime $created
   *   The date and time this post was created.
   * @param \MovLib\Data\User\User $creator
   *   The user who created this post.
   * @return this
   * @throws \mysqli_sql_exception
   */
  public function create(\MovLib\Data\Forum\Topic $topic, \MovLib\Component\DateTime $created, \MovLib\Data\User\User $creator) {
    $connection = Database::getConnection();

    // No need for the insert object at this point, this is called from topic and executed within a transaction.
    $connection->real_query(
      "INSERT INTO `{$this->tableName}` (`topic_id`, `created`, `creator_id`, `message`) VALUES ({$topic->id}, '{$created}', {$creator->id}, '{$connection->real_escape_string($this->message)}')"
    );

    // Export new values to class scope.
    $this->id        = $connection->insert_id;
    $this->topicId   = $topic->id;
    $this->created   = $created;
    $this->creatorId = $created->id;

    return $this;
  }

  /**
   * Edit the post.
   *
   * @param \MovLib\Component\DateTime $edited
   *   The date and time the post was edited, usually the request's date and time.
   * @param \MovLib\Data\User\User $editor
   *   The user who edited the post.
   * @return this
   * @throws \mysqli_sql_exception
   */
  public function edit(\MovLib\Component\DateTime $edited, \MovLib\Data\User\User $editor) {
    $connection = Database::getConnection();
    $connection->autocommit(false);
    try {
      $connection->real_query("UPDATE `{$this->tableName}` SET `edited` = '{$edited}', `editor_id` = {$editor->id}, `message` = '{$connection->real_escape_string($this->message)}' WHERE `id` = {$this->id}");
      if ($connection->affected_rows !== 1) {
        throw new \mysqli_sql_exception("Wrong number of affected rows: '{$connection->affected_rows}'");
      }
      $connection->commit();
    }
    catch (\Exception $e) {
      $connection->rollback();
      throw $e;
    }
    finally {
      $connection->autocommit(true);
    }
    return $this;
  }

  /**
   * Set the post's topic.
   *
   * @param \MovLib\Data\Forum\Topic $topic
   *   The post's topic.
   * @return this
   */
  public function setTopic(\MovLib\Data\Forum\Topic $topic) {
    $this->parents = [ ($this->topic = $topic), $topic->forum ];
    return $this;
  }


  // ------------------------------------------------------------------------------------------------------------------- Protected Methods


  /**
   * {@inheritdoc}
   */
  protected function getCacheKey($suffix) {
    return "forum-{$this->topic->forum->id}-topic-{$this->topic->id}-post-{$this->id}-{$suffix}";
  }

}
