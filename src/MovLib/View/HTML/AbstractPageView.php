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

use \MovLib\Utility\String;
use \MovLib\View\HTML\AbstractBaseView;

/**
 * The <b>AbstractView</b> is the base class for all other HTML views.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractPageView extends AbstractBaseView {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Array index of the active header navigation point.
   *
   * This is kept complicated to ensure that it's not easily overwritten. The active state of a header navigation point
   * is not determined by the presenter, because it only affects the view itself, but not the presentation of the pages
   * content. The amount of header navigation points is very limited and if a new view class is created the developer is
   * forced to have a look at the methods body to find out which index might be the right one.
   *
   * @see \MovLib\View\HTML\AbstractPageView::$activeHeaderUserNavigationPoint
   * @var int
   */
  protected $activeHeaderNavigationPoint;

  /**
   * Array index of the active header user navigation point.
   *
   * @see \MovLib\View\HTML\AbstractPageView::$activeHeaderNavigationPoint
   * @var int
   */
  protected $activeHeaderUserNavigationPoint;

  /**
   * Contains all alert messages of the current view.
   *
   * @var string
   */
  private $alerts = "";

  /**
   * The presenter that created the view instance.
   *
   * @var \MovLib\Presenter\AbstractPresenter
   */
  protected $presenter;

  /**
   * Content to render before the heading.
   *
   * @var string
   */
  protected $headerBefore;

  /**
   * Content to render after the heading.
   *
   * @var string
   */
  protected $headerAfter;

  /**
   * Numeric array containing all stylsheets of this view.
   *
   * @var array
   */
  protected $stylesheets = [
    "base.css",
    "layout/grid.css",
    "layout/generic.css",
    "layout/header.css",
    "layout/content.css",
    "layout/nav-secondary.css",
    "layout/footer.css",
    "layout/icons.css",
    "layout/alert.css",
    "layout/buttons.css",
  ];

  /**
   * Associative array that will be passed to <code>window.MovLib.settings</code> in our JavaScript.
   *
   * @var array
   */
  protected $scripts;

  /**
   * The title of the page.
   *
   * @var string
   */
  public $title = "";


  // ------------------------------------------------------------------------------------------------------------------- Abstract Methods


  /**
   * Get the rendered content, without HTML head, header or footer.
   *
   * @return string
   */
  abstract public function getContent();


  // ------------------------------------------------------------------------------------------------------------------- Public Methods


  /**
   * Get the full rendered view.
   *
   * @return string
   *   The rendered view ready for print.
   */
  public function __toString() {
    return
      $this->getHead() .
      $this->getHeader() .
      $this->getRenderedContent() .
      $this->getFooter()
    ;
  }

  /**
   * Add an alert to the current view.
   *
   * @param \MovLib\View\HTML\Alert $alert
   *   The alert to add.
   * @return this
   */
  public function addAlert($alert) {
    $this->alerts .= $alert;
    return $this;
  }

  /**
   * Initialize new view.
   *
   * @param \MovLib\Presenter\AbstractPresenter $presenter
   *   The presenter that created the view instance.
   * @param string $title
   *   The unique title of this view.
   * @return this
   */
  public function init($presenter, $title) {
    $this->presenter = $presenter;
    // This is not real OO style, because we are playing with the properties of another object. But it makes life easy.
    $this->presenter->view = $this;
    $this->title = $title;
    $this->scripts = $GLOBALS["movlib"];
    $this->scripts["modules"] = [];
    if (isset($_SESSION["ALERTS"])) {
      $c = count($_SESSION["ALERTS"]);
      for ($i = 0; $i < $c; ++$i) {
        $this->alerts .= $_SESSION["ALERTS"][$i];
      }
      unset($_SESSION["ALERTS"]);
    }
    return $this;
  }

  /**
   * Get all alerts that were previously set.
   *
   * The surrounding HTML is always included, no matter if there are any alerts or not. This is important to ensure
   * that the ID is present within the DOM for later alert insertion via JavaScript.
   *
   * @return string
   *   All alerts ready for print.
   */
  public function getAlerts() {
    return "<div id='alerts'>{$this->alerts}</div>";
  }

  /**
   * Get the HTML head element, this includes doctype and the html root element.
   *
   * @link http://www.netmagazine.com/features/create-perfect-favicon
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance.
   * @global \MovLib\Model\SessionModel $user
   *   The currently logged in user.
   * @return string
   *   The head ready for print.
   */
  public function getHead() {
    global $i18n, $user;
    $c = count($this->stylesheets);
    $stylesheets = "";
    for ($i = 0; $i < $c; ++$i) {
      $stylesheets .= "<link rel='stylesheet' href='{$GLOBALS["movlib"]["static_domain"]}css/{$this->stylesheets[$i]}'>";
    }
    $bodyClass = "{$this->getShortName()}-body";
    if (isset($user) && $user->isLoggedIn === true) {
      $bodyClass .= " logged-in";
    }
    $ariaRole = "document";
    if (strpos($this->getShortName(), "edit") !== false) {
      $ariaRole = "application";
    }
    return
      "<!doctype html><html id='nojs' lang='{$i18n->languageCode}' dir='{$i18n->direction}'><head>" .
        // @todo The meta-charset is only needed if a document is not sending appropriate HTTP headers. So for instance
        //       if someone saves a page to disc. The question is, do we really need support for such situations? Older
        //       (not supported) browsers like IE8 have problems with more than one charset declaration (experimental
        //       page speed rule says so) and it is simply redundant in the web context. Our HTTP header already told
        //       the browser that this page is completely in UTF-8 (same is true for any other text based content our
        //       server is going to deliver). The bytes we save here are of course irrevelant, it's the redundancy that
        //       bugs me.
        //"<meta charset='utf-8'>" .
        "<title>{$this->getHeadTitle()}</title>" .
        $stylesheets .
        "<link rel='icon' type='image/svg+xml' href='/img/logo/vector.svg'>" .
        "<link rel='icon' type='image/png' sizes='256x256' href='/img/logo/256.png'>" .
        "<link rel='icon' type='image/png' sizes='128x128' href='/img/logo/128.png'>" .
        "<link rel='icon' type='image/png' sizes='64x64' href='/img/logo/64.png'>" .
        "<link rel='icon' type='image/png' sizes='32x32' href='/img/logo/32.png'>" .
        "<link rel='icon' type='image/png' sizes='24x24' href='/img/logo/24.png'>" .
        "<link rel='icon' type='image/png' sizes='16x16' href='/img/logo/16.png'>" .
        // @todo Add opensearch tag (rel="search").
        "<meta name='viewport' content='width=device-width,initial-scale=1.0'>" .
      "</head><body class='{$bodyClass}' role='{$ariaRole}'>";
  }

  /**
   * Get the HTML header, this includes the logo, navigations and search box.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance.
   * @global \MovLib\Model\SessionModel $user
   *   The currently logged in user.
   * @return string
   *   The header ready for print.
   */
  public function getHeader() {
    global $i18n, $user;
    if (isset($user) && $user->isLoggedIn === true) {
      $points = [
        [ $i18n->r("/user"), $i18n->t("Profile"), [ "title" => $i18n->t("Go to your personal user page.") ]],
        [ $i18n->r("/user/watchlist"), $i18n->t("Watchlist"), [ "title" => $i18n->t("Have a look at the latest changes of the content your are watching.") ]],
        [ $i18n->r("/user/sign-out"), $i18n->t("Sign Out"), [ "title" => $i18n->t("Click here to sign out from your current session.") ]],
      ];
    }
    else {
      $points = [
        [ $i18n->r("/user/register"), $i18n->t("Register"), [ "title" => $i18n->t("Click here to create a new account.") ]],
        [ $i18n->r("/user/login"), $i18n->t("Login"), [ "title" => $i18n->t("Click here to log in to your account.") ]],
      ];
    }
    return
      "<a class='visuallyhidden' href='#content'>{$i18n->t("Skip to content")}</a>" .
      "<header id='header'>" .
        "<div id='nav-mega-container'>" .
          "<div id='nav-mega'>" .
            "<div class='container'>" .
              $this->getBreadcrumb() .
              "<div class='row'>" .
                "<div class='span span--3'>{$this->getNavigation($i18n->t("Movies"), "movies", [
                  [ $i18n->r("/movies"), $i18n->t("Latest movie entries"), [ "title" => $i18n->t("Have a look at the latest movie entries at MovLib.") ]],
                  [ $i18n->r("/movies/new"), $i18n->t("Create new movie"), [ "title" => $i18n->t("Add a new movie to the MovLib library.") ]],
                ], " ", [], false)}</div>" .
                "<div class='span span--3'>{$this->getNavigation($i18n->t("Series"), "series", [], " ", [], false)}</div>" .
                "<div class='span span--3'>{$this->getNavigation($i18n->t("Persons"), "persons", [], " ", [], false)}</div>" .
                "<div class='span span--3'>{$this->getNavigation($i18n->t("Other"), "other", [], " ", [], false)}</div>" .
              "</div>" . // .row
            "</div>" . // .container
          "</div>" . // #nav-mega
          // No title and nothing else for this element. Handicapped people are not interested in an element that is
          // only here for presentational purposes.
          "<div class='container'><span id='nav-mega-switch'><span class='button button--inverse'><i class='icon icon--menu'></i></span></span></div>" .
        "</div>" . // #nav-mega-container
        "<div class='container'>" .
          "<div class='row'>" .
            "<a class='span' href='{$i18n->r("/")}' id='header__logo' title='{$i18n->t("Take me back to the home page.")}'>" .
              "<img alt='{$i18n->t("MovLib, the free movie library.")}' height='42' id='logo' src='{$GLOBALS["movlib"]["static_domain"]}img/logo/vector.svg' width='42'> MovLib" .
            "</a>" .
            // Render the main navigation.
            $this->getNavigation($i18n->t("Main Navigation"), "main", $points, " ") .
            // Render the header search, this is not an instance of form because it would make things complicated.
            "<form action='{$i18n->t("/search")}' class='span' id='header__search-form' method='post' role='search'>" .
              "<input type='hidden' name='form_id' value='header-search'>" .
              "<label class='visuallyhidden' for='header__search-input'>{$i18n->t("Search the MovLib database.")}</label>" .
              "<input accesskey='f' id='header__search-input' name='searchterm' required role='textbox' tabindex='{$this->getTabindex()}' title='{$i18n->t(
                "Enter the search term you wish to search for and hit enter. [alt-shift-f]"
              )}' type='search'>" .
              "<button id='header__search-button' title='{$i18n->t("Start searching for the entered keyword.")}' type='submit'>" .
                "<i class='icon icon--search'></i>" .
              "</button>" .
            "</form>" .
          "</div>" . // .row
        "</div>" . // .container
      "</header>"
    ;
  }

  /**
   * Helper method to generate a navigation.
   *
   * @param string $title
   *   The title of the section, this will be wrapped in a <code>&lt;h2&gt;</code>.
   * @param string $role
   *   The logic role of this navigation menu (e.g. <em>main</em>, <em>footer</em>, ...).
   * @param array $points
   *   Numeric array containing the navigation points in the format:
   *   <pre>[ 0 => route, 1 => text, 2 => attributes ]</pre>
   *   All array offsets are mandatory; the attributes have to have an already translated title!
   * @param string $glue
   *   The string that is used to combine the various navigation points.
   * @param array $attributes [optional]
   *   The attributes that should be applied to the HTML nav element.
   * @param boolean $hideTitle [optional]
   *   Defines if the title should be hidden or not, default is to hide the title on navigation elements.
   * @return string
   *   Fully rendered navigation.
   */
  public function getNavigation($title, $role, array $points, $glue, array $attributes = null, $hideTitle = true) {
    $menuitems = "";
    $k = count($points);
    for ($i = 0; $i < $k; ++$i) {
      $classes = "menuitem";
      $this->addClass($classes, $points[$i][2]);
      $points[$i][2]["role"] = "menuitem";
      if ($i !== 0) {
        $menuitems .= $glue;
      }
      $menuitems .= $this->a($points[$i][0], $points[$i][1], $points[$i][2]);
    }
    $attributes["id"] = "nav-{$role}";
    $attributes["role"] = "menu";
    $attributes["aria-labelledby"] = "nav-{$role}__title";
    $hideTitle = $hideTitle === true ? " class='visuallyhidden'" : "";
    return "<nav{$this->expandTagAttributes($attributes)}><h2 id='nav-{$role}__title'{$hideTitle} role='presentation'>{$title}</h2>{$menuitems}</nav>";
  }

  /**
   * Get the breadcrumb navigation.
   *
   * @see \MovLib\Presenter\AbstractPresenter::getBreadcrumb()
   * @see \MovLib\View\HTML\AbstractPageView::getNavigation()
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance.
   * @return string
   *   The breadcrumb ready for print.
   */
  public function getBreadcrumb() {
    global $i18n;
    $points = [[ $i18n->r("/"), $i18n->t("Home"), [ "title" => $i18n->t("Go back to the home page.") ]]];
    $trail = $this->presenter->getBreadcrumb();
    $trailCount = count($trail);
    if ($trailCount !== 0) {
      for ($i = 0; $i < $trailCount; ++$i) {
        $trail[$i][1] = mb_strimwidth($trail[$i][1], 0, 25, $i18n->t("…"));
        $points[] = $trail[$i];
      }
    }
    $points[] = [ $_SERVER["REQUEST_URI"], isset($this->breadcrumbTitle) ? $this->breadcrumbTitle : $this->title ];
    return "<div id='breadcrumb'>{$this->getNavigation($i18n->t("You are here: "), "breadcrumb", $points, " › ", [ "class" => "container" ], false)}</div>";
  }

  /**
   * Get the HTML footer including all script tags.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance.
   * @return string
   *   The footer ready for print.
   */
  public function getFooter() {
    global $i18n;
    return
      "<footer id='footer'>" .
        "<div class='container'>" .
          "<div class='row footer-row-copyright'>" .
            "<i class='icon icon--cc'></i> <i class='icon icon--cc-zero'></i> {$i18n->t(
              "Database data is available under the {0}Creative Commons — CC0 1.0 Universal{1} license.",
              [ "<a href='http://creativecommons.org/publicdomain/zero/1.0/deed.{$i18n->languageCode}' rel='license'>", "</a>" ]
            )}<br>" .
            "{$i18n->t(
              "Additional terms may apply for third-party content, please refer to any license or copyright information that is additionaly stated."
            )}<br>" .
            $i18n->t(
              "By using this site, you agree to the {0} and {1}.",
              [ $this->a($i18n->r("/terms-of-use"), $i18n->t("Terms of Use")), $this->a($i18n->r("/privacy-policy"), $i18n->t("Privacy Policy")) ],
              [ "comment" => "<code>{0}</code> is <em>Terms of Use</em> and <code>{1}</tt> is <em>Privacy Policy</em>." ]
            ) .
          "</div>" .
          "<div class='row footer-row-logos'>" .
            "<a target='_blank' href='http://www.fh-salzburg.ac.at/'><img alt='Fachhochschule Salzburg' height='41' src='{$GLOBALS["movlib"]["static_domain"]}img/footer/fachhochschule-salzburg.svg' width='64'></a>" .
            "<a target='_blank' href='https://github.com/MovLib'><img alt='GitHub' height='17' src='{$GLOBALS["movlib"]["static_domain"]}img/footer/github.svg' width='64'></a>" .
          "</div>" .
        "</div>" .
      "</footer>"
