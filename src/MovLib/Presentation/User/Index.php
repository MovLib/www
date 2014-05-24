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
namespace MovLib\Presentation\User;

use \MovLib\Partial\Alert;
use \MovLib\Data\User\UserSet;

/**
 * Defines the user index persenter.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Index extends \MovLib\Presentation\AbstractIndexPresenter {

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
    $this->set = new UserSet($this->diContainerHTTP);
    if ($this->session->isAuthenticated === false) {
      $this->headingBefore =
        "<a class='btn btn-large btn-success fr' href='{$this->intl->r("/profile/join")}'>{$this->intl->t(
          "Join {sitename}",
          [ "sitename" => $this->config->sitename ]
        )}</a>"
      ;
    }
    $this
      ->initPage($this->intl->t("Users"))
      ->initLanguageLinks("/users", null, true)
      ->sidebarInit([
        [ $this->request->path, $this->title, [ "class" => "ico ico-user" ] ],
        [ $this->intl->r("/user/random"), $this->intl->t("Random"), [ "class" => "ico ico-random" ] ],
      ])
      ->paginationInit($this->set->getTotalCount())
    ;
  }

  /**
   * {@inheritdoc}
   * @param \MovLib\Data\User\User $user {@inheritdoc}
   */
  protected function formatListingItem(\MovLib\Data\AbstractEntity $user, $delta) {
    return
      "<li typeof='Person'><article>" .
        "<a class='hover-item r' href='{$user->route}' property='url'>" .
          $this->img($user->imageGetStyle("s1"), [ "class" => "s s1", "property" => "image" ], false) .
          "<div class='s s9'><h2 class='link-color para' property='name'>{$user->name}</h2></div>" .
        "</a>" .
      "</article></li>"
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function getNoItemsContent() {
    $join = null;
    if ($this->session->isAuthenticated === false) {
      $join = "<p>{$this->intl->t(
        "Would you like {0}to join {sitename}{1}?",
        [ "<a href='{$this->intl->r("/profile/join")}'>", "</a>", "sitename" => $this->config->sitename ]
      )}</p>";
    }
    return new Alert(
      "<p>{$this->intl->t("We couldn’t find any users matching your filter criteria, or there simply isn’t any user available.")}</p>{$join}",
      $this->intl->t("No Users")
    );
  }

}
