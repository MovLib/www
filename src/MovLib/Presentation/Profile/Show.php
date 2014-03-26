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
namespace MovLib\Presentation\Profile;

use \MovLib\Data\User\FullUser;
use \MovLib\Presentation\Partial\DateTime;

/**
 * User account summary for logged in user's.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Show extends \MovLib\Presentation\AbstractPresenter {
  use \MovLib\Presentation\TraitSidebar;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The user we are currently displaying.
   *
   * @var \MovLib\Data\User\FullUser
   */
  protected $user;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new user show presentation.
   *
   * @throws \MovLib\Presentation\Error\Unauthorized
   */
  public function __construct() {
    $session->checkAuthorization($this->intl->t("You must be signed in to view your profile."));
    $this->init($this->intl->t("Profile"), "/profile");
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get the page's content.
   *
   * @return string
   *   The page's content.
   */
  protected function getPageContent() {
    $joined = new DateTime($this->user->created);
    $visited = new DateTime($this->user->access);
    return
      "<h2>{$this->intl->t("Your Account Summary")}</h2>" .
      "<div class='r'>" .
        "<dl class='dl--horizontal s s7'>" .
          "<dt>{$this->intl->t("Username")}</dt><dd>{$this->user->name}</dd>" .
          "<dt>{$this->intl->t("User ID")}</dt><dd>{$this->user->id}</dd>" .
          "<dt>{$this->intl->t("Edits")}</dt><dd>{$this->user->edits}</dd>" .
          "<dt>{$this->intl->t("Reputation")}</dt><dd>{$this->user->reputation}</dd>" .
          "<dt>{$this->intl->t("Email Address")}</dt><dd>{$this->user->email}</dd>" .
          "<dt>{$this->intl->t("Joined")}</dt><dd>{$joined}</dd>" .
          "<dt>{$this->intl->t("Last visit")}</dt><dd>{$visited}</dd>" .
        "</dl>" .
        "<div class='s s2'>{$this->getImage($this->user->getStyle(), $this->intl->r("/user/{0}", [ $this->user->filename ]))}</div>" .
      "</div>"
    ;
  }

  /**
   * Initialize profile page.
   *
   * This automatically calls {@see initPage()}, {@see initBreadcrumb()}, {@see initLanguageLinks()}, {@see initSidebar()},
   * and instantiates a {@see \MovLib\Data\User\Full} object (available via {@see Show::$user}).
   *
   * @param string $title
   *   The translated profile page's title.
   * @param string $route
   *   The route key of this profile page.
   * @param array $breadcrumbs [optional]
   *   Numeric array containing additional breadcrumbs to put between home and the current page.
   * @return this
   */
  protected function init($title, $route, array $breadcrumbs = []) {
    $this->initPage($title);
    $this->initBreadcrumb($breadcrumbs);
    $this->initLanguageLinks($route);

    $sidebar = [
      [ $this->intl->r("/profile"), $this->intl->t("Profile"), [ "class" => "ico ico-user" ] ],
      [ $this->intl->r("/profile/messages"), $this->intl->t("Messages"), [ "class" => "ico ico-email" ] ],
      [ $this->intl->r("/profile/collection"), $this->intl->t("Collection"), [ "class" => "ico ico-release" ] ],
      [ $this->intl->r("/profile/wantlist"), $this->intl->t("Wantlist"), [ "class" => "ico ico-heart" ] ],
      [ $this->intl->r("/profile/lists"), $this->intl->t("Lists"), [ "class" => "ico ico-ul" ] ],
      [ $this->intl->r("/profile/watchlist"), $this->intl->t("Watchlist"), [ "class" => "separator ico ico-view" ] ],
      [ $this->intl->r("/profile/account-settings"), $this->intl->t("Account"), [ "class" => "ico ico-settings" ] ],
      [ $this->intl->r("/profile/notification-settings"), $this->intl->t("Notifications"), [ "class" => "ico ico-notification" ] ],
      [ $this->intl->r("/profile/email-settings"), $this->intl->t("Email"), [ "class" => "ico ico-email" ] ],
      [ $this->intl->r("/profile/password-settings"), $this->intl->t("Password"), [ "class" => "ico ico-lock" ] ],
      [ $this->intl->r("/profile/danger-zone"), $this->intl->t("Danger Zone"), [ "class" => "ico ico-alert" ] ],
    ];
    $this->sidebarInit($sidebar);

    $this->user = new FullUser(FullUser::FROM_ID, $session->userId);

    return $this;
  }

}
