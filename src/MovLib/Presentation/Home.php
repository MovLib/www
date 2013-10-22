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
namespace MovLib\Presentation;

/**
 * The global home page for anonymous visitors.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Home extends \MovLib\Presentation\Page {

  /**
   * Instantiate new special home presentation page.
   */
  public function __construct() {
    $this->init("MovLib");
    $this->stylesheets[] = "modules/home.css";
  }

  /**
   * @inheritdoc
   */
  protected function getBreadcrumb() {
    $breadcrumb = parent::getBreadcrumb();
    // A link to the current page would be redundant!
    unset($breadcrumb->menuitems[1]);
    return $breadcrumb;
  }

  /**
   * @inheritdoc
   */
  protected function getHeaderLogo() {
    global $config, $i18n;
    return
      "<h1 class='span' id='header__logo'>" .
        "<img alt='{$i18n->t("{0}, the free movie library.", [ "MovLib" ])}' height='42' id='logo' src='//{$config->domainStatic}/asset/img/logo/vector.svg' width='42'> MovLib" .
      "</h1>"
    ;
  }

  /**
   * @inheritdoc
   */
  protected function getHeadTitle() {
    global $i18n;
    return $i18n->t("{0}, the free movie library.", [ "MovLib" ]);
  }

  /**
   * @inheritdoc
   */
  protected function getWrappedContent() {
    global $config, $i18n;
    return
      "<div class='{$this->id}-content' id='content' role='main'>" .
        "<div id='home-banner'>" .
          "<div class='container lead hero'>{$i18n->t("Do you like movies?<br>Great, so do we!")}</div>" .
        "</div>" .
        "<div id='alerts'>{$this->alerts}</div>" .
        "<div class='container container--home'>" .
          "<div class='row'>" .
            "<article class='span span--4 span--home'>" .
              "<h2>{$i18n->t("Movies")}</h2>" .
              "<p>{$i18n->t(
                "Discover new and old movies, find out about all related details like who was the director, when and " .
                "where was it released, what releases are available, find poster and lobby card art, plus many, many more …"
              )}</p>" .
            "</article>" .
            "<article class='span span--4 span--home'>" .
              "<h2>{$i18n->t("Persons")}</h2>" .
              "<p>{$i18n->t(
                "You always wanted to collect all movies of a specific director, actor or any other movie related " .
                "person? This is the place for you to go. Find out all details about the person you admire, or simply " .
                "add them yourself if you are an expert."
              )}</p>" .
            "</article>" .
            "<article class='span span--4 span--home'>" .
              "<h2>{$i18n->t("Marketplace")}</h2>" .
              "<p>{$i18n->t(
                "Searching for a specific release? Our marketplace is free, open, and built upon the exact release " .
                "database. This makes it easy for sellers to list their inventory and buyers are able to specify the " .
                "exact version they want."
              )}</p>" .
            "</article>" .
          "</div>" .
          "<div class='row'>" .
            "<article class='span span--4 span--home'>" .
              "<h2>{$i18n->t("Releases")}</h2>" .
              "<p></p>" .
            "</article>" .
            "<article class='span span--4 span--home'>" .
              "<h2>{$i18n->t("My MovLib")}</h2>" .
              "<p></p>" .
              "<p>{$this->a(
                $i18n->r("/users/registration"),
                $i18n->t("Sign up for a new account"),
                [ "class" => "button button--success button--large" ]
              )}</p>" .
            "</article>" .
            "<article class='span span--4 span--home'>" .
              "<h2>{$i18n->t("<abbr title='Application Programming Interface'>API</abbr>")}</h2>" .
              "<p>{$i18n->t(
                "The MovLib API is a REST interface to access the free movie library. Specifically designed for all " .
                "developers out there. We want to keep the barrier as low as possible and ensure that everybody can " .
                "use the data we all collect here at MovLib."
              )}</p>" .
              "<p><a class='button button--primary button--large' href='//{$config->domainAPI}/'>" .
                $i18n->t("Read the API documentation") .
              "</a></p>" .
            "</article>" .
          "</div>" .
        "</div>" .
      "</div>"
    ;
  }

}
