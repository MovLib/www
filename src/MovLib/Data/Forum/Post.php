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
final class Post {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Post";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


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
   * The post's unique identifier.
   *
   * @var integer
   */
  public $id;

  /**
   * The post's ISO 639-1 language code.
   *
   * @see ::__construct
   * @var string
   */
  public $languageCode;

  /**
   * The post's message.
   *
   * @var string
   */
  public $message;

  /**
   * The name of the database table that contains the post.
   *
   * Each system language code has its own database table because there's no correlation between the languages.
   *
   * @see ::__construct
   * @var string
   */
  protected $tableName;

  /**
   * The post's unique topic identifier it belongs to.
   *
   * @var integer
   */
  public $topicId;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new forum post.
   *
   * @param string $languageCode
   *   The post's (system) language code. The language code of a post is usually provided by the topic a post belongs
   *   to, and the request's language code should be used in case no topic exists for a post. Note that the language
   *   code is used to determine which table is queried.
   * @param integer $id [optional]
   *   The post's unique identifier to load, defaults to <code>NULL</code> and an empty post is created.
   * @throws \MovLib\Exception\ClientException\NotFoundException
   *   If no post was found for <var>$id</var>.
   */
  public function __construct($languageCode, $id = null) {
    $this->languageCode = $languageCode;
    $this->tableName    = "{$languageCode}_post";
    if ($id) {
      (new Select())
        ->select("id")
        ->select("topic_id")
        ->select("created")
        ->select("creator_id")
        ->select("edited")
        ->select("editor_id")
        ->select("message")
        ->from($this->tableName)
        ->where("id", $id)
        ->fetchInto($this)
      ;
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Create new post.
   *
   * @param \MovLib\Data\Forum\Topic $topic
   *   The topic this post belongs to.
   * @param \MovLib\Component\DateTime $created
   *   The date and time this post was created.
   * @param \MovLib\Data\User\User $creator
   *   The user who created this post.
   * @param string $message
   *   The post's message.
   * @return this
   * @throws \mysqli_sql_exception
   */
  public function create(\MovLib\Data\Forum\Topic $topic, \MovLib\Component\DateTime $created, \MovLib\Data\User\User $creator, $message) {
    $connection = Database::getConnection();

    // @devStart
    // @codeCoverageIgnoreStart
    assert(!empty($message), "\$message cannot be empty.");
    // @codeCoverageIgnoreEnd
    // @devEnd

    // No need for the insert object at this point, this is called from topic and executed within a transaction.
    $connection->real_query("INSERT INTO `{$this->tableName}` (`topic_id`, `created`, `creator_id`, `message`) VALUES ({$topic->id}, '{$created}', {$creator->id}, '{$connection->real_escape_string($message)}')");

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
   * @param string $message
   *   The new post message.
   * @return this
   * @throws \mysqli_sql_exception
   */
  public function edit(\MovLib\Component\DateTime $edited, \MovLib\Data\User\User $editor, $message) {
    // @devStart
    // @codeCoverageIgnoreStart
    assert(!empty($message), "\$message cannot be empty.");
    assert($message !== $this->message, "\$message cannot be the same as current message.");
    // @codeCoverageIgnoreEnd
    // @devEnd
    $connection = Database::getConnection();
    $connection->autocommit(false);
    try {
      $connection->real_query("UPDATE `{$this->tableName}` SET `edited` = '{$edited}', `editor_id` = {$editor->id}, `message` = '{$connection->real_escape_string($message)}' WHERE `id` = {$this->id}");
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

}