//      "<script id='js-settings' type='application/json'>" . json_encode($this->scripts) . "</script>"
      // @todo Minify and combine!
//      "<script src='{$GLOBALS["movlib"]["static_domain"]}js/jquery.js'></script>" .
//      "<script src='{$GLOBALS["movlib"]["static_domain"]}js/movlib.js?" . rand() . "'></script>"
    ;
  }

  /**
   * Get the logo for the <code>&lt;header&gt;</code>-element.
   *
   * This method must stay public and not final. We have to overwrite this in the special homepage view!
   *
   * @see \MovLib\View\HTML\HomeView
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance.
   * @return string
   *   The logo ready for print.
   */
  public function getHeaderLogo() {
    global $i18n;
    return $this->a(
      $i18n->r("/"),
      $i18n->t("MovLib <small>the <em>free</em> movie library</small>"),
      [ "id" => "logo", "class" => "inline", "title" => $i18n->t("Go back to the home page.") ]
    );
  }

  /**
   * Get the title for the HTML <code>&lt;title&gt;</code>-element.
   *
   * This method must stay public and not final. We have to overwrite this in the special homepage view!
   *
   * @see \MovLib\View\HTML\HomeView
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance.
   * @return string
   *   The title ready for print.
   */
  public function getHeadTitle() {
    global $i18n;
    return
      String::checkPlain($this->title) .
      $i18n->t(" — ", [], [
        "comment" => "The em dash is used as separator character in the header title to denote the source of the "
        . "document (like in a quote the author), this should be translated to the equivalent character in your "
        . "language. More information on this specific character can be found at "
        . "<a href='//en.wikipedia.org/wiki/Dash#Em_dash'>Wikipedia</a>."
      ]) .
      "MovLib"
    ;
  }

  /**
   * Get the content wrapped in the outer content <tt>div</tt>.
   *
   * @return string
   *   The rendered content ready for print.
   */
  public function getRenderedContent() {
    return
      "<div class='{$this->getShortName()}-content' id='content' role='main'>" .
        "<div id='content__header'>" .
          "<div class='container'>" .
            $this->headerBefore .
            "<h1 class='title' id='content__header__title'>{$this->title}</h1>" .
            $this->headerAfter .
          "</div>" .
          $this->getAlerts() .
        "</div>" .
        $this->getContent() .
      "</div>"
    ;
  }

}