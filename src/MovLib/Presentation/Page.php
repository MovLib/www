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

use \MovLib\Data\SystemLanguages;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\Navigation;

/**
 * A simple page without any content.
 *
 * Page is the reference implementation for a page without any content. Presentation classes which want to provide a
 * complete page can extend this class and overwrite the methods for which they need custom contents.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Page extends \MovLib\Presentation\AbstractPage {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Contains all alert messages of the current page.
   *
   * This property must be public, in order to allow partials to add alert messages to the presentation they are
   * attached to. An example would be the form partial, which adds alert messages if validation of one of it's input
   * elements fails.
   *
   * @var string
   */
  public $alerts = "";

  /**
   * HTML that should be included before the page's heading.
   *
   * @var string
   */
  protected $headingBefore;

  /**
   * HTML that should be included after the page's heading.
   *
   * @var string
   */
  protected $headingAfter;

  /**
   * Associative array containing global settings that will be passed as JSON encoded string to our JavaScript.
   *
   * @var type
   */
  protected $scriptSettings = [];


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new empty reference page presentation.
   *
   * @param string $title
   *   The title of the empty page.
   */
  public function __construct($title) {
    $this->init($title);
  }


  // ------------------------------------------------------------------------------------------------------------------- Protected Methods


  /**
   * Format system language for footer system language links.
   *
   * @see \MovLib\Presentation\Partial\Navigation::__toString()
   * @todo This won't work for all kinds of links we have, how can we solve this?
   * @global \MovLib\Kernel $kernel
   * @param \MovLib\Data\SystemLanguage $systemLanguage
   *   The system language to format.
   * @return array
   *   Array for navigation.
   */
  public function formatFooterSystemLanguage($systemLanguage) {
    global $kernel;
    return [ "//{$systemLanguage->domain}{$kernel->requestURI}", "{$systemLanguage->name} ({$systemLanguage->nameNative})" ];
  }

  /**
   * Get the breadcrumb navigation.
   *
   * @see \MovLib\Presentation\Partial\Navigation
   * @global \MovLib\Data\I18n $i18n
   * @return \MovLib\Presentation\Navigation
   *   The breadcrumb navigation.
   */
  protected function getBreadcrumb() {
    global $i18n;

    // Always include the home and the current page, any other breadcrumb trails are up to the extending class.
    $trail       = [[ "/", $i18n->t("Home"), [ "title" => $i18n->t("Go back to the home page.") ] ] ];
    $breadcrumbs = $this->getBreadcrumbs();
    $c           = count($breadcrumbs);
    for ($i = 0; $i < $c; ++$i) {
      // 0 => route
      // 1 => linktext
      // 2 => attributes
      if (mb_strlen($breadcrumbs[$i][1]) > 25) {
        $breadcrumbs[$i][2]["title"] = $breadcrumbs[$i][1];
        $breadcrumbs[$i][1]          = mb_strimwidth($breadcrumbs[$i][1], 0, 25, $i18n->t("…"));
      }
      $trail[] = $breadcrumbs[$i];
    }
    $trail[] = [ "#", isset($this->breadcrumbTitle) ? $this->breadcrumbTitle : $this->title ];

    // Create the actual navigation with the trail we just built.
    $breadcrumb                      = new Navigation("breadcrumb", $i18n->t("You are here: "), $trail);
    $breadcrumb->attributes["class"] = "container";
    $breadcrumb->glue                = " › ";
    $breadcrumb->hideTitle           = false;

    // We return the navigation instance itself, this allows an extending class to perform further actions.
    return $breadcrumb;
  }

  /**
   * Get additional breadcrumbs.
   *
   * This method is called automatically from the reference implementation of
   * {@see \MovLib\Presentation\Page::getBreadcrumb()}. If a presentation has additional breadcrumbs that should be
   * added to the trail it should override this method and return them. Please note that the home link and the current
   * page are always part of the trail and don't have to be created in this method.
   *
   * @see \MovLib\Presentation\Partial\Navigation
   * @return null|array
   *   Additional breadcrumbs that should be added to the trail. Please have a look at the navigation partial for an in
   *   depth disucssion about the format of the returned array.
   */
  protected function getBreadcrumbs() {
    // The reference implementation doesn't add any breadcrumbs to the trail!
  }

  /**
   * The reference implmentation is absolutely empty.
   *
   * @return string
   */
  protected function getContent() {
    return "";
  }

  /**
   * Get the reference footer.
   *
   * @global \MovLib\Kernel $kernel
   * @global \MovLib\Data\I18n $i18n
   * @return string
   *   The reference footer.
   */
  protected function getFooter() {
    global $kernel, $i18n;
    $displayLanguage        = \Locale::getDisplayLanguage($i18n->languageCode, $i18n->locale);
    $languageLinks          = new Navigation("language-links", $i18n->t("Language Links"), new SystemLanguages(false));
    $languageLinks->callback = [ $this, "formatFooterSystemLanguage" ];
    $footerNavigation       = new Navigation("footer", $i18n->t("Legal Links"), [
      [ $i18n->r("/imprint"), $i18n->t("Imprint") ],
      [ $i18n->r("/privacy-policy"), $i18n->t("Privacy Policy") ],
      [ $i18n->r("/terms-of-use"), $i18n->t("Terms of Use") ],
    ]);
    $footerNavigation->glue = " · ";
    return
      "<footer id='footer'><div class='container'>" .
        "<div class='row'>" .
        // #footer-copyright
          "<p id='footer-copyright'><i class='icon icon--cc'></i> <i class='icon icon--cc-zero'></i> {$i18n->t(
            "Database data is available under the {0}Creative Commons — CC0 1.0 Universal{1} license.",
            [ "<a href='http://creativecommons.org/protecteddomain/zero/1.0/deed.{$i18n->languageCode}' rel='license'>", "</a>" ]
          )}<br>{$i18n->t(
            "Additional terms may apply for third-party content, please refer to any license or copyright information that is additionaly stated."
          )}</p>" .
        // #footer-copyright
        // #footer-links
          "<div id='footer-links'>" .
            "<button class='button button--inverse'>{$i18n->t("Language")}: {$displayLanguage}</button>" .
            "<div class='well'>{$languageLinks}</div>" .
          "</div>" .
        // #footer-links
        "</div>" .
        "<div class='row' id='footer-logos'>" .
          "<a target='_blank' href='http://www.fh-salzburg.ac.at/'><img alt='Fachhochschule Salzburg' height='41' src='//{$kernel->domainStatic}/asset/img/footer/fachhochschule-salzburg.svg' width='64'></a>" .
          "<a target='_blank' href='https://github.com/MovLib'><img alt='GitHub' height='17' src='//{$kernel->domainStatic}/asset/img/footer/github.svg' width='64'></a>" .
        "</div>" .
        "<div class='row'>{$footerNavigation}</div>" .
      "</div></footer>"
