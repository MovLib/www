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
final class Topic implements \MovLib\Core\Routing\RoutingInterface {
  use \MovLib\Core\Routing\RoutingTrait;

  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Topic";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The topic's total post count.
   *
   * @var integer
   */
  public $countPosts;

  /**
   * Whether this topic is closed or not.
   *
   * @var boolean
   */
  public $closed = false;

  /**
   * The topic's date and time of the last edit.
   *
   * This property is <code>NULL</code> if it was never edited.
   *
   * @see ::$editorId
   * @var \MovLib\Component\DateTime|null
   */
  public $edited;

  /**
   * The topic's unique user identifier who last edited it.
   *
   * This property is <code>NULL</code> if it was never edited.
   *
   * @see ::$edited
   * @var integer|null
   */
  public $editorId;

  /**
   * The topic's unique forum identifier it belongs to.
   *
   * @var integer
   */
  public $forumId;

  /**
   * The topic's unique identifier.
   *
   * @param integer
   */
  public $id;

  /**
   * The topic's ISO 639-1 language code.
   *
   * @see ::__construct
   * @var string
   */
  protected $languageCode;

  /**
   * The topic's parent entities.
   *
   * @var array
   */
  public $parents = [];

  /**
   * Whether the topic is sticky or not.
   *
   * @var boolean
   */
  public $sticky = false;

  /**
   * The name of the database table that contains the topic.
   *
   * Each system language code has its own database table because there's no correlation between the languages.
   *
   * @see ::__construct
   * @var string
   */
  protected $tableName;

  /**
   * The topic's title.
   *
   * @var string
   */
  public $title;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new forum topic.
   *
   * @param string $languageCode
   *   The topic's (system) language code. The language code of a topic is usually provided by the request's language
   *   code. Note that the language code is used to determine which table is queried.
   * @param integer $id [optional]
   *   The topic's unique identifier to load, defaults to <code>NULL</code> and an empty topic is created.
   */
  public function __construct($languageCode, $id = null) {
    $this->languageCode = $languageCode;
    $this->tableName    = "{$languageCode}_topic";
    if ($id) {
      (new Select())
        ->select("id")
        ->select("closed")
        ->select("edited")
        ->select("editor_id")
        ->select("forum_id")
        ->select("sticky")
        ->select("title")
        ->from($this->tableName)
        ->where("id", $id)
        ->fetchInto($this)
      ;
      $this->parents[0] = new Forum($this->languageCode, $this->forumId);
      $this->setRoute("/forum/{$this->parents[0]->title}/topic/{0}", $this->id);
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get the topic's posts.
   *
   * @return array
   *   The topic's posts.
   */
  public function getPosts() {
    return (new Select())
      ->select("id")
      ->select("topic_id")
      ->select("created")
      ->select("creator_id")
      ->select("edited")
      ->select("editor_id")
      ->select("message")
      ->from("{$this->languageCode}_post")
      ->where("topic_id", $this->id)
      ->fetchObjects("\\MovLib\\Data\\Forum\\Post", [ $this->languageCode ])
    ;
  }

}
