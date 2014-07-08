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


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Index";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  public function init() {
    $this->initPage($this->intl->t("Forums"));
    $this->initLanguageLinks("/forums");
    $this->stylesheets[] = "forum";

    $this->container->getPersistentCache()->purge();
    $this->container->getMemoryCache()->purge();
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    $forums   = null;
    $dateTime = new DateTime($this->intl, $this, $this->session->userTimezone);

    /* @var $forum \MovLib\Data\Forum\Forum */
    foreach (Forum::getAll($this->container) as $forum) {
      $last = null;
      if (($topic = $forum->getLastTopic()) && ($post = $topic->getLastPost())) {
        $last =
          "<article class='cf'>" .
            "<h3 class='fl para'><a href='{$topic->route}'>{$topic->title}</a></h3>" .
            "<p class='fr'>{$this->intl->t("by {username} {time}", [
              "username" => "<a href='{}'>{}</a>",
              "time"     => $dateTime->formatRelative($post->created, $this->request->dateTime),
            ])}</p>" .
          "</article>"
        ;
      }
      else {
        $last = "<em>{$this->intl->t("No topics so far in this forum.")}</em>";
      }

      // Get the forum's category ID and check if we already created this offset in any of the previous iterations of
      // this loop, if not initialize so we can append.
      if (empty($forums[$forum->categoryId])) {
        $forums[$forum->categoryId] = null;
      }

      // We can simply append the rendered forum to the array offset because of the previous check.
      $forums[$forum->categoryId] =
        "<tr>" .
          "<td>{$forum->icon}</td>" .
          "<td>{$this->a($forum->route, $forum->title)}</td>" .
          "<td>{$last}</td>" .
          "<td class='tar'>{$this->intl->formatInteger($forum->getTopicCount())}</td>" .
          "<td class='tar'>{$this->intl->formatInteger($forum->getPostCount())}</td>" .
        "</tr>{$forums[$forum->categoryId]}"
      ;
    }

    // We can only continue with the rendering process if we have at least a single forum.
    if (isset($forums)) {
      $categories = null;

      // Go through all defined categories, but only render it if there is at least a single forum assigned to it.
      foreach (Forum::getCategories($this->intl) as $id => $title) {
        if (isset($forums[$id])) {
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
              "<tbody>{$forums[$id]}</tbody>" .
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
