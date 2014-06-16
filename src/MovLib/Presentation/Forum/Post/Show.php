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
namespace MovLib\Presentation\Forum\Post;

use \MovLib\Data\Forum\Post;

/**
 * Defines the post show presentation.
 *
 * Allows the presentation of a single post without knowing it's topic or forum. Note that there's still the dependency
 * for the language code which is provided by the subdomain. We don't know which post is which in case we have no
 * language code, we could only display a selection list to the client.
 *
 * @route /forum/post/{id}
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Show extends \MovLib\Presentation\AbstractPresenter {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Show";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The user who created the post.
   *
   * @var \MovLib\Data\User\User
   */
  protected $creator;

  /**
   * The user who last edited the post.
   *
   * @var \MovLib\Data\User\User|null
   */
  protected $editor;

  /**
   * The post's forum.
   *
   * @var \MovLib\Data\Forum\Forum
   */
  protected $forum;

  /**
   * The post to present.
   *
   * @var \MovLib\Data\Forum\Post
   */
  protected $post;

  /**
   * The post's topic.
   *
   * @var \MovLib\Data\Forum\Topic
   */
  protected $topic;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  public function init() {
    $this->post = new Post($this->intl->languageCode, $_SERVER["FORUM_ID"]);
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    return "<div class='c'>" . \Krumo::dump($this->post, KRUMO_RETURN) . "</div>";
  }

}
