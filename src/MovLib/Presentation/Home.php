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
namespace MovLib\Presentation;

use \MovLib\Partial\Alert;

/**
 * The global home page for anonymous visitors.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Home extends \MovLib\Presentation\AbstractPresenter {

  /**
   * {@inheritdoc}
   * @global \MovLib\Core\I18n $i18n
   */
  public function getContent() {
    global $i18n;
    $articles = [];

    $articles[$i18n->t("Movies")] = "<p>{$i18n->t(
      "Discover new and old movies, find out about all related details like who was the director, when and where was " .
      "it released, what releases are available, find poster and lobby card art, plus many, many more …"
    )}</p>";

    $articles[$i18n->t("Persons")] = "<p>{$i18n->t(
      "You always wanted to collect all movies of a specific director, actor or any other movie related person? This " .
      "is the place for you to go. Find out all details about the person you admire, or simply add them yourself if " .
      "you are an expert."
    )}</p>";

    $articles[$i18n->t("Marketplace")] = "<p>{$i18n->t(
      "Searching for a specific release? Our marketplace is free, open, and built upon the exact release database. " .
      "This makes it easy for sellers to list their inventory and buyers are able to specify the exact version they " .
      "want."
    )}</p>";

    $articles[$i18n->t("Releases")] = "<p>{$i18n->t(
      "Insert text here …"
    )}</p>";

    $articles[$i18n->t("My {sitename}", [ "sitename" => $this->siteName ])] = "<p>{$i18n->t(
      "Insert text here …"
    )}</p><p><a class='btn btn-success btn-large' href='{$i18n->r("/profile/join")}'>{$i18n->t(
      "Join {sitename}", [ "sitename" => $this->siteName ])
    }</a></p>";

    $articles["<abbr title='{$i18n->t("Application Programming Interface")}'>{$i18n->t("API")}</abbr>"] = "<p>{$i18n->t(
      "The {sitename} API is a REST interface to access the free movie library. Specifically designed for all " .
      "developers out there. We want to keep the barrier as low as possible and ensure that everybody can use the " .
      "data we all collect here at {sitename}.",
      [ "sitename" => $this->siteName ]
    )}</p><p><a class='btn btn-primary btn-large' href='{$i18n->r("/help/api")}'>{$i18n->t(
      "Read the API documentation"
    )}</a></p>";

    $content = "";
    foreach ($articles as $title => $body) {
      $content .= "<article class='s s4 taj'><h2 class='tac'>{$title}</h2>{$body}</article>";
    }

    return "<div class='c'><div class='r'>{$content}</div></div>";
  }

  /**
   * {@inheritdoc}
   * @global \MovLib\Core\Config $config
   */
  protected function getHeadTitle() {
    global $config;
    return $config->siteNameAndSlogan;
  }

  /**
   * @inheritdoc
   * @global \MovLib\Core\I18n $i18n
   */
  public function getMainContent($content) {
    global $i18n;

    $noscript = new Alert(
      $i18n->t("Please activate JavaScript in your browser to experience our website with all its features."),
      $i18n->t("JavaScript Disabled")
    );

    return
      "<main id='m' role='main'>" .
        "<div id='banner'>" .
          "<h2 class='c'>{$i18n->t("Do you like movies?{0}Great, so do we!", [ "<br>" ])}</h2>" .
        "</div>" .
        "<noscript>{$noscript}</noscript>{$this->alerts}" .
      "</main>"
    ;
  }

  /**
   * @inheritdoc
   */
  protected function init() {
    $this->initPage($this->siteName);
    $this->initLanguageLinks("/");
    $this->initBreadcrumb();
    // A link to the current page would be redundant!
    unset($this->breadcrumb->menuitems[1]);
    $this->stylesheets[] = "home";
  }

}
