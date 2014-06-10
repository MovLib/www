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
namespace MovLib\Presentation\Forum;

use \MovLib\Data\Forum\Forum;
use \MovLib\Partial\DateTime;

/**
 * Defines the forum index presentation.
 *
 * @route /forums
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Index extends \MovLib\Presentation\AbstractPresenter {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Index";
  // @codingStandardsIgnoreEnd

  /**
   * {@inheritdoc}
   */
  public function init() {
    $this->initPage($this->intl->t("Forums"));
    $this->initLanguageLinks("/forums");
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    $forums         = Forum::getAll($this->container);
    $renderedForums = null;
    $dateTime       = new DateTime($this->intl, $this, $this->session->userTimezone);

    /* @var $forum \MovLib\Data\Forum\Forum */
    foreach ($forums as $forum) {
      // Usually each forum has a last topic, but this might not be the case if a forum was just created and we have
      // to honor that case, even if it's rare.
      if ($forum->lastTopic->id) {
        // This might be the last topic because somebody replied to it. The title itself always links to the first
        // post in the topic, but we want to add a link to the last post in case we have one. Note that the last post
        // is automatically corrected to the last post a signed in user has read, that's why we had to pass the
        // session to the data layer. A special table keeps track of the posts a user has read.
        if ($forum->lastPost->id) {
          $lastPost = "lastPost";
        }
        else {
          $lastPost = null;
        }

        $last =
          "<h4 class='last-title para'>{$forum->lastTopic->title}</h4>{$lastPost}" .
          "<p class='last-meta'>{$this->intl->t("by {username} {time}", [
            "username" => "<a href='{}'>{$forum->lastPost->username}</a>",
            "time"     => $dateTime->formatRelative($forum->lastPost->created)
          ])}</p>"
        ;
      }
      // Rare but possible, we're using an article in our HTML to represent the last topic, therefore we have to
      // include some mark-up for robots and screen readers that explains that there actually isn't any content.
      else {
        $last = "<h4 class='last-title para'>{$this->intl->t("No topics so far in this forum.")}</h4>";
      }

      // Get the forum's category ID and check if we already created this offset in any of the previous iterations of
      // this loop, if not initialize with NULL value.
      if (empty($renderedForums[$forum->categoryId])) {
        $renderedForums[$forum->categoryId] = null;
      }

      // We can simply append the rendered forum to the array offset because of the previous check.
      $renderedForums[$forum->categoryId] .=
        "<article class='dtr'>" .
          "<div class='dtc forum-icon'>{$forum->icon}</div>" .
          "<h3 class='dtc forum-title para'>{$this->a($forum->route, $forum->title)}</h3>" .
          "<article class='dtc forum-last'>{$last}</article>" .
          "<div class='dtc forum-topics'>{$this->intl->formatInteger($forum->countTopics)}</div>" .
          "<div class='dtc forum-posts'>{$this->intl->formatInteger($forum->countPosts)}</div>" .
        "</article>"
      ;
    }

    // We can only continue with the rendering process if we have at least a single forum.
    if ($renderedForums) {
      $categories = null;

      // Go through all defined categories, but only render it if there is at least a single forum assigned to it.
      foreach (Forum::getCategories($this->intl) as $id => $title) {
        if (isset($renderedForums[$id])) {
          $categories .= "<h2>{$title}</h2><div class='dt w100'>{$renderedForums[$id]}</div>";
        }
      }

      // That's it, we're finally done.
      return "<div class='c forums'>{$categories}</div>";
    }

    // This is actually only possible if something went terribly wrong or if this is a new installation.
    $this->log->critical("No forums were found, please seed the forums or create some with the admin command.");
    $this->alertError(
      $this->intl->t("No Forums"),
      $this->intl->t("Please create new forums with the appropriate admin command.")
    );
  }

}
