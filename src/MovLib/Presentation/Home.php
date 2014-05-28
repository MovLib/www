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

/**
 * The global home page for anonymous visitors.
 *
 * @route /
 * @routeCache home
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Home extends \MovLib\Presentation\AbstractPresenter {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Home";
  // @codingStandardsIgnoreEnd

  /**
   * {@inheritdoc}
   */
  public function init() {
    $this->initPage($this->config->sitename);
    $this->initLanguageLinks("/");
    $this->stylesheets[] = "home";
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    $tSitenameArg = [ "sitename" => $this->config->sitename ];
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
      "The exact release database of {sitename} allows you to search for specific releases, check if a release is " .
      "truly uncut, or simply lookup some technical specs of a release. It allows you to keep track of the exact " .
      "releases you own, or the ones you’d like to buy. Cinema, DVD, TV, Digital Download, … doesn’t matter, all " .
      "forms and formats have a place at {sitename}.",
      $tSitenameArg
    )}</p>";

    $articles[$this->intl->t("My {sitename}", $tSitenameArg)] = "<p>{$this->intl->t(
      "If you join {sitename} you’ll be able to use the extensive statistical tools that we provide to you for free. " .
      "You can easily keep track of all the releases you own, the ones you want to buy, create lists (like “All " .
      "movies with cats as main actors”), track your contributions, rate movies and series, and much more …",
      $tSitenameArg
    )}</p>";

    $articles["<abbr title='{$this->intl->t("Application Programming Interface")}'>{$this->intl->t("API")}</abbr>"] = "<p>{$this->intl->t(
      "The {sitename} API is a REST interface to access the free movie library. Specifically designed for all " .
      "developers out there. We want to keep the barrier as low as possible and ensure that everybody can use the " .
      "data we all collect here at {sitename}.",
      $tSitenameArg
    )}</p>";

    $content = null;
    foreach ($articles as $title => $body) {
      $content .= "<article class='s s4'><h2 class='tac'>{$title}</h2>{$body}</article>";
    }

    return
      "<div class='c'><div class='r'>{$content}<div class='s s4 tac'>{$this->a(
        "/releases/create", $this->intl->t("Add a Release"), [ "class" => "btn btn-info btn-large" ]
      )}</div><div class='s s4 tac'>{$this->a(
        "/profile/join", $this->intl->t("Join {sitename}", $tSitenameArg), [ "class" => "btn btn-success btn-large" ]
      )}</div><div class='s s4 tac'>{$this->a(
        "/help/api", $this->intl->t("Read the API documentation"),  [ "class" => "btn btn-large" ]
      )}</div></div></div>"
    ;
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
    $this->response->setAlerts($this);
    return
      "<main id='m' role='main'>" .
        "<div id='banner'>" .
          "<h2 class='c'>{$this->intl->t("Do you like movies?{0}Great, so do we!", [ "<br>" ])}</h2>" .
        "</div>" .
        "{$this->getAlertNoScript()}{$this->alerts}{$content}" .
      "</main>"
    ;
  }

}
