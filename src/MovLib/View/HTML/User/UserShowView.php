<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link http://movlib.org/ MovLib}.
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
namespace MovLib\View\HTML\User;

use \MovLib\View\HTML\AbstractView;

/**
 * @todo Description of UserShowView
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class UserShowView extends AbstractView {

  /**
   * The user presenter controlling this view.
   *
   * @var \MovLib\Presenter\UserPresenter
   */
  protected $presenter;

  /**
   * Get user profile view.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance.
   * @param \MovLib\Presenter\UserPresenter $userPresenter
   *   The user presenter controlling this view.
   */
  public function __construct($userPresenter) {
    global $i18n;
    parent::__construct($userPresenter, $i18n->t("Profile"));
    $this->stylesheets[] = "modules/user.css";
  }

  /**
   * {@inheritdoc}
   * @global \MovLib\Model\I18nModel $i18n
   * @global \MovLib\Model\SessionModel $user
   */
  public function getContent() {
    global $i18n, $user;
    $nav = $this->presenter->getSecondarySettingsNavigation();
    return
      "<div class='container'>" .
        "<div class='row'>" .
          "<aside class='span span--3'>{$this->getSecondaryNavigation($nav["title"], $nav["points"])}</aside>" .
          "<div class='span span--9'>" .
            "<h2>{$i18n->t("Your Account Summary")}</h2>" .
            "<div class='row'>" .
              "<div class='span span--3'>" .
                $this->a($i18n->r("/user/account-settings"), $this->presenter->profile->getAvatarImage(), [
                  "class" => "change-avatar",
                  "title" => "Change your avatar image.",
                ]) .
              "</div>" .
              "<dl class='span span--6'>" .
                "<dt class='visuallyhidden'>{$i18n->t("Username")}</dt><dd>{$this->presenter->profile->name}</dd>" .
                "<dt>{$i18n->t("Edits")}</dt><dd>{$this->presenter->profile->edits}</dd>" .
                "<dt>{$i18n->t("Reputation")}</dt><dd>@todo reputation counter</dd>" .
                "<dt>{$i18n->t("Mail")}</dt><dd>{$this->presenter->profile->mail}</dd>" .
                "<dt>{$i18n->t("Registration date")}</dt><dd>{$i18n->formatDate($this->presenter->profile->created, $this->presenter->profile->timezone)}</dd>" .
                "<dt>{$i18n->t("Last visit")}</dt><dd>{$i18n->formatDate($this->presenter->profile->access, $this->presenter->profile->timezone)}</dd>" .
              "</dl>" .
            "</div>" .
            "<h2>\$_SESSION</h2>" .
            "<pre>" . print_r($_SESSION, true) . "</pre>" .
            "<h2>\$user</h2>" .
            "<pre>" . print_r($this->presenter->profile, true) . "</pre>" .
            "<h2>\$_SERVER</h2>" .
            "<pre>" . print_r($_SERVER, true) . "</pre>" .
          "</div>" .
        "</div>" .
      "</div>"
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function getRenderedContent($tag = null, $attributes = null) {
    return parent::getRenderedContent("article");
  }

}
