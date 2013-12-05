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
 * Abstract base class for all presentation classes.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class Page extends \MovLib\Presentation\AbstractBase {


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
   * Contains the CSS classes of the body element.
   *
   * @var string
   */
  private $bodyClasses;

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
   * The page's unique ID.
   *
   * In order to identify a page in HTML, CSS, and JavaScript we have to use unique IDs. The unique ID of a page is
   * automatically generated from it's namespace and set after calling the <code>AbstractPage::init()</code>-method. The
   * unique page's ID is generated by removing the common part from the namespace (specifically the
   * <code>"MovLib\Presentation\"</code> string), replacing all backslashes with dashes, and finally lower-casing the
   * complete string. The unique ID for this page that would be generated with this class (if it wouldn't be abstract)
   * would be <code>"abstractpage"</code>.
   *
   * @see \MovLib\Presentation\Page::init()
   * @var string
   */
  public $id;

  /**
   * Contains the namespace parts as array.
   *
   * @var array
   */
  protected $namespace;

  /**
   * The page's title used in the header.
   *
   * @var string
   */
  protected $pageTitle;

  /**
   * The type name of the schema of this presentation's content.
   *
   * @link http://schema.org/docs/schemas.html
   * @var string
   */
  protected $schemaType;

  /**
   * The page's title.
   *
   * @var string
   */
  protected $title;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Add a class (or more) to the body's class attribute.
   *
   * @param string $class
   *   The CSS class(es) that should be added to the body's class attribute.
   * @return this
   */
  protected function addBodyClass($class) {
    if (!$this->bodyClasses) {
      $this->bodyClasses = $class;
    }
    else {
      $this->bodyClasses .= " {$class}";
    }
    return $this;
  }

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

//    $displayLanguage        = \Locale::getDisplayLanguage($i18n->languageCode, $i18n->locale);
//    $languageLinks          = new Navigation($i18n->t("Language Links"), array_keys($kernel->systemLanguages));
//    $languageLinks->callback = [ $this, "formatFooterSystemLanguage" ];

    return
      "<a class='visuallyhidden' href='#main' tabindex='{$this->getTabindex()}'>{$i18n->t("Skip footer and jump back to main content.")}</a> " .
      "<a class='visuallyhidden' href='#header' tabindex='{$this->getTabindex()}'>{$i18n->t("Skip footer and jump back to header content.")}</a>" .
      "<footer id='footer' role='contentinfo'>" .
        // @todo Is this title appropriate?
        "<h1 class='visuallyhidden'>{$i18n->t("Infos all around {sitename}", [
          "sitename" => $kernel->siteName
        ])}</h1>" .
        "<div class='container'><div class='row'>" .
          "<div class='span span--12'>" .
            "<h3 class='visuallyhidden'>{$i18n->t("Copyright and licensing information")}</h3>" .
            "<p id='footer-copyright'><span class='ico ico-cc'></span> <span class='ico ico-cc-zero'></span> {$i18n->t(
              "Database data is available under the {0}Creative Commons — CC0 1.0 Universal{1} license.",
              [ "<a href='http://creativecommons.org/protecteddomain/zero/1.0/deed.{$i18n->languageCode}' rel='license' tabindex='{$this->getTabindex()}'>", "</a>" ]
            )}<br>{$i18n->t(
              "Additional terms may apply for third-party content, please refer to any license or copyright information that is additionaly stated."
            )}</p>" .
          "</div>" .
