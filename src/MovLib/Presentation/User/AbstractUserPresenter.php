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

/**
 * Defines the base class for user presenters.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractUserPresenter extends \MovLib\Presentation\AbstractPresenter {
  use \MovLib\Partial\SidebarTrait;

  /**
   * The user to present.
   *
   * @var \MovLib\Data\User\User
   */
  protected $user;

  /**
   * {@inheritdoc}
   */
  final protected function initPage($headTitle, $pageTitle = null, $breadcrumbTitle = null) {
    $this->stylesheets[] = "user";

    $this->user = new User($this->diContainerHTTP, $_SERVER["USER_NAME"]);

    $headTitle = $headTitle ? str_replace("{username}", $this->user->name, $headTitle) : $this->user->name;
    $pageTitle && ($pageTitle = str_replace("{username}", $this->user->name, $pageTitle));
    $breadcrumbTitle && ($breadcrumbTitle = str_replace("{username}", $this->user->name, $breadcrumbTitle));
    parent::initPage($headTitle, $pageTitle, $breadcrumbTitle);

    $this->breadcrumb->addCrumb($this->user->routeIndex, $this->intl->t("Users"));
    if ($this->request->path != $this->user->route) {
      $this->breadcrumb->addCrumb($this->user->route, $this->user->name);
    }

    if ($this->user->deleted) {
      $this->sidebarInit([
        [ $this->user->rp("/contributions"), $this->intl->t("Contributions") ],
        [ $this->user->rp("/uploads"), $this->intl->t("Uploads") ],
      ]);
    }
    else {
      $this->sidebarInit([
        [ $this->user->r("/collection"), $this->intl->t("Collection") ],
        [ $this->user->r("/wantlist"), $this->intl->t("Wantlist") ],
        [ $this->user->rp("/lists"), $this->intl->t("Lists") ],
        [ $this->user->rp("/contributions"), $this->intl->t("Contributions") ],
        [ $this->user->rp("/uploads"), $this->intl->t("Uploads") ],
        [ $this->user->r("/contact"), $this->intl->t("Contact") ],
      ]);
    }

    $langKey = $this->user->routeKey;
    if (($shortName = strtolower($this->shortName())) != "show") {
      $langKey .= "/{$shortName}";
      $this->headingBefore .= "<div class='r'><div class='s s11'>";
      $this->headingAfter  .= "</div><div class='s s1'>{$this->img($this->user->imageGetStyle("s1"))}</div></div>";
    }
    $this->initLanguageLinks($langKey, $this->user->routeArgs);

    return $this;
  }

}
