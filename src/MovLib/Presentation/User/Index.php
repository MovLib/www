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

use \MovLib\Data\User\User;
use \MovLib\Data\User\Users;

/**
 * Latest users.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Index extends \MovLib\Presentation\Page {
  use \MovLib\Presentation\TraitPagination;
  use \MovLib\Presentation\TraitSidebar;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The users database instance.
   *
   * @var \MovLib\Data\User\Users
   */
  protected $users;

  /**
   * The translated route to user page's.
   *
   * @var string
   */
  protected $userRoute;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new users show presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @global \MovLib\Data\User\Session $session
   */
  public function __construct() {
    global $i18n, $kernel, $session;
    $this->users = new Users();
    $this->initPage($i18n->t("Users"));
    $this->initBreadcrumb();
    $this->initLanguageLinks("/users", null, true);
    $this->paginationInit($this->users->getTotalCount());
    $this->sidebarInit([
      [ $kernel->requestPath, $this->title ],
    ]);
    if ($session->isAuthenticated === false) {
      $this->headingBefore = "<a class='btn btn-large btn-success fr' href='{$i18n->r("/profile/join")}'>{$i18n->t("Join {sitename}", [ "sitename" => $kernel->siteName ])}</a>";
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  protected function getPageContent() {
    global $i18n;
    $list  = null;
    $users = $this->users->getOrderedByCreatedResult($this->paginationOffset, $this->paginationLimit);
    /* @var $user \MovLib\Data\User\User */
    while ($user = $users->fetch_object("\\MovLib\\Data\\User\\User")) {
      $list .=
        "<li class='s s5' itemscope itemtype='http://schema.org/Person'>" .
          "<a class='no-link r' href='{$i18n->r("/user/{0}", [ $user->filename ])}' itemprop='url'>" .
            $this->getImage($user->getStyle(User::STYLE_SPAN_01), false, [ "class" => "s s1", "itemprop" => "image" ]) .
            "<span class='s s4' itemprop='name'>{$user->name}</span>" .
          "</a>" .
        "</li>"
      ;
    }
    return "<div id='filter'>filter filter filter</div><ol class='hover-list no-list r'>{$list}</ol>";
  }

}
