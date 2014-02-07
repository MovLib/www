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
 * Default page class with no content.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Page extends \MovLib\Presentation\AbstractBase {


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
  protected $bodyClasses = "";

  /**
   * The presentation's breadcrumb navigation.
   *
   * @var \MovLib\Presentation\Partial\Navigation
   */
  protected $breadcrumb;

  /**
   * The title used for the current page in the breadcrumb, defaults to the current title if not given.
   *
   * @var string
   */
  protected $breadcrumbTitle;

  /**
   * HTML that should be included after the page's content.
   *
   * @var string
   */
  protected $contentAfter;

  /**
   * HTML that should be included before the page's content.
   *
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
   * The page's translated routes.
   *
   * Associative array where the key is the system language code and the value the translated route.
   *
   * @var null|array
   */
  protected $languageLinks;

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

    // Only build the language links if we have routes to build them. For example the internal server error page doesn't
    // need language links ;)
    if ($this->languageLinks) {
      $languageLinks = $currentLanguageName = $teamOffset = null;
      foreach ($this->languageLinks as $code => $route) {
        $language = new \MovLib\Data\Language($code);
        if ($code == $i18n->languageCode) {
          $currentLanguageName = $language->name;
          $languageLinks[$language->name] =
            "<a class='active' href='{$route}' tabindex='0' title='{$i18n->t("You’re currently viewing this page.")}'>{$language->name}</a>"
          ;
        }
        else {
          $languageLinks[$language->name] =
            "<a href='{$kernel->scheme}://{$code}.{$kernel->domainDefault}{$route}'>{$i18n->t("{0} ({1})", [
              $language->name, "<span lang='{$language->code}'>{$language->native}</span>"
            ])}</a>"
          ;
        }
      }

      $i18n->getCollator()->ksort($languageLinks);
      $languageLinks = implode(" ", $languageLinks);

      $languageLinks =
        "<section class='last s s4'>" .
          "<div class='popup'>" .
            "<div class='content'><h2>{$i18n->t("Choose your language")}</h2><small>{$i18n->t(
              "Is your language missing in our list? {0}Help us translate {sitename}.{1}",
              [ "<a href='//{$kernel->domainLocalize}/'>", "</a>", "sitename" => $kernel->siteName ]
            )}</small>{$languageLinks}</div>" .
            "<a class='ico ico-languages' id='f-language' tabindex='0'>{$i18n->t("Language")}: {$currentLanguageName}</a>" .
          "</div>" .
        "</section>"
      ;
    }
    // Insert placeholder and be sure to use div tags instead of section tags.
    else {
      $languageLinks = null;
      $teamOffset    = " o4";
    }

    return
      "<footer id='f' role='contentinfo'>" .
        "<h1 class='vh'>{$i18n->t("Infos all around {sitename}", [ "sitename" => $kernel->siteName ])}</h1>" .
        "<div class='c'><div class='r'>" .
          "<section class='s s12'>" .
            "<h3 class='vh'>{$i18n->t("Copyright and licensing information")}</h3>" .
            "<p id='f-copyright'><span class='ico ico-cc'></span> <span class='ico ico-cc-zero'></span> {$i18n->t(
              "Database data is available under the {0}Creative Commons — CC0 1.0 Universal{1} license.",
              [ "<a href='https://creativecommons.org/publicdomain/zero/1.0/deed.{$i18n->languageCode}' rel='license'>", "</a>" ]
            )}<br>{$i18n->t(
              "Additional terms may apply for third-party content, please refer to any license or copyright information that is additionaly stated."
            )}</p>" .
          "</section>" .
          "<section id='f-logos' class='s s12 tac'>" .
            "<h3 class='vh'>{$i18n->t("Sponsors and external resources")}</h3>" .
            "<a class='img' href='http://www.fh-salzburg.ac.at/' target='_blank'>" .
              "<img alt='Fachhochschule Salzburg' height='30' src='{$kernel->getAssetURL("footer/fachhochschule-salzburg", "svg")}' width='48'>" .
            "</a>" .
            "<a class='img' href='https://github.com/MovLib' target='_blank'>" .
              "<img alt='GitHub' height='30' src='{$kernel->getAssetURL("footer/github", "svg")}' width='48'>" .
            "</a>" .
          "</section>" .
          $languageLinks .
          "<section id='f-team' class='last{$teamOffset} s s4 tac'><h3>{$this->a($i18n->r("/team"), $i18n->t("Made with {love} in Austria", [
            "love" => "<span class='ico ico-heart'></span><span class='vh'>{$i18n->t("love")}</span>"
          ]))}</h3></section>" .
          "<section class='last s s4 tar'>" .
            "<h3 class='vh'>{$i18n->t("Legal Links")}</h3>" .
            "{$this->a($i18n->r("/impressum"), $i18n->t("Impressum"))} · " .
            "{$this->a($i18n->r("/privacy-policy"), $i18n->t("Privacy Policy"))} · " .
            "{$this->a($i18n->r("/terms-of-use"), $i18n->t("Terms of Use"))}" .
          "</section>" .
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

    $subNavigations = [];
    foreach ([
      "explore" => [
        "movies" => [ $i18n->t("Movies"), [
          [ $i18n->rp("/movies"), $i18n->t("Latest Entries") ],
          [ $i18n->rp("/movies/charts"), $i18n->t("Charts") ],
          [ $i18n->r("/movie/create"), $i18n->t("Create New") ],
          [ $i18n->r("/movie/random"), $i18n->t("Random Movie") ],
        ]],
        "serials" => [ $i18n->t("Serials"), [
          [ $i18n->rp("/serials"), $i18n->t("Latest Entries") ],
          [ $i18n->rp("/serials/charts"), $i18n->t("Charts") ],
          [ $i18n->r("/serial/create"), $i18n->t("Create New") ],
          [ $i18n->r("/serial/random"), $i18n->t("Random Serial") ],
        ]],
        "persons" => [ $i18n->t("Persons"), [
          [ $i18n->rp("/persons"), $i18n->t("Latest Entries") ],
          [ $i18n->r("/person/create"), $i18n->t("Create New") ],
          [ $i18n->r("/person/random"), $i18n->t("Random Person") ],
        ]],
        "companies" => [ $i18n->t("Companies"), [
          [ $i18n->rp("/companies"), $i18n->t("Latest Entries") ],
          [ $i18n->r("/company/create"), $i18n->t("Create New") ],
          [ $i18n->r("/company/random"), $i18n->t("Random Company") ],
        ]],
        "more" => [ $i18n->t("More"), [
          [ $i18n->rp("/genres"), $i18n->t("Explore all genres") ],
        ]],
      ],
      "community" => [
        "users" => [ $i18n->t("Users"), [
          [ $i18n->rp("/users"), $i18n->t("Latest Users") ],
        ]],
        "utilities" => [ $i18n->t("Utilities"), [
          [ $i18n->rp("/deletion-requests"), $i18n->t("Deletion Requests") ],
        ]],
      ],
    ] as $name => $subNavigation) {
      foreach ($subNavigation as $subName => $subNav) {
        $subNavigations[$name][$subName]                = new Navigation($subNav[0], $subNav[1], [ "class" => "s s3" ]);
        $subNavigations[$name][$subName]->headingLevel  = "3";
        $subNavigations[$name][$subName]->hideTitle     = false;
        $subNavigations[$name][$subName]->unorderedList = true;
      }
    }

    if ($session->isAuthenticated === true) {
      $userIcon = "<div class='clicker ico ico-settings authenticated'>{$this->getImage($session->userAvatar, false)}<span class='badge'>2</span></div>";
      $userNavigation =
        "<ul class='o1 sm2 no-list'>" .
          "<li>{$this->a($i18n->r("/profile/messages"), $i18n->t("Messages"), [ "class" => "ico ico-email" ])}</li>" .
          "<li>{$this->a($i18n->r("/profile/collection"), $i18n->t("Collection"), [ "class" => "ico ico-release" ])}</li>" .
          "<li>{$this->a($i18n->r("/profile/wantlist"), $i18n->t("Wantlist"), [ "class" => "ico ico-heart" ])}</li>" .
          "<li>{$this->a($i18n->r("/profile/lists"), $i18n->t("Lists"), [ "class" => "ico ico-ul" ])}</li>" .
          "<li>{$this->a($i18n->r("/profile/watchlist"), $i18n->t("Watchlist"), [ "class" => "ico ico-view" ])}</li>" .
          "<li class='separator'>{$this->a($i18n->r("/profile"), $i18n->t("Profile"), [ "class" => "ico ico-user" ])}</li>" .
          "<li>{$this->a($i18n->r("/profile/account-settings"), $i18n->t("Settings"), [ "class" => "ico ico-settings" ])}</li>" .
          "<li class='separator name'>{$session->userName}</li>" .
          "<li>{$this->a($i18n->r("/profile/sign-out"), $i18n->t("Sign Out"), [ "class" => "danger" ])}</li>" .
        "</ul>" .
        $this->getImage($session->userAvatar, $i18n->r("/profile"))
      ;
    }
    else {
      $userIcon = "<div class='btn btn-inverse clicker ico ico-user-add'></div>";
      $userNavigation =
        "<ul class='o1 sm2 no-list'>" .
          "<li>{$this->a($i18n->r("/profile/sign-in"), $i18n->t("Sign In"))}</li>" .
          "<li>{$this->a($i18n->r("/profile/join"), $i18n->t("Join"))}</li>" .
          "<li>{$this->a($i18n->r("/profile/reset-password"), $i18n->t("Forgot Password"))}</li>" .
        "</ul>"
      ;
    }

    $notImplemented = new Alert("Not implemented yet!");

    $searchQuery = isset($_GET["q"]) ? $_GET["q"] : null;

    return
      // No skip-to-content link! We have proper headings, semantic HTML5 elements and proper ARIA landmarks!
      "<header id='h' role='banner'><div class='c'><div class='r'>" .
        // Only one <h1> per page? No problem according to Google https://www.youtube.com/watch?v=GIn5qJKU8VM plus HTML5
        // wants us to use multiple <h1>s for multiple sections, so here we go. The header is always the MovLib header.
        "<h1 class='s s3'>{$this->a(
          "/",
          "<img alt='' height='42' src='{$kernel->getAssetURL("logo/vector", "svg")}' width='42'> {$kernel->siteName}",
          [ "id" => "l", "title" => $i18n->t("Go back to the home page.") ]
        )}</h1>" .
        "<div class='s s9'>" .
          "<nav aria-expanded='false' aria-haspopup='true' class='expander' id='explore-nav' role='navigation' tabindex='0'>" .
            "<h2 class='visible clicker'>{$i18n->t("Explore")}</h2>" .
            "<div class='concealed r'>" .
              $subNavigations["explore"]["movies"] .
              $subNavigations["explore"]["serials"] .
              "<div class='s s3'>{$subNavigations["explore"]["persons"]}{$subNavigations["explore"]["companies"]}</div>" .
              $subNavigations["explore"]["more"] .
            "</div>" .
          "</nav>" .
          "<nav aria-expanded='false' aria-haspopup='true' class='expander' id='marketplace-nav' role='navigation' tabindex='0'>" .
            "<h2 class='visible clicker'>{$i18n->t("Marketplace")}</h2>" .
            "<div class='concealed r'><div class='s s12'>{$notImplemented}</div></div>" .
          "</nav>" .
          "<nav aria-expanded='false' aria-haspopup='true' class='expander' id='community-nav' role='navigation' tabindex='0'>" .
            "<h2 class='visible clicker'>{$i18n->t("Community")}</h2>" .
            "<div class='concealed r'>" .
              $subNavigations["community"]["users"] .
              $subNavigations["community"]["utilities"] .
            "</div>" .
          "</nav>" .
          "<form action='{$i18n->r("/search")}' class='s' id='s' method='get' role='search'>" .
            "<button class='ico ico-search' tabindex='2' type='submit'><span class='vh'>{$i18n->t(
              "Start searching for the entered keyword."
            )}</span></button>" .
            "<input name='q' required tabindex='1' title='{$i18n->t(
              "Enter the search term you wish to search for and hit enter."
            )}' type='search' value='{$searchQuery}'>" .
          "</form>" .
          "<nav aria-expanded='false' aria-haspopup='true' class='expander' id='user-nav' role='navigation' tabindex='0'>" .
            "<h2 class='vh'>{$i18n->t("User Navigation")}</h2>{$userIcon}" .
            "<div class='concealed s sm3'>{$userNavigation}</div>" .
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

    // Allow the presentation to alter presentation in getContent() method.
    $content = $this->getMainContent();

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

    $title   = $this->getHeadTitle();
    $logo256 = $kernel->getAssetURL("logo/256", "png");

    return
      "<!doctype html>" .
      "<html dir='{$i18n->direction}' id='nojs' lang='{$i18n->languageCode}' prefix='og: http://ogp.me/ns#'>" .
      "<head>" .
        "<title>{$title}</title>" .
        // Include the global styles and any presentation specific ones.
        "<link href='{$kernel->getAssetURL("MovLib", "css")}' rel='stylesheet'>{$stylesheets}" .
        // Yes, we could create these in a loop, but why should we implement a loop for static data? To be honest, I
        // generated it with a loop and simply copied the output here.
        "<link href='{$kernel->getAssetURL("logo/vector", "svg")}' rel='icon' type='image/svg+xml'>" .
        "<link href='{$logo256}' rel='icon' sizes='256x256' type='image/png'>" .
        "<link href='{$kernel->getAssetURL("logo/128", "png")}' rel='icon' sizes='128x128' type='image/png'>" .
        "<link href='{$kernel->getAssetURL("logo/64", "png")}' rel='icon' sizes='64x64' type='image/png'>" .
        "<link href='{$kernel->getAssetURL("logo/32", "png")}' rel='icon' sizes='32x32' type='image/png'>" .
        "<link href='{$kernel->getAssetURL("logo/24", "png")}' rel='icon' sizes='24x24' type='image/png'>" .
        "<link href='{$kernel->getAssetURL("logo/16", "png")}' rel='icon' sizes='16x16' type='image/png'>" .
        "<link href='https://plus.google.com/115387876584819891316' rel='publisher'>" .
        "<meta property='og:description' content='{$i18n->t("The free online movie database that anyone can edit.")}'>" .
        "<meta property='og:image' content='{$kernel->scheme}:{$logo256}'>" .
        "<meta property='og:site_name' content='{$kernel->siteName}'>" .
        "<meta property='og:title' content='{$title}'>" .
        "<meta property='og:type' content='website'>" .
        "<meta property='og:url' content='{$kernel->scheme}://{$kernel->hostname}{$kernel->requestURI}'>" .
        // @todo Add opensearch tag (rel="search").
      "</head>" .
      "<body id='{$this->id}' class='{$this->bodyClasses}'>" .
        "{$this->getHeader()}{$content}{$this->getFooter()}" .
        "<script id='jss' type='application/json'>{$jsSettings}</script>" .
        "<script async src='{$kernel->getAssetURL("MovLib", "js")}'></script>"
    ;
  }

  /**
   * Get the wrapped content, including heading.
   *
   * @global \MovLib\Kernel $kernel
   * @return string
   *   The wrapped content, including heading.
   */
  protected function getMainContent() {
    global $kernel;

    // Allow the presentation to alter the main content in getContent() method.
    $content = $this->getContent();

    // Allow the presentation to set a heading that includes HTML mark-up.
    $title = $this->pageTitle ?: $this->title;

    // Add the current page to the breadcrumb.
    if ($this->breadcrumb) {
      $this->breadcrumb->menuitems[] = [ $kernel->requestPath, $this->breadcrumbTitle ?: $this->title ];
    }

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
      "<main id='m' role='main'{$schema}>" .
        "<header id='header'>" .
          "<div class='c'>{$this->breadcrumb}{$this->headingBefore}<h1{$headingprop}>{$title}</h1>{$this->headingAfter}</div>" .
          $this->alerts .
        "</header>" .
        "{$this->contentBefore}{$content}{$this->contentAfter}" .
      "</main>"
    ;
  }

  /**
   * Initialize the page's breadcrumb.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param array $breadcrumbs [optional]
   *   Numeric array containing additional breadcrumbs to put between home and the current page.
   * @return this
   */
  protected function initBreadcrumb(array $breadcrumbs = []) {
    global $i18n;

    // Initialize the breadcrumb navigation and always include the home page's link and the currently displayed page.
    $trail       = [[ "/", $i18n->t("Home"), [ "title" => $i18n->t("Go back to the home page.") ] ] ];
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

    // Create the actual navigation with the trail we just built.
    $this->breadcrumb       = new Navigation($i18n->t("You are here: "), $trail, [ "class" => "c", "id" => "b" ]);
    $this->breadcrumb->glue = " › ";

    return $this;
  }

  /**
   * Initialize the language links for the current page.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @param string $route
   *   The key of this route.
   * @param array $args [optional]
   *   The route arguments, defaults to no arguments.
   * @param boolean $plural [optional]
   *   Set to <code>TRUE</code> if the current page has a plural route, defaults to <code>FALSE</code>.
   * @param string $query [optional]
   *   Append string to each language link.
   * @return this
   */
  protected function initLanguageLinks($route, array $args = null, $plural = false, $query = null) {
    global $i18n, $kernel;

    // Not pretty but efficient, only check once if we have plural form or singular.
    if ($plural === true) {
      foreach ($kernel->systemLanguages as $code => $locale) {
        $this->languageLinks[$code] = "{$i18n->rp($route, $args, $locale)}{$query}";
      }
    }
    else {
      foreach ($kernel->systemLanguages as $code => $locale) {
        $this->languageLinks[$code] = "{$i18n->r($route, $args, $locale)}{$query}";
      }
    }

    return $this;
  }

  /**
   * Initialize the page.
   *
   * @param string $title
   *   The already translated title of this page.
   * @return this
   */
  protected function initPage($title) {
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

    // Add all alerts that are stored in a cookie to the current presentation and remove them afterwards.
    if (isset($_COOKIE["alerts"])) {
      $this->alerts .= $_COOKIE["alerts"];
      setcookie("alerts", "", 1, "/", $kernel->domainDefault, $kernel->https, true);
    }

    return $this;
  }

}
