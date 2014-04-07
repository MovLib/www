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
final class Home extends \MovLib\Presentation\AbstractPresenter {

  /**
   * {@inheritdoc}
   */
  public function init() {
    $this->initPage($this->config->sitename);
    $this->initBreadcrumb();
    // A link to the current page would be redundant!
    unset($this->breadcrumb->menuitems[1]);
    $this->initLanguageLinks("/");
    $this->stylesheets[] = "home";
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    $articles = [];

    $articles[$this->intl->t("Movies")] = "<p>{$this->intl->t(
      "Discover new and old movies, find out about all related details like who was the director, when and where was " .
      "it released, what releases are available, find poster and lobby card art, plus many, many more …"
    )}</p>";

    $articles[$this->intl->t("Persons")] = "<p>{$this->intl->t(
      "You always wanted to collect all movies of a specific director, actor or any other movie related person? This " .
      "is the place for you to go. Find out all details about the person you admire, or simply add them yourself if " .
      "you are an expert."
    )}</p>";

    $articles[$this->intl->t("Marketplace")] = "<p>{$this->intl->t(
      "Searching for a specific release? Our marketplace is free, open, and built upon the exact release database. " .
      "This makes it easy for sellers to list their inventory and buyers are able to specify the exact version they " .
      "want."
    )}</p>";

    $articles[$this->intl->t("Releases")] = "<p>{$this->intl->t(
      "Insert text here …"
    )}</p>";

    $articles[$this->intl->t("My {sitename}", [ "sitename" => $this->config->sitename ])] = "<p>{$this->intl->t(
      "Insert text here …"
    )}</p><p><a class='btn btn-success btn-large' href='{$this->intl->r("/profile/join")}'>{$this->intl->t(
      "Join {sitename}", [ "sitename" => $this->config->sitename ])
    }</a></p>";

    $articles["<abbr title='{$this->intl->t("Application Programming Interface")}'>{$this->intl->t("API")}</abbr>"] = "<p>{$this->intl->t(
      "The {sitename} API is a REST interface to access the free movie library. Specifically designed for all " .
      "developers out there. We want to keep the barrier as low as possible and ensure that everybody can use the " .
      "data we all collect here at {sitename}.",
      [ "sitename" => $this->config->sitename ]
    )}</p><p><a class='btn btn-primary btn-large' href='{$this->intl->r("/help/api")}'>{$this->intl->t(
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
   */
  protected function getHeadTitle() {
    return $this->config->sitename;
  }

  /**
   * {@inheritdoc}
   */
  public function getMainContent($content) {
    $noscript = new Alert(
      $this->intl->t("Please activate JavaScript in your browser to experience our website with all its features."),
      $this->intl->t("JavaScript Disabled")
    );

    return
      "<main id='m' role='main'>" .
        "<div id='banner'>" .
          "<h2 class='c'>{$this->intl->t("Do you like movies?{0}Great, so do we!", [ "<br>" ])}</h2>" .
        "</div>" .
        "<noscript>{$noscript}</noscript>{$this->alerts}{$content}" .
      "</main>"
    ;
  }

}
