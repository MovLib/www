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
  public function __construct($language) {
    parent::__construct($language, SITENAME);
    $this->addStylesheet("/assets/css/modules/home.css");
  }

  /**
   * {@inheritdoc}
   */
  public function getBreadcrumb() {
    return "";
  }

  /**
   * {@inheritdoc}
   */
  public function getHeaderLogo() {
    return "<h1 id='logo' class='inline'>" . SITENAME . " <small>" . __("the <em>free</em> movie library") . "</small></h1>";
  }

  /**
   * {@inheritdoc}
   */
  public function getHeadTitle() {
    return
      $this->title .
      //# The comma is used as separator character in the header title of the home page. The header title of the home
      //# page is built like a setence with the pattern "[sitename][separator][description]". The content of this
      //# sentence in the first version of the software was "MovLib, the free movie library.". The trailing dot was part
      //# of the slogan but the comma is translated as separate string (to ensure that the slogan stays re-usable).
      //# Translate the comma to the equivalent character in your language. More information on this specific character
      //# can be found at Wikipedia: https://en.wikipedia.org/wiki/Comma
      __(", ") .
      __("the free movie library.")
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function getRenderedContent() {
    return
      "<div id='home-banner'>" .
        "<div class='row'>" .
          "<div class='span span--1 lead hero'>" . __('Do you like movies?<br>Great, so do we!') . "</div>" .
        "</div>" .
      "</div>" .
      "<div class='row row--home'>" .
        "<article class='span span--3 span--home text-justify'>" .
          "<h2 class='text-center'>" . __("Movies") . "</h2>" .
          "<p>" . __("Discover new and old movies, find out about all related details like who was the director, when and where was it released, what releases are available, find poster and lobby card art, plus many, many more …") . "</p>" .
        "</article>" .
        "<article class='span span--3 span--home text-justify'>" .
          "<h2 class='text-center'>" . __("Persons") . "</h2>" .
          "<p>" . __("You always wanted to collect all movies of a specific director, actor or any other movie related person? This is the place for you to go. Find out all details about the person you admire, or simply add them yourself if you are an expert.") . "</p>" .
        "</article>" .
        "<article class='span span--3 span--home text-justify'>" .
          "<h2 class='text-center'>" . __("Marketplace") . "</h2>" .
          "<p>" . __("Searching for a specific release? Our marketplace is free, open, and built upon the exact release database. This makes it easy for sellers to list their inventory and buyers are able to specify the exact version they want.") . "</p>" .
        "</article>" .
      "</div>" .
      "<div class='row row--home'>" .
        "<article class='span span--3 span--home text-justify'>" .
          "<h2 class='text-center'>" . __("Releases") . "</h2>" .
          "<p>" . __("") . "</p>" .
        "</article>" .
        "<article class='span span--3 span--home text-justify'>" .
          "<h2 class='text-center'>" . sprintf(__("My %s"), SITENAME) . "</h2>" .
          "<p></p>" .
          "<p class='text-center'>{$this->a(__("sign-up", "route"), __("Sign up for a new account"), [ "class" => "button button--success button--large" ])}</p>" .
        "</article>" .
        "<article class='span span--3 span--home text-justify'>" .
          "<h2 class='text-center'>" . __("API") . "</h2>" .
          "<p>" . sprintf(__("The %s API is a REST interface to access the free movie library. Specifically designed for all developers out there. We want to keep the barrier as low as possible and ensure that everybody can use the data we all collect here at %s."), SITENAME, SITENAME) . "</p>" .
          "<p class='text-center'>{$this->a("//api.movlib.org", __("Read the API documentation"), [ "class" => "button button--primary button--large" ])}</p>" .
        "</article>" .
      "</div>"
    ;
  }

}
