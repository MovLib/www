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

use \MovLib\Data\User\FullUser;

/**
 * @todo Description of AbstractUserPage
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractUserPage extends \MovLib\Presentation\Page {
  use \MovLib\Presentation\TraitSidebar;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The user's collection route.
   *
   * @var string
   */
  protected $routeCollection;

  /**
   * The user's contact route.
   *
   * @var string
   */
  protected $routeContact;

  /**
   * The user's uploads route.
   *
   * @var string
   */
  protected $routeUploads;

  /**
   * The translated route to the users page.
   *
   * @var string
   */
  protected $routeUsers;

  /**
   * The user we are currently displaying.
   *
   * @var \MovLib\Data\User\FullUser
   */
  protected $user;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Initialize user sub page.
   *
   * Calling this method will load a full user object, instantiate the breadcrumb with a trail to the users and user's
   * page, and instantiate the sidebar with links to direct user sub pages.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @return this
   */
  protected function init() {
    global $i18n, $kernel;
    $kernel->stylesheets[] = "user";
    $this->user = new FullUser(FullUser::FROM_NAME, $_SERVER["USER_NAME"]);

    // Display the avatar within the header if the user has one.
    if ($this->user->imageExists === true) {
      $this->headingBefore = $this->getImage($this->user->getStyle(FullUser::STYLE_SPAN_01), true, null, [ "class" => "fr" ]);
    }

    // Just for ease of use.
    $routeArgs             = [ $this->user->filename ];
    $this->routeCollection = $i18n->r("/user/{0}/collection", $routeArgs);
    $this->routeContact    = $i18n->r("/user/{0}/contact", $routeArgs);
    $this->routeUploads    = $i18n->r("/user/{0}/uploads", $routeArgs);
    $this->routeUsers      = $i18n->rp("/users");

    // Initialize the breadcrumb with all parent trails.
    $this->initBreadcrumb([
      [ $this->routeUsers, $i18n->t("Users") ],
      [ $this->user->route, $this->user->name ],
    ]);

    // Initialize the sidebar with all direct sub pages.
    $this->sidebarInit([
      [ $this->routeUploads, $i18n->t("Uploads") ],
      [ $this->routeCollection, $i18n->t("Collection") ],
      [ $this->routeContact, $i18n->t("Contact") ],
    ]);

    return $this;
  }

}
