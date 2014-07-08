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

use \MovLib\Core\Routing\Route;
use \MovLib\Data\Forum\Topic;
use \MovLib\Data\User\User;
use \MovLib\Partial\DateTime;

/**
 * Defines the base class for all topic index presentations.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractTopic extends \MovLib\Presentation\AbstractPresenter {
  use \MovLib\Partial\PaginationTrait;


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "AbstractTopic";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The topic to present.
   *
   * @var \MovLib\Data\Forum\Topic
   */
  protected $topic;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  public function init() {
    $this->container->getPersistentCache()->purge();
    $this->container->getMemoryCache()->purge();

    $this->topic = Topic::getInstance($this->container, $_SERVER["FORUM_ID"]);
    $this->initPage($this->topic->title);
    $this->breadcrumb->addCrumb($this->topic->forum->route, $this->topic->forum->title);
    $this->paginationInit($this->topic->getTotalPostCount());
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    $dateTime    = new DateTime($this->intl, $this, $this->session->userTimezone);
    $posts       = null;
    $routeReply  = $this->topic->getRoute("/reply");
    $routeReport = new Route($this->intl, "/forum/report");
    if ($this->topic->closed === false) {
      $this->headingBefore = "<a class='btn btn-large btn-success fr' href='{$routeReply}'>{$this->intl->t("Reply")}</a>";
    }
    /* @var $post \MovLib\Data\Forum\Post */
    foreach ($this->topic->getPosts($this->paginationLimit, $this->paginationOffset) as $post) {
      $poster = User::getInstance($this->container, $post->creatorId);
      $routeReply->query["post"] = $routeReport->query["post"] = $post->id;
      $actions = null;
      if ($this->session->isAuthenticated === true) {
        $edit = null;
        if ($this->session->userId === $poster->id || $this->session->isAdmin() === true) {
          $edit = "<a class='fr btn btn-small post-edit' href='{$post->r("/edit")}'>{$this->intl->t("Edit")}</a> ";
        }
        $actions =
          "<p class='cf post-actions'>" .
            "<a class='fl btn btn-small post-reply' href='{$routeReply->compile()}'>{$this->intl->t("Reply")}</a> " . // ico ico-discussion
            $edit .
            "<a class='fr btn btn-small post-report' href='{$routeReport->compile()}'>{$this->intl->t("Report")}</a>" . // ico ico-alert
          "</p>"
        ;
      }
      $posts .=
        "<article id='post-{$post->id}' class='post'>" .
          "<p class='cf post-meta'>" .
            $dateTime->formatRelative($post->created, $this->request->dateTime, [ "class" => "fl" ]) .
            "<a class='fr' href='#post-{$post->id}'>{$this->intl->t("#{0,number,integer}", $post->id)}</a>" .
          "</p>" .
          "<div class='post-body r'>" .
            "<div class='post-message s s10'>{$this->htmlDecode($post->message)}</div>" .
            "<div class='post-user s s2 tar'>" .
              "<a href='{$poster->route}'>{$poster->name}</a>" .
              $this->img($poster->imageGetStyle("s1")) .
            "</div>" .
          "</div>{$actions}" .
        "</article>"
      ;
    }
    return "<div class='c'>{$posts}</div>";
  }

  /**
   * {@inheritdoc}
   */
  public function getNoItemsContent() {
    // This can't happen!
  }

}
