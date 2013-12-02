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
   * The presentation's breadcrumb navigation.
   *
   * @var \MovLib\Presentation\Partial\Navigation
   */
  protected $breadcrumb;

  /**
   * HTML that should be included after the page's content.
   *
   * @var string
   */
  protected $contentAfter;

  /**
   * HTML that should be included before the page's content.
   * @var string
   */
  protected $contentBefore;

  /**
   * HTML that should be included after the page's heading.
   *
   * @var string
   */
  protected $headingAfter;

  /**
   * HTML that should be included before the page's heading.
   *
   * @var string
   */
  protected $headingBefore;

  /**
   * The itemprop value for the page's heading.
   *
   * @var string
   */
  protected $headingSchemaProperty;

  /**
   * The type name of the schema of this presentation's content.
   *
   * @link http://schema.org/docs/schemas.html
   * @var string
   */
  protected $schemaType;


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
   * @todo This won't work for all kinds of links we have, how can we solve this?
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @param string $languageCode
   *   The language code of the system language.
   * @return array
   *   Array for navigation.
   */
  public function formatFooterSystemLanguage($languageCode) {
    global $i18n, $kernel;
    $attributes = null;
    if ($languageCode != $i18n->languageCode) {
      $attributes["lang"] = $languageCode;
    }
    return [ "//{$languageCode}.{$kernel->domainDefault}{$kernel->requestURI}", \Locale::getDisplayLanguage($languageCode, $i18n->languageCode), $attributes ];
  }

  /**
   * Get additional breadcrumbs.
   *
   * This method is called automatically from the reference implementation of
   * {@see \MovLib\Presentation\Page::getBreadcrumb()}. If a presentation has additional breadcrumbs that should be
   * added to the trail it should override this method and return them. Please note that the home link and the current
   * page are always part of the trail and don't have to be created in this method.
   *
   * @internal
   *   The reference implementation doesn't add any breadcrumbs to the trail!
   * @see \MovLib\Presentation\Partial\Navigation
   * @return null|array
   *   Additional breadcrumbs that should be added to the trail. Please have a look at the navigation partial for an in
   *   depth disucssion about the format of the returned array.
   */
  protected function getBreadcrumbs() {}

  /**
   * Get the presentation's page content.
   *
   * @internal
   *   The reference implementation doesn't add any breadcrumbs to the trail!
   * @return string
   */
  protected function getContent() {}

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
    $languageLinks          = new Navigation("language-links", $i18n->t("Language Links"), array_keys($kernel->systemLanguages));
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
          "<p id='footer-copyright'><i class='ico-cc'></i> <i class='ico-cc-zero'></i> {$i18n->t(
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
    ;
  }

  /**
   * Get the reference header, including logo, navigations and search form.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @global \MovLib\Data\User\Session $session
   * @return string
   *   The reference header.
   */
  protected function getHeader() {
    global $i18n, $kernel, $session;

    // Sub-navigations of the explore menuitem.
    $moviesNavigation  = new Navigation("movies-mega", $i18n->t("Movies"), [
      [ $i18n->r("/movies"), $i18n->t("Latest movie entries") ],
      [ $i18n->r("/movie/create"), $i18n->t("Create new movie") ],
      [ $i18n->r("/movies/reviews"), $i18n->t("Latest movie reviews") ],
      [ $i18n->r("/movie/random"), $i18n->t("Go to random movie") ],
    ], false);
    $seriesNavigation  = new Navigation("series-mega", $i18n->t("Series"), [
      [ $i18n->r("/series"), $i18n->t("Latest series entries") ],
      [ $i18n->r("/series/create"), $i18n->t("Create new series") ],
      [ $i18n->r("/series/reviews"), $i18n->t("Latest series reviews") ],
      [ $i18n->r("/series/random"), $i18n->t("Go to random series") ],
    ], false);
    $personsCompaniesNavigation = new Navigation("persons-companies-mega", $i18n->t("Persons and Companies"), [
      [ $i18n->r("/persons"), $i18n->t("Latest person entries") ],
      [ $i18n->r("/person/create"), $i18n->t("Create new person") ],
      [ $i18n->r("/person/random"), $i18n->t("Go to random person") ],
      [ $i18n->r("/companies"), $i18n->t("Latest company entries") ],
      [ $i18n->r("/company/create"), $i18n->t("Create new company") ],
      [ $i18n->r("/company/random"), $i18n->t("Go to random company") ],
    ], false);
    $otherNavigation   = new Navigation("other-mega", $i18n->t("Other"), [
      [ $i18n->r("/genres"), $i18n->t("Overview of all genres") ],
      [ $i18n->r("/articles"), $i18n->t("Overview of all articles") ],
      [ $i18n->r("/help"), $i18n->t("Overview of all help articles") ],
    ], false);

    // Put them all together.
    $mainNavigation = new Navigation("main", $i18n->t("Main Navigation"), [
      "<div class='expander'>{$i18n->t("Explore")}<div class='row'>" .
        "<div class='span span--3'>{$moviesNavigation}</div>" .
        "<div class='span span--3'>{$seriesNavigation}</div>" .
        "<div class='span span--3'>{$personsCompaniesNavigation}</div>" .
        "<div class='span span--3'>{$otherNavigation}</div>" .
      "</div></div>"
    ]);

    if ($session->isAuthenticated === true) {
      $userNavigation = [

      ];
    }
    else {
      $userNavigation = [
        [ $i18n->r("/profile/sign-in"), $i18n->t("Sign In") ],
        [ $i18n->r("/profile/join"), $i18n->t("Join") ],
        [ $i18n->r("/profile/reset-password"), $i18n->t("Reset Password") ],
      ];
    }
    $userNavigation = new Navigation("user", $i18n->t("User Navigation"), $userNavigation);

    return
      "<a class='visuallyhidden' href='#content'>{$i18n->t("Skip to content")}</a>" .
      "<header id='header'>" .
        "<div class='container'>" .
          "<div class='row'>" .
            "{$this->getHeaderLogo()}{$mainNavigation}" .
            "<form action='{$i18n->t("/search")}' class='span' id='search' method='post' role='search'>" .
              "<input type='hidden' name='form_id' value='header_search'>" .
              "<label class='visuallyhidden' for='search-input'>{$i18n->t("Search the {0} database.", [ $kernel->siteName ])}</label>" .
              "<input accesskey='f' id='search-input' name='searchterm' required tabindex='{$this->getTabindex()}' title='{$i18n->t("Enter the search term you wish to search for and hit enter.")}' type='search'>" .
              "<button title='{$i18n->t("Start searching for the entered keyword.")}' type='submit'><i class='ico-search'></i></button>" .
            "</form>" .
            "<div class='button button--inverse ico-user-add'>{$userNavigation}</div>" .
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
      "<a class='span' href='/' id='header-logo' title='{$i18n->t("Go back to the home page.")}'>" .
        "<img alt='{$kernel->siteName}' height='42' src='{$kernel->getAssetURL("logo/vector", "svg")}' width='42'> {$kernel->siteName}" .
      "</a>"
    ;
  }

  /**
   * Get the wrapped content, including heading.
   *
   * @return string
   *   The wrapped content, including heading.
   */
  protected function getWrappedContent() {
    // Allow the presentation to set a heading that includes HTML mark-up.
    $title  = isset($this->pageTitle) ? $this->pageTitle : $this->title;

    // The schema for the complete page content.
    $schema = null;
    if ($this->schemaType) {
      $schema = " itemscope itemtype='http://schema.org/{$this->schemaType}'";
    }

    // The schema property of the heading.
    $headingprop = null;
    if ($this->headingSchemaProperty) {
      $headingprop = " itemprop='{$this->headingSchemaProperty}'";
    }

    // Render the actual page header.
    return
      "<div class='{$this->id}-content' id='content' role='main'{$schema}>" .
        "<header id='content-header'>" .
          "<div class='container'>{$this->headingBefore}<h1 id='content-title'{$headingprop}>{$title}</h1>{$this->headingAfter}</div>" .
          "<div id='alerts'>{$this->alerts}</div>" .
        "</header>" .
        "{$this->contentBefore}{$this->getContent()}{$this->contentAfter}" .
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

    // Warn users that have JavaScript disabled that not all things will be as awesome as they should be.
    $noscript      = new Alert($i18n->t("Please activate JavaScript in your browser to experience our website with all its features."), $i18n->t("JavaScript Disabled"));
    $this->alerts .= "<noscript>{$noscript}</noscript>";

    // Each sub-namespace within the presentation namespace is worth a stylsheet.
    $c = count($this->namespace);
    for ($i = 0; $i < $c; ++$i) {
      $kernel->stylesheets[] = $this->namespace[$i];
    }

    // Add all alerts that are stored in a cookie to the current presentation and remove them afterwards.
    if (isset($_COOKIE["alerts"])) {
      $this->alerts .= $_COOKIE["alerts"];
      setcookie("alerts", "", 1, "/", $kernel->domainDefault);
    }

    // Initialize the breadcrumb navigation and always include the home page's link and the currently displayed page.
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
    $trail[] = [ $kernel->requestPath, $this->title ];

    // Create the actual navigation with the trail we just built.
    $this->breadcrumb                      = new Navigation("breadcrumb", $i18n->t("You are here: "), $trail);
    $this->breadcrumb->attributes["class"] = "container";
    $this->breadcrumb->glue                = " › ";
    $this->breadcrumb->hideTitle           = false;

    return $this;
  }

}
