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
    $this->stylesheets[] = "forum";
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    $forums         = Forum::getAll();
    $renderedForums = null;
    $dateTime       = new DateTime($this->intl, $this, $this->session->userTimezone);

    /* @var $forum \MovLib\Data\Forum\Forum */
    foreach ($forums as $forum) {
      if ($forum->lastTopic->id) {
        // Provide direct link to the last post (read by the signed in user) in the topic.
        $lastPost = $forum->lastPost->id ? $this->a("#", ">") : null;
        $last =
          "<article class='cf'>" .
            "<h3 class='fl para'>{$forum->lastTopic->title}</h3>{$lastPost}" .
            "<p class='fr'>{$this->intl->t("by {username} {time}", [
              "username" => "<a href='{}'>{$forum->lastPost->creator->name}</a>",
              "time"     => $dateTime->formatRelative($forum->lastPost->created, $this->request->dateTime)
            ])}</p>" .
          "</article>"
        ;
      }
      else {
        $last = "<em>{$this->intl->t("No topics so far in this forum.")}</em>";
      }

      // Get the forum's category ID and check if we already created this offset in any of the previous iterations of
      // this loop, if not initialize with NULL value.
      if (empty($renderedForums[$forum->categoryId])) {
        $renderedForums[$forum->categoryId] = null;
      }

      // We can simply append the rendered forum to the array offset because of the previous check.
      $renderedForums[$forum->categoryId] .=
        "<tr>" .
          "<td>{$forum->icon}</td>" .
          "<td>{$this->a($forum->route, $forum->title)}</td>" .
          "<td>{$last}</td>" .
          "<td class='tar'>{$this->intl->formatInteger($forum->countTopics)}</td>" .
          "<td class='tar'>{$this->intl->formatInteger($forum->countPosts)}</td>" .
        "</tr>"
      ;
    }

    // We can only continue with the rendering process if we have at least a single forum.
    if ($renderedForums) {
      $categories = null;

      // Go through all defined categories, but only render it if there is at least a single forum assigned to it.
      foreach (Forum::getCategories($this->intl) as $id => $title) {
        if (isset($renderedForums[$id])) {
          $categories .=
            "<table class='forums'>" .
              "<caption class='h2'>{$title}</caption>" .
              "<colgroup>" .
                "<col class='forum-icon'>" .
                "<col class='forum-title'>" .
                "<col class='forum-last'>" .
                "<col class='forum-counts'>" .
                "<col class='forum-counts'>" .
              "</colgroup>" .
              "<thead><tr>" .
                "<th colspan='2'>{$this->intl->t("Forum")}</th>" .
                "<th>{$this->intl->t("Last Post")}</th>" .
                "<th>{$this->intl->t("Topics")}</th>" .
                "<th>{$this->intl->t("Posts")}</th>" .
              "</tr></thead>" .
              "<tbody>{$renderedForums[$id]}</tbody>" .
            "</table>"
          ;
        }
      }

      // That's it, we're finally done.
      return "<div class='c'>{$categories}</div>";
    }

    // This is actually only possible if something went terribly wrong or if this is a new installation.
    $this->log->critical("No forums were found, please seed the forums or create some with the admin command.");
    $this->alertError(
      $this->intl->t("No Forums"),
      $this->intl->t("Please create new forums with the appropriate admin command.")
    );
  }

}
