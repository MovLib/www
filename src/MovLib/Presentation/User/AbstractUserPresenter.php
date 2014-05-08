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
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractUserPresenter extends \MovLib\Presentation\AbstractPresenter {
  use \MovLib\Partial\SidebarTrait;
  use \MovLib\Partial\SectionTrait;

  /**
   * The user to present.
   *
   * @var \MovLib\Data\User\User
   */
  protected $entity;

  /**
   * {@inheritdoc}
   */
  final protected function initPage($headTitle, $pageTitle = null, $breadcrumbTitle = null) {
    $this->stylesheets[] = "user";

    $this->entity = new User($this->diContainerHTTP, $_SERVER["USER_NAME"]);

    $headTitle = $headTitle ? str_replace("{username}", $this->entity->name, $headTitle) : $this->entity->name;
    $pageTitle && ($pageTitle = str_replace("{username}", $this->entity->name, $pageTitle));
    $breadcrumbTitle && ($breadcrumbTitle = str_replace("{username}", $this->entity->name, $breadcrumbTitle));
    parent::initPage($headTitle, $pageTitle, $breadcrumbTitle);

    $this->breadcrumb->addCrumb($this->entity->routeIndex, $this->intl->t("Users"));
    if ($this->request->path != $this->entity->route) {
      $this->breadcrumb->addCrumb($this->entity->route, $this->entity->name);
    }

    if ($this->entity->deleted) {
      $this->sidebarInit([
        [ $this->entity->route, $this->intl->t("View"), [ "class" => "ico ico-view" ]],
        [ $this->entity->r("/contributions"), "{$this->intl->t("Contributions")} <span class='fr'>{$this->intl->format("{0,number}", $this->entity->contributionCount)}</span>", [ "class" => "ico ico-database" ] ],
        [ $this->entity->r("/uploads"), "{$this->intl->t("Uploads")} <span class='fr'>{$this->intl->format("{0,number}", $this->entity->uploadCount)}</span>", [ "class" => "ico ico-upload" ] ],
      ]);
    }
    else {
      $this->sidebarInit([
        [ $this->entity->route, $this->intl->t("View"), [ "class" => "ico ico-view" ]],
        [ $this->entity->r("/collection"), $this->intl->t("Collection"), [ "class" => "ico ico-release" ] ],
        [ $this->entity->r("/wantlist"), $this->intl->t("Wantlist"), [ "class" => "ico ico-heart" ] ],
        [ $this->entity->r("/lists"), "{$this->intl->t("Lists")} <span class='fr'>{$this->intl->format("{0,number}", $this->entity->listCount)}</span>", [ "class" => "ico ico-ul" ] ],
        [ $this->entity->r("/contributions"), "{$this->intl->t("Contributions")} <span class='fr'>{$this->intl->format("{0,number}", $this->entity->contributionCount)}</span>", [ "class" => "ico ico-database" ] ],
        [ $this->entity->r("/uploads"), "{$this->intl->t("Uploads")} <span class='fr'>{$this->intl->format("{0,number}", $this->entity->uploadCount)}</span>", [ "class" => "ico ico-upload" ] ],
        [ $this->entity->r("/contact"), $this->intl->t("Contact"), [ "class" => "ico ico-email separator" ] ],
      ]);
    }

    $langKey = $this->entity->routeKey;
    if (($shortName = strtolower($this->shortName())) != "show") {
      $langKey .= "/{$shortName}";
      $this->headingBefore .= "<div class='r'><div class='s s11'>";
      $this->headingAfter  .= "</div><div class='s s1'>{$this->img($this->entity->imageGetStyle("s1"))}</div></div>";
    }
    $this->initLanguageLinks($langKey, $this->entity->routeArgs);

    return $this;
  }

}
