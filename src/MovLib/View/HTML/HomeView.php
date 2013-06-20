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
namespace MovLib\View\HTML;

use \MovLib\View\HTML\AbstractView;

/**
 * The <b>Home</b> view contains the HTML layout for the MovLib home page.
 *
 * The home page should be generated without a single database call.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class HomeView extends AbstractView {

  /**
   * {@inheritdoc}
   */
  public function __construct($homePresenter) {
    parent::__construct($homePresenter, "MovLib");
    $this->stylesheets[] = "modules/home.css";
  }

  /**
   * The home view has no breadcrumb.
   *
   * @return string
   *   Empty string.
   */
  public function getBreadcrumb() {
    return "";
  }

  /**
   * {@inheritdoc}
   */
  public function getHeaderLogo() {
    global $i18n;
    return "<h1 id='logo' class='inline'>{$i18n->t("MovLib <small>the <em>free</em> movie library.</small>")}</h1>";
  }

  /**
   * {@inheritdoc}
   */
  public function getHeadTitle() {
    global $i18n;
    return
      $this->title .
      $i18n->t(", ", null, "The comma is used as separator character in the header title of the home page. The header title of the home page is built like a setence with the pattern '[sitename][separator][description]'. The content of this sentence in the first version of the software was 'MovLib, the free movie library.'. The trailing dot was part of the slogan but the comma is translated as separate string (to ensure that the slogan stays re-usable). Translate the comma to the equivalent character in your language. More information on this specific character can be found at <a href='//en.wikipedia.org/wiki/Comma'>Wikipedia.</a>") .
      $i18n->t("the free movie library.")
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    global $i18n;
    return
      "<div id='home-banner'>" .
        "<div class='container lead hero'>{$i18n->t("Do you like movies?<br>Great, so do we!")}</div>" .
      "</div>" .
      "<div class='container container--home'>" .
        "<div class='row'>" .
          "<article class='span span--4 span--home'>" .
            "<h2>{$i18n->t("Movies")}</h2>" .
            "<p>{$i18n->t("Discover new and old movies, find out about all related details like who was the director, when and where was it released, what releases are available, find poster and lobby card art, plus many, many more …")}</p>" .
          "</article>" .
          "<article class='span span--4 span--home'>" .
            "<h2>{$i18n->t("Persons")}</h2>" .
            "<p>{$i18n->t("You always wanted to collect all movies of a specific director, actor or any other movie related person? This is the place for you to go. Find out all details about the person you admire, or simply add them yourself if you are an expert.")}</p>" .
          "</article>" .
          "<article class='span span--4 span--home'>" .
            "<h2>{$i18n->t("Marketplace")}</h2>" .
            "<p>{$i18n->t("Searching for a specific release? Our marketplace is free, open, and built upon the exact release database. This makes it easy for sellers to list their inventory and buyers are able to specify the exact version they want.")}</p>" .
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
            "<p style='text-align:center'>{$this->a($i18n->r("/user/register"), $i18n->t("Sign up for a new account"), [ "class" => "button button--success button--large" ])}</p>" .
          "</article>" .
          "<article class='span span--4 span--home'>" .
            "<h2>{$i18n->t("<abbr title='Application Programming Interface'>API</abbr>")}</h2>" .
            "<p>{$i18n->t("The MovLib API is a REST interface to access the free movie library. Specifically designed for all developers out there. We want to keep the barrier as low as possible and ensure that everybody can use the data we all collect here at MovLib.")}</p>" .
            "<p style='text-align:center'><a class='button button--primary button--large' href='https://api.movlib.org/'>{$i18n->t("Read the API documentation")}</a></p>" .
          "</article>" .
        "</div>" .
      "</div>"
    ;
  }

  /**
   * We already have the <tt>h1</tt>-element in the header, the content shall not have another one. Additionally the
   * home view doesn't have a breadcrumb.
   *
   * @param null $tag
   *   Unused but declaration must be compatible.
   * @param null $attributes
   *   Unused but declaration must be compatible.
   * @return string
   *   The rendered view ready for print.
   */
  public function getRenderedContent($tag = null, $attributes = null) {
    return "<div id='content' class='{$this->getShortName()}-content' role='main'>{$this->getContent()}</div>";
  }

}