//      "<script id='js-settings' type='application/json'>" . json_encode($this->scripts) . "</script>"
      // @todo Minify and combine!
//      "<script src='{$GLOBALS["movlib"]["static_domain"]}js/jquery.js'></script>" .
//      "<script src='{$GLOBALS["movlib"]["static_domain"]}js/movlib.js?" . rand() . "'></script>"
    ;
  }

  /**
   * Get the reference header, including logo, navigations and search form.
   *
   * @global \MovLib\Kernel $kernel
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\Session $session
   * @return string
   *   The reference header.
   */
  protected function getHeader() {
    global $kernel, $i18n, $session;

    $moviesNavigation             = new Navigation("movies-mega", $i18n->t("Movies"), [
      [ $i18n->r("/movies"), $i18n->t("Latest movie entries"), [ "title" => $i18n->t("Have a look at the latest movie entries at {0}.", [ $kernel->siteName ]) ] ],
      [ $i18n->r("/movies/new"), $i18n->t("Create new movie"), [ "title" => $i18n->t("Add a new movie to the {0} library.", [ $kernel->siteName ]) ] ],
    ]);
    $moviesNavigation->hideTitle  = false;

    $seriesNavigation             = new Navigation("series-mega", $i18n->t("Series"), [ ]);
    $seriesNavigation->hideTitle  = false;

    $personsNavigation            = new Navigation("persons-mega", $i18n->t("Perons"), [ ]);
    $personsNavigation->hideTitle = false;

    $otherNavigation              = new Navigation("other-mega", $i18n->t("Other"), [ ]);
    $otherNavigation->hideTitle   = false;

    if ($session->isAuthenticated === true) {
      $mainMenuitems = [
        [ $i18n->r("/profile"),           $i18n->t("Profile"),    [ "title" => $i18n->t("Go to your personal user page.")                                       ]],
        [ $i18n->r("/profile/watchlist"), $i18n->t("Watchlist"),  [ "title" => $i18n->t("Have a look at the latest changes of the content your are watching.")  ]],
        [ $i18n->r("/profile/sign-out"),  $i18n->t("Sign Out"),   [ "title" => $i18n->t("Click here to sign out from your current session.")                    ]],
      ];
    }
    else {
      $mainMenuitems = [
        [ $i18n->r("/users/registration"), $i18n->t("Registration"), [ "title" => $i18n->t("Click here to sign up for a new account.") ]],
        [ $i18n->r("/users/login"),        $i18n->t("Login"),        [ "title" => $i18n->t("Click here to sign in to your account.")   ]],
      ];
    }
    $mainNavigation = new Navigation("main", $i18n->t("Main Navigation"), $mainMenuitems);

    return
      "<a class='visuallyhidden' href='#content'>{$i18n->t("Skip to content")}</a>" .
      "<header id='header'>" .
        "<div id='mega-nav-container'>" .
          "<div id='mega-nav'>" .
            "<div class='container'>" .
              $this->getBreadcrumb() .
              "<div class='row'>" .
                "<div class='span span--3'>{$moviesNavigation}</div>" .
                "<div class='span span--3'>{$seriesNavigation}</div>" .
                "<div class='span span--3'>{$personsNavigation}</div>" .
                "<div class='span span--3'>{$otherNavigation}</div>" .
              "</div>" . // .row
            "</div>" . // .container
          "</div>" . // #mega-nav
          // No title and nothing else for this element. Handicapped people are not interested in an element that is
          // only here for presentational purposes.
          "<div class='container'><span id='mega-nav-switch'><span class='button button--inverse'><i class='icon icon--menu'></i></span></span></div>" .
        "</div>" . // #mega-nav-container
        "<div class='container'>" .
          "<div class='row'>" .
            "{$this->getHeaderLogo()}{$mainNavigation}" .
            // Render the header search, this is not an instance of form because it would make things complicated.
            "<form action='{$i18n->t("/search")}' class='span' id='header__search-form' method='post' role='search'>" .
              "<input type='hidden' name='form_id' value='header-search'>" .
              "<label class='visuallyhidden' for='header__search-input'>{$i18n->t("Search the {0} database.", [ $kernel->siteName ])}</label>" .
              "<input accesskey='f' id='header__search-input' name='searchterm' required tabindex='{$this->getTabindex()}' title='{$i18n->t(
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
   * Get the header logo.
   *
   * @global \MovLib\Kernel $kernel
   * @global \MovLib\Data\I18n $i18n
   * @return string
   *   The header logo.
   */
  protected function getHeaderLogo() {
    global $kernel, $i18n;
    return
      "<a class='span' href='/' id='header__logo' title='{$i18n->t("Go back to the home page.")}'>" .
        "<img alt='{$kernel->siteName}' height='42' id='logo' src='//{$kernel->domainStatic}/asset/img/logo/vector.svg' width='42'> {$kernel->siteName}" .
      "</a>"
    ;
  }

  /**
   * Get the current presentation as string.
   *
   * Any standard MovLib page consists of the same elements:
   * <ul>
   *   <li>The <b>Header</b> which includes the small and big navigation</li>
   *   <li>The <b>WrappedContent</b> which wraps the heading and the actual content</li>
   *   <li>The <b>Footer</b> which contains miscallenous links</li>
   * </ul>
   * Base on this assumptions this class contains reference implementations of all these regions and concatenates them
   * at this point. Of course including the global MovLib HTML header from <code>\MovLib\Presentation\AbstractPage</code>.
   * Noteworthy as well, this is the first place in which we include JavaScript. Not every page needs JavaScript, but
   * a page extending the reference implementation needs at least the Header-JavaScript that helps browsers to correctly
   * manage focus on our search box and header navigation. Plus without any JavaScript there's not module loader.
   *
   * @return string
   *   The current presentation as string.
   */
  public function getPresentation() {
    $html = parent::getPresentation();
    return "{$html}{$this->getHeader()}{$this->getWrappedContent()}{$this->getFooter()}";
  }

  /**
   * Get the wrapped content, including heading.
   *
   * @return string
   *   The wrapped content, including heading.
   */
  protected function getWrappedContent() {
    $title = isset($this->pageTitle) ? $this->pageTitle : $this->title;
    return
      "<div class='{$this->id}-content' id='content' role='main'>" .
        "<div id='content__header'>" .
          "<div class='container'>" .
            $this->headingBefore .
            "<h1 class='title' id='content__header__title'>{$title}</h1>" .
            $this->headingAfter .
          "</div>" .
          "<div id='alerts'>{$this->alerts}</div>" .
        "</div>" .
        $this->getContent() .
      "</div>"
    ;
  }

  /**
   * Initialize this presentation.
   *
   * The reference implementation extends the abstract class by creating the noscript alert that advises any user with
   * disabled JavaScript to activate it, additionally any alerts saved in the user's session will be set as well.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @param string $title
   *   The already translated title of this page.
   * @return this
   */
  protected function init($title) {
    global $i18n, $kernel;
    parent::init($title);

    $noscript         = new Alert($i18n->t("Please activate JavaScript in your browser to experience our website with all its features."));
    $noscript->title  = $i18n->t("JavaScript Disabled");
    $this->alerts    .= "<noscript>{$noscript}</noscript>";

    if (count($this->namespace) > 1) {
      $this->stylesheets[] = "modules/{$this->namespace[0]}.css";
    }

    if (isset($_COOKIE["alerts"])) {
      $this->alerts .= $_COOKIE["alerts"];
      setcookie("alerts", "", 1, "/", $kernel->domainDefault);
    }

    return $this;
  }

}
