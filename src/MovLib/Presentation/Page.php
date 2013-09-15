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

use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\Navigation;
use \Locale;

/**
 * A simple page without any content.
 *
 * Page is the reference implementation for a page without any content. Presentation classes which want to provide a
 * complete page can extend this class and overwrite the methods for which they need custom contents.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
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
   * Checks if <var>$errors</var> contains anything and aborts if it does.
   *
   * It's a very common pattern to collect error messages within an array if validating data. Otherwise one would have
   * to set an alert message for each error that occurs. This method let's you pass the possibly collected errors and
   * checks if there are any, if there are any it will create and set the alert message for you.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param null|array $errors
   *   The collected error messages to check.
   * @return boolean
   *   Returns <code>TRUE</code> if there were any errors, otherwise <code>FALSE</code>.
   */
  public function checkErrors($errors) {
    global $i18n;
    if ($errors) {
      $errors           = implode("<br>", $errors);
      $alert            = new Alert($errors);
      $alert->block     = true;
      $alert->title     = $i18n->t("Validation Error");
      $alert->severity  = Alert::SEVERITY_ERROR;
      $this->alerts    .= $alert;
      return true;
    }
    return false;
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
    $trail = [[ "{$_SERVER["SCHEME"]}://{$_SERVER["SERVER_NAME"]}/", $i18n->t("Home"), [ "title" => $i18n->t("Go back to the home page.") ]]];
    $breadcrumbs = $this->getBreadcrumbs();
    $c = count($breadcrumbs);
    for ($i = 0; $i < $c; ++$i) {
      // 0 => route
      // 1 => linktext
      // 2 => attributes
      if (mb_strlen($breadcrumbs[$i][1]) > 25) {
        $breadcrumbs[$i][2]["title"] = $breadcrumbs[$i][1];
        $breadcrumbs[$i][1] = mb_strimwidth($breadcrumbs[$i][1], 0, 25, $i18n->t("…"));
      }
      $trail[] = $breadcrumbs[$i];
    }
    $trail[] = [ "#", isset($this->breadcrumbTitle) ? $this->breadcrumbTitle : $this->title ];

    // Create the actual navigation with the trail we just built.
    $breadcrumb = new Navigation("breadcrumb", $i18n->t("You are here: "), $trail);
    $breadcrumb->attributes["class"] = "container";
    $breadcrumb->glue = " › ";
    $breadcrumb->hideTitle = false;

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
   * @global \MovLib\Data\I18n $i18n
   * @return string
   *   The reference footer.
   */
  protected function getFooter() {
    global $i18n;
    $displayLanguage = Locale::getDisplayLanguage($_SERVER["LANGUAGE_CODE"], $i18n->locale);
    $languageLinks = new Navigation("language-links", $i18n->r("Language Links"), $i18n->getLanguageLinks());
    return
      "<footer id='footer'><div class='container'>" .
        "<div class='row'>" .
          // #footer-copyright
          "<div id='footer-copyright'>" .
            "<i class='icon icon--cc'></i> <i class='icon icon--cc-zero'></i> {$i18n->t(
              "Database data is available under the {0}Creative Commons — CC0 1.0 Universal{1} license.",
              [ "<a href='http://creativecommons.org/protecteddomain/zero/1.0/deed.{$i18n->languageCode}' rel='license'>", "</a>" ]
            )}<br>{$i18n->t(
              "Additional terms may apply for third-party content, please refer to any license or copyright information that is additionaly stated."
            )}<br>{$i18n->t(
              "By using this site, you agree to the {0}Terms of Use{1} and {2}Privacy Policy{3}.",
              [ "<a href='{$i18n->r("/terms-of-use")}'>", "</a>", "<a href='{$i18n->r("/privacy-policy")}'>", "</a>" ]
            )}" .
          "</div>" .
          // #footer-copyright
          // #footer-links
          "<div id='footer-links'>" .
            "<button class='button button--inverse'>{$i18n->t("Language")}: {$displayLanguage}</button>" .
            "<div class='well'>{$languageLinks}</div>" .
          "</div>" .
          // #footer-links
        "</div>" .
        "<div class='row' id='footer-logos'>" .
          "<a target='_blank' href='http://www.fh-salzburg.ac.at/'><img alt='Fachhochschule Salzburg' height='41' src='{$GLOBALS["movlib"]["static_domain"]}img/footer/fachhochschule-salzburg.svg' width='64'></a>" .
          "<a target='_blank' href='https://github.com/MovLib'><img alt='GitHub' height='17' src='{$GLOBALS["movlib"]["static_domain"]}img/footer/github.svg' width='64'></a>" .
        "</div>" .
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
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\Session $session
   * @return string
   *   The reference header.
   */
  protected function getHeader() {
    global $i18n, $session;

    $moviesNavigation = new Navigation("movies", $i18n->t("Movies"), [
      [ $i18n->r("/movies"),      $i18n->t("Latest movie entries"), [ "title" => $i18n->t("Have a look at the latest movie entries at MovLib.") ]],
      [ $i18n->r("/movies/new"),  $i18n->t("Create new movie"),     [ "title" => $i18n->t("Add a new movie to the MovLib library.")             ]],
    ]);
    $moviesNavigation->hideTitle = false;

    $seriesNavigation = new Navigation("series", $i18n->t("Series"), []);
    $seriesNavigation->hideTitle = false;

    $personsNavigation = new Navigation("persons", $i18n->t("Perons"), []);
    $personsNavigation->hideTitle = false;

    $otherNavigation = new Navigation("other", $i18n->t("Other"), []);
    $otherNavigation->hideTitle = false;

    if ($session->isAuthenticated === true) {
      $mainMenuitems = [
        [ $i18n->r("/user"),            $i18n->t("Profile"),    [ "title" => $i18n->t("Go to your personal user page.")                                       ]],
        [ $i18n->r("/user/watchlist"),  $i18n->t("Watchlist"),  [ "title" => $i18n->t("Have a look at the latest changes of the content your are watching.")  ]],
        [ $i18n->r("/user/sign-out"),   $i18n->t("Sign Out"),   [ "title" => $i18n->t("Click here to sign out from your current session.")                    ]],
      ];
    }
    else {
      $mainMenuitems = [
        [ $i18n->r("/user/registration"), $i18n->t("Registration"), [ "title" => $i18n->t("Click here to sign up for a new account.") ]],
        [ $i18n->r("/user/login"),        $i18n->t("Login"),        [ "title" => $i18n->t("Click here to sign in to your account.")   ]],
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
   * Get the header logo.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return string
   *   The header logo.
   */
  protected function getHeaderLogo() {
    global $i18n;
    return
      "<a class='span' href='/' id='header__logo' title='{$i18n->t("Go back to the home page.")}'>" .
        "<img alt='{$i18n->t("{0}, the free movie library.", [ "MovLib" ])}' height='42' id='logo' src='{$GLOBALS["movlib"]["static_domain"]}img/logo/vector.svg' width='42'> MovLib" .
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
    return
      "<div class='{$this->id}-content' id='content' role='main'>" .
        "<div id='content__header'>" .
          "<div class='container'>" .
            $this->headingBefore .
            "<h1 class='title' id='content__header__title'>{$this->title}</h1>" .
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
   * @param string $title
   *   The already translated title of this page.
   * @return this
   */
  protected function init($title) {
    global $i18n;
    $noscript = new Alert($i18n->t("Please activate JavaScript in your browser to experience our website with all its features."));
    $noscript->title = $i18n->t("JavaScript Disabled");
    $this->alerts .= "<noscript>{$noscript}</noscript>";
    if (isset($_SESSION["alerts"])) {
      $c = count($_SESSION["alerts"]);
      for ($i = 0; $i < $c; ++$i) {
        $this->alerts .= $_SESSION["alerts"][$i];
      }
      unset($_SESSION["alerts"]);
    }
    return parent::init($title);
  }

}