//            "<h3 class='visuallyhidden'>{$i18n->t("")}</h3>" .
//            "<button class='button button--inverse'>{$i18n->t("Language")}: {$displayLanguage}</button>" .
//            "<div class='well'>{$languageLinks}</div>" .
          "<div id='footer-logos' class='span span--12 tac'>" .
            "<h3 class='visuallyhidden'>{$i18n->t("Sponsors and external resources")}</h3>" .
            "<a class='img' href='http://www.fh-salzburg.ac.at/' tabindex='{$this->getTabindex()}' target='_blank'>" .
              "<img alt='Fachhochschule Salzburg' height='30' src='{$kernel->getAssetURL("footer/fachhochschule-salzburg", "svg")}' width='48'>" .
            "</a>" .
            "<a class='img' href='https://github.com/MovLib' tabindex='{$this->getTabindex()}' target='_blank'>" .
              "<img alt='GitHub' height='30' src='{$kernel->getAssetURL("footer/github", "svg")}' width='48'>" .
            "</a>" .
          "</div>" .
          "<div class='last span span--6'>" .
            "<h3 class='visuallyhidden'>{$i18n->t("Language Selection")}</h3>" .
            // @todo Add language selection.
          "</div>" .
          "<div class='last span span--6 tar'>" .
            "<h3 class='visuallyhidden'>{$i18n->t("Legal Links")}</h3>" .
            "{$this->a($i18n->r("/imprint"), $i18n->t("Imprint"))} · " .
            "{$this->a($i18n->r("/privacy-policy"), $i18n->t("Privacy Policy"))} · " .
            "{$this->a($i18n->r("/terms-of-use"), $i18n->t("Terms of Use"))}" .
          "</div>" .
        "</div>" .
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

    // The search is the most important thing within MovLib, therefor we always give it the two first tab indexes. But
    // if a form is present the form has precedence over the search and the search directly follows the form. The third
    // tabindex is reserved for the "skip to content" anchor. Afterwards we leave it to the browser to manage the focus
    // state (it should go down the page, starting with the main navigation).
    $searchInputTabindex  = $this->getTabindex();
    $searchSubmitTabindex = $this->getTabindex();
    $skipLinkTabindex     = $this->getTabindex();

    $moviesNavigation = new Navigation($i18n->t("Movies"), [
      [ $i18n->r("/movies"), $i18n->t("Latest Entries") ],
      [ $i18n->r("/movies/charts"), $i18n->t("Charts") ],
      [ $i18n->r("/movie/create"), $i18n->t("Create New") ],
      [ $i18n->r("/movies/reviews"), $i18n->t("Latest Reviews") ],
      [ $i18n->r("/movie/random"), $i18n->t("Random Movie") ],
    ], [ "class" => "span span--3" ]);
    $moviesNavigation->headingLevel  = "3";
    $moviesNavigation->hideTitle     = false;
    $moviesNavigation->unorderedList = true;
    // Retrieve tabindexes.
    $moviesNavigation = (string) $moviesNavigation;

    $seriesNavigation = new Navigation($i18n->t("Series"), [
      [ $i18n->r("/series"), $i18n->t("Latest Entries") ],
      [ $i18n->r("/series/charts"), $i18n->t("Charts") ],
      [ $i18n->r("/series/create"), $i18n->t("Create New") ],
      [ $i18n->r("/series/reviews"), $i18n->t("Latest Reviews") ],
      [ $i18n->r("/series/random"), $i18n->t("Random Series") ],
    ], [ "class" => "span span--3" ]);
    $seriesNavigation->headingLevel  = "3";
    $seriesNavigation->hideTitle     = false;
    $seriesNavigation->unorderedList = true;
    // Retrieve tabindexes.
    $seriesNavigation = (string) $seriesNavigation;

    $personsNavigation = new Navigation($i18n->t("Persons"), [
      [ $i18n->r("/persons"), $i18n->t("Latest Entries") ],
      [ $i18n->r("/person/create"), $i18n->t("Create New") ],
      [ $i18n->r("/person/random"), $i18n->t("Random Person") ],
    ], [ "class" => "span span--3" ]);
    $personsNavigation->headingLevel  = "3";
    $personsNavigation->hideTitle     = false;
    $personsNavigation->unorderedList = true;
    // Retrieve tabindexes.
    $personsNavigation = (string) $personsNavigation;

    $companiesNavigation = new Navigation($i18n->t("Companies"), [
      [ $i18n->r("/companies"), $i18n->t("Latest Entries") ],
      [ $i18n->r("/company/create"), $i18n->t("Create New") ],
      [ $i18n->r("/company/random"), $i18n->t("Random Company") ],
    ], [ "class" => "span span--3" ]);
    $companiesNavigation->headingLevel  = "3";
    $companiesNavigation->hideTitle     = false;
    $companiesNavigation->unorderedList = true;
    // Retrieve tabindexes.
    $companiesNavigation = (string) $companiesNavigation;

    $otherNavigation = new Navigation($i18n->t("More"), [
      [ $i18n->r("/genres"), $i18n->t("Explore all genres") ],
      [ $i18n->r("/articles"), $i18n->t("Explore all articles") ],
    ], [ "class" => "span span--3" ]);
    $otherNavigation->headingLevel  = "3";
    $otherNavigation->hideTitle     = false;
    $otherNavigation->unorderedList = true;
    // Retrieve tabindexes.
    $otherNavigation = (string) $otherNavigation;

    if ($session->isAuthenticated === true) {
      // @todo Store image in session or create special nginx route that stays the same and is internally redirected
      //       to the correct avatar via redirect, e.g. /avatar.jpg?username=fleshgrinder and it's internally
      //       redirected to /upload/user/fleshgrinder.70.jpg
      //       On the other hand, we'd only have to save the changed timestamp in the session to generate the image
      //       route ...
      $user = new \MovLib\Data\User\User(\MovLib\Data\User\User::FROM_ID, $session->userId);
      // @todo We need a 50x50 avatar (this one's 60x60).
      $userIcon = $this->getImage($user->getStyle(\MovLib\Data\User\User::STYLE_SPAN_01), false, [ "id" => "user-avatar-for-now" ]);
      $userNavigation =
        "<ul>" .
          "<li>{$this->a($i18n->r("/profile"), $i18n->t("Profil"))}</li>" .
          "<li>{$this->a($i18n->r("/profile/sign-out"), $i18n->t("Sign Out"))}</li>" .
        "</ul>"
      ;
    }
    else {
      $userIcon = "<span class='button button--inverse ico ico-user-add'></span>";
      $userNavigation =
        "<ul>" .
          "<li>{$this->a($i18n->r("/profile/sign-in"), $i18n->t("Sign In"))}</li>" .
          "<li>{$this->a($i18n->r("/profile/join"), $i18n->t("Join"))}</li>" .
          "<li>{$this->a($i18n->r("/profile/reset-password"), $i18n->t("Reset Password"))}</li>" .
        "</ul>"
      ;
    }

    // @todo Nice placeholder for the missing navigations, there are simply too many people checking out our dev site :P
    $notImplemented = new Alert("Not implemented yet!");

    // @todo Is it a problem that we have nested navigations?
    return
      "<a class='visuallyhidden' href='#main' tabindex='{$skipLinkTabindex}'>{$i18n->t("Skip directly to the page’s main content.")}</a>" .
      "<header id='header' role='banner'><div class='container'><div class='row'>" .
        // Only one <h1> per page? No problem according to Google https://www.youtube.com/watch?v=GIn5qJKU8VM plus HTML5
        // wants us to use multiple <h1>s for multiple sections, so here we go. The header is always the MovLib header.
        "<h1 class='span span--3'>{$this->a(
          "/",
          "<img alt='' height='42' src='{$kernel->getAssetURL("logo/vector", "svg")}' width='42'> {$kernel->siteName}",
          [ "id" => "logo", "title" => $i18n->t("Go back to the home page.") ]
        )}</h1>" .
        "<div class='span span--9'>" .
          "<nav aria-expanded='false' class='expander' role='navigation'>" .
            "<h2 class='visible'>{$i18n->t("Explore")}</h2>" .
            "<div class='concealed row'>" .
              "{$moviesNavigation}{$seriesNavigation}" .
              "<div class='span span--3'>{$personsNavigation}{$companiesNavigation}</div>" .
              $otherNavigation .
            "</div>" .
          "</nav>" .
          "<nav aria-expanded='false' class='expander' role='navigation'>" .
            "<h2 class='visible'>{$i18n->t("Marketplace")}</h2>" .
            "<div class='concealed row'><div class='span span--12'>{$notImplemented}</div></div>" .
          "</nav>" .
          "<nav aria-expanded='false' class='expander' role='navigation'>" .
            "<h2 class='visible'>{$i18n->t("Community")}</h2>" .
            "<div class='concealed row'><div class='span span--12'>{$notImplemented}</div></div>" .
          "</nav>" .
          "<form action='{$i18n->t("/search")}' class='span' id='search' method='post' role='search'>" .
            "<input type='hidden' name='form_id' value='header_search'>" .
            "<label class='visuallyhidden' for='search-input'>{$i18n->t("Search the {sitename} database.", [ "sitename" => $kernel->siteName ])}</label>" .
            "<button class='ico ico-search' tabindex='{$searchSubmitTabindex}' type='submit'><span class='visuallyhidden'>{$i18n->t(
              "Start searching for the entered keyword."
            )}</span></button>" .
            "<input id='search-input' name='searchterm' required tabindex='{$searchInputTabindex}' title='{$i18n->t(
              "Enter the search term you wish to search for and hit enter."
            )}' type='search'>" .
          "</form>" .
          "<nav id='user-nav' aria-expanded='false' class='expander' role='navigation'>" .
            "<h2 class='visuallyhidden'>{$i18n->t("User Navigation")}</h2>{$userIcon}" .
            "<div class='concealed row'><div class='span span--12'>{$userNavigation}</div></div>" .
          "</nav>" .
        "</div>" .
      "</div></div></header>"
    ;
  }

  /**
   * Get the head title.
   *
   * Formats the title of this page for the <code><title></code>-element. A special separator string is used before
   * appending the sitename.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @return string
   *   The head title.
   */
  protected function getHeadTitle() {
    global $i18n, $kernel;
    return $i18n->t("{page_title} — {sitename}", [ "page_title" => $this->title, "sitename" => $kernel->siteName ]);
  }

  /**
   * Get string representation of the current page.
   *
   * Any HTML page needs the HTML header and the wrapping <code><body></code>-element. Therefor the most basic variation
   * is to only print exactly these elements.
   *
   * Short note on why we are using a method called <code>getPresentation()</code> and not the platform provided
   * <code>getPresentation()</code> magic method: any <code>getPresentation()</code>-method can't throw an execption, which is a
   * huge problem in the way we are dealing with errors. Everything throws an exception if something goes wrong and if
   * something goes wrong during the rendering process one wouldn't get any stacktrace, instead a generic
   * <i>getPresentation() must not throw an exception</i> message would be displayed (fatal error of course, so you get
   * nothing).
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @global \MovLib\Data\User\Session $session
   * @return string
   */
  public function getPresentation() {
    global $i18n, $kernel, $session;

    // Build a link for each stylesheet of this page.
    $stylesheets = null;
    $c           = count($kernel->stylesheets);
    for ($i = 0; $i < $c; ++$i) {
      $stylesheets .= "<link href='{$kernel->getAssetURL($kernel->stylesheets[$i], "css")}' rel='stylesheet'>";
    }

    // Apply additional CSS class if the current request is made from a signed in user.
    if ($session->isAuthenticated === true) {
      $this->bodyClasses .= " authenticated";
    }

    // Build the JavaScript settings JSON.
    $kernel->javascriptSettings["domainStatic"] = $kernel->domainStatic;
    $c = count($kernel->javascripts);
    for ($i = 0; $i < $c; ++$i) {
      $kernel->javascriptSettings["modules"][$kernel->javascripts[$i]] = $kernel->getAssetURL($kernel->javascripts[$i], "js");
    }
    $jsSettings = json_encode($kernel->javascriptSettings, JSON_UNESCAPED_UNICODE);

    return
      "<!doctype html>" .
      "<html dir='{$i18n->direction}' id='nojs' lang='{$i18n->languageCode}'>" .
      "<head>" .
        "<title>{$this->getHeadTitle()}</title>" .
        // Include the global styles and any presentation specific ones.
        "<link href='{$kernel->getAssetURL("MovLib", "css")}' rel='stylesheet'>{$stylesheets}" .
        // Yes, we could create these in a loop, but why should we implement a loop for static data? To be honest, I
        // generated it with a loop and simply copied the output here.
        "<link href='{$kernel->getAssetURL("logo/vector", "svg")}' rel='icon' type='image/svg+xml'>" .
        "<link href='{$kernel->getAssetURL("logo/256", "png")}' rel='icon' sizes='256x256' type='image/png'>" .
        "<link href='{$kernel->getAssetURL("logo/128", "png")}' rel='icon' sizes='128x128' type='image/png'>" .
        "<link href='{$kernel->getAssetURL("logo/64", "png")}' rel='icon' sizes='64x64' type='image/png'>" .
        "<link href='{$kernel->getAssetURL("logo/32", "png")}' rel='icon' sizes='32x32' type='image/png'>" .
        "<link href='{$kernel->getAssetURL("logo/24", "png")}' rel='icon' sizes='24x24' type='image/png'>" .
        "<link href='{$kernel->getAssetURL("logo/16", "png")}' rel='icon' sizes='16x16' type='image/png'>" .
        // @todo Add opensearch tag (rel="search").
        "<meta name='viewport' content='width=device-width,initial-scale=1.0'>" .
      "</head>" .
      "<body id='{$this->id}' class='{$this->bodyClasses}'>" .
        "{$this->getHeader()}{$this->getMainContent()}{$this->getFooter()}" .
        "<script id='js-settings' type='application/json'>{$jsSettings}</script>" .
        "<script async src='{$kernel->getAssetURL("MovLib", "js")}'></script>"
    ;
  }

  /**
   * Get the wrapped content, including heading.
   *
   * @return string
   *   The wrapped content, including heading.
   */
  protected function getMainContent() {
    // Allow the presentation to set a heading that includes HTML mark-up.
    $title = $this->pageTitle ?: $this->title;

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

    // Render the page's main element (note that we still include the ARIA role "main" at this point because not all
    // user agents support the new HTML5 element yet).
    return
      "<main id='main' role='main'{$schema}>" .
        "<header>" .
          "<div class='container'>{$this->headingBefore}<h1{$headingprop}>{$title}</h1>{$this->headingAfter}</div>" .
          "<div id='alerts'>{$this->alerts}</div>" .
        "</header>" .
        "{$this->contentBefore}{$this->getContent()}{$this->contentAfter}" .
      "</main>"
    ;
  }

  /**
   * Initialize this presentation.
   *
   * @param string $title
   *   The already translated title of this page.
   * @return this
   */
  protected function init($title) {
    global $i18n, $kernel;

    // The substr() removes the \MovLib\Presentation\ part!
    $className         = strtolower(substr(get_class($this), 20));
    $this->namespace   = explode("\\", $className);
    array_pop($this->namespace); // The last element is the name of the class and not part of the namespace.
    $this->bodyClasses = strtr($className, "\\", " ");
    $this->id          = strtr($className, "\\", "-");
    $this->title       = $title;

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
    $this->breadcrumb       = new Navigation($i18n->t("You are here: "), $trail, [ "class" => "container", "id" => "breadcrumb" ], false);
    $this->breadcrumb->glue = " › ";

    return $this;
  }

}
