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
use \ReflectionClass;

/**
 * The <b>AbstractView</b> is the base class for all other HTML views.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractView {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Severity level <em>error</em> for alert message (color <em>red</em>).
   *
   * @var string
   */
  const ALERT_SEVERITY_ERROR = "error";

  /**
   * Severity level <em>info</em> for alert message (color <em>blue</em>).
   *
   * @var string
   */
  const ALERT_SEVERITY_INFO = "info";

  /**
   * Severity level <em>success</em> for alert message (color <em>green</em>).
   *
   * @var string
   */
  const ALERT_SEVERITY_SUCCESS = "success";

  /**
   * Severity level <em>warning</em> (default) for alert message (color <em>yellow</em>).
   *
   * @var string
   */
  const ALERT_SEVERITY_WARNING = "warning";


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Array index of the active header navigation point.
   *
   * This is kept complicated to ensure that it's not easily overwritten. The active state of a header navigation point
   * is not determined by the presenter, because it only affects the view itself, but not the presentation of the pages
   * content. The amount of header navigation points is very limited and if a new view class is created the developer is
   * forced to have a look at the methods body to find out which index might be the right one.
   *
   * @see \MovLib\View\HTML\AbstractView::$activeHeaderUserNavigationPoint
   * @var int
   */
  protected $activeHeaderNavigationPoint;

  /**
   * Array index of the active header user navigation point.
   *
   * @see \MovLib\View\HTML\AbstractView::$activeHeaderNavigationPoint
   * @var int
   */
  protected $activeHeaderUserNavigationPoint;

  /**
   * Array containing all alert messages that might be set during execution.
   *
   * @var array
   */
  private $alerts = [];

  /**
   * The HTML elements tag that is used to wrap the content, defaults to <tt>div</tt>.
   *
   * @var string
   */
  protected $contentHtmlElement = "div";

  /**
   * The presenter that created the view instance.
   *
   * @var \MovLib\Presenter\AbstractPresenter
   */
  protected $presenter;

  /**
   * Array that contains all stylesheet for the view.
   *
   * @var array
   */
  protected $stylesheets = [];

  /**
   * The title of the page.
   *
   * @var string
   */
  protected $title = "";


  // ------------------------------------------------------------------------------------------------------------------- Constructor


  /**
   * Initialize new view.
   *
   * @param \MovLib\Presenter\AbstractPresenter $presenter
   *   The presenter that created the view instance.
   * @param string $title
   *   The unique title of this view.
   */
  public function __construct($presenter, $title) {
    $this->presenter = $presenter;
    $this->title = $title;
    $this->addStylesheet([
      "//fonts.googleapis.com/css?family=Open+Sans:300,300italic,400,400italic,600,600italic,700,700italic,800,800italic&amp;subset=latin,cyrillic-ext,greek-ext,greek,vietnamese,latin-ext,cyrillic",
      "/assets/css/modules/entypo.css",
      "/assets/css/base.css",
      "/assets/css/layout.css",
      "/assets/css/layout-responsive.css",
      "/assets/css/modules/alert.css",
      "/assets/css/modules/button.css",
    ]);
  }


  // ------------------------------------------------------------------------------------------------------------------- Abstract Methods


  /**
   * Get the rendered content, without HTML head, header or footer.
   *
   * @return string
   */
  abstract public function getContent();


  // ------------------------------------------------------------------------------------------------------------------- Protected Methods


  /**
   * Add a stylesheet to the view.
   *
   * @todo Aggregate, minify and compress for production. What we have here is only meant for development, so it's easy
   *       to add stylesheets to a specific view. In production we only want to deliver a single stylesheet and the
   *       system will change.
   * @param string|array $stylesheets
   *   The absolute path to a single (string) stylesheet or multiple (array) stylesheets (can be external URL as well).
   * @return $this
   */
  protected final function addStylesheet($stylesheets) {
    if (!is_array($stylesheets)) {
      $this->stylesheets[] = $stylesheets;
    }
    // No need to check if this stylesheet is already in our array. This is only for development and if a dev includes
    // the same stylesheet twice, shame on him or her. :P
    else {
      $stylesheetCount = count($stylesheets);
      for ($i = 0; $i < $stylesheetCount; ++$i) {
        $this->stylesheets[] = $stylesheets[$i];
      }
    }
    return $this;
  }

  /**
   * Expand the given HTML element attributes for usage on an HTML element.
   *
   * @param array $attributes
   *   [optional] The attributes that should be expanded, if array is empty, empty stirng is returned.
   * @return string
   *   Expanded attributes or empty string.
   */
  protected final function expandTagAttributes($attributes = []) {
    $expandedAttributes = "";
    if (empty($attributes)) {
      return $expandedAttributes;
    }
    foreach ($attributes as $attribute => $value) {
      switch ($attribute) {
        case "href":
        case "src":
        case "action":
          $value = String::checkUrl($value);
          break;

        default:
          $value = String::checkPlain($value);
      }
      $expandedAttributes .= " {$attribute}='{$value}'";
    }
    return $expandedAttributes;
  }

  /**
   * Get current counter of the global <code>tabindex</code>-attribute for HTML elements and increment the static
   * variable associated with it once.
   *
   * Many browsers have a very strange <kbd>tab</kbd>-policy. This counter variable is to make sure that users who love
   * or have to use the keyboard can easily navigate through our pages. You should only use the tabindex for
   * <strong>important</strong> page elements. For instance, the main navigation isn't that important for a user if he
   * already reached the page he wants. On the other hand the header search field is a very important field, in contrast
   * to that the associated search submit button is not. If a user is using the <kbd>tab</kbd>-key to navigate through
   * the page, she or he most certainly also knows that he can easily submit the form by hitting enter within the search
   * field itself.
   *
   * Use all your knowledge as web user to decide whetever an element is important enough to make use of this index or
   * not.
   *
   * @link http://www.w3.org/TR/2010/WD-wai-aria-practices-20100916/#focus_tabindex
   * @link http://www.w3.org/TR/wai-aria/usage#managingfocus
   * @staticvar int $tabindex
   *   Static counter to keep track of the page's tabindex accros a single request.
   * @return int
   *   The current counter of the index.
   */
  protected final function getTabindex() {
    static $tabindex = 1;
    return $tabindex++;
  }

  /**
   * Get the title of this view.
   *
   * @return string
   *   The title of this view.
   */
  public final function getTitle() {
    return $this->title;
  }

  /**
   * Add a CSS class to an existing attributes array.
   *
   * @param string $class
   *   String of CSS classes that should be added to <var>$attributes</var>.
   * @param array $attributes
   *   The array containing the previously set attributes for the elment.
   */
  protected final function addClass($class, &$attributes) {
    if (!is_array($attributes)) {
      $attributes = [];
    }
    if (isset($attributes["class"])) {
      $attributes["class"] .= " {$class}";
    } else {
      $attributes["class"] = $class;
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Public Final Methods


  /**
   * Create HTML anchor element.
   *
   * <b>IMPORTANT:</b> Always use this method to generate crosslinks! This method ensures that no links within the
   * document point to the currently displayed document itself; as per W3C recommendation.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance.
   * @param string $route
   *   The URL to which we should link (only internal routes).
   * @param string $text
   *   The text that should be displayed as anchor.
   * @param string|array $titleOrAttributes
   *   [Optional] If you pass along a string it is simply used as the <code>title</code>-attribute of the anchor and if
   *   you pass along an array it will be expanded.
   * @return string
   *   The anchor element ready for print.
   */
  public final function a($route, $text, $titleOrAttributes = false) {
    global $i18n;
    $isArray = is_array($titleOrAttributes);
    $route = is_array($route) ? $i18n->r($route[0], $route[1]) : $i18n->r($route);
    $text = is_array($text) ? $i18n->t($text[0], $text[1]) : $i18n->t($text);
    // Check if given route needs a slash at the beginning.
    if (!empty($route)) {
      if ($route[0] !== "#" && $route[0] !== "/") {
        $route = "/{$route}";
      }
    }
    // Never create a link to the current page, http://www.nngroup.com/articles/avoid-within-page-links/
    if (empty($route) || $route === $_SERVER["REQUEST_URI"]) {
      // A hash keeps the anchor element itself but removes the link to the current page—perfect!
      $route = "#";
      // Remove the title if we have one in the attributes array.
      if ($isArray === true && isset($titleOrAttributes["title"])) {
        unset($titleOrAttributes["title"]);
      }
      // Mark this anchor-element to be active, this will also remove the title if the variable contained a string.
      $this->addClass("active", $titleOrAttributes);
      // Now we are dealing with an array for sure.
      $isArray = true;
    }
    // Check if we are dealing with a simple title or multiple attributes.
    if ($titleOrAttributes !== false) {
      $titleOrAttributes = $isArray === true ? $this->expandTagAttributes($titleOrAttributes) : " title='" . String::checkPlain($titleOrAttributes) . "'";
    }
    return "<a href='{$route}'{$titleOrAttributes}>{$text}</a>";
  }

  /**
   * Get the views short class name (e.g. <em>abstract</em> for <em>AbstractView</em>).
   *
   * The short name is the name of the current instance of this class without the namespace only in lower case letters.
   * This is used to mark various HTML elements for easy CSS and JavaScript access. For instance the
   * <code>&lt;body&gt;</code>-element has this class applied, or the <code>&lt;div&gt;</code> that wraps the pages
   * content in full view (with <tt>-content</tt> suffix).
   *
   * @staticvar string $shortName
   *   Used to cache the short name of this instance.
   * @return string
   *   The short name of the class (lowercased).
   */
  public final function getShortName() {
    static $shortName = null;
    if ($shortName === null) {
      // Always remove the "view" suffix from the name, this is redundant and not needed in the frontend.
      $shortName = substr(strtolower((new ReflectionClass($this))->getShortName()), 0, -4);
    }
    return $shortName;
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
  public final function getAlerts() {
    return "<div id='alerts' class='container'>" . implode("", $this->alerts) . "</div>";
  }

  /**
   * Get the HTML head element, this includes doctype and the html root element.
   *
   * @link http://www.netmagazine.com/features/create-perfect-favicon
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance.
   * @global \MovLib\Model\UserModel $user
   *   The global user model instance.
   * @return string
   *   The head ready for print.
   */
  public final function getHead() {
    global $i18n, $user;
    $stylesheets = "";
    $stylesheetCount = count($this->stylesheets);
    for ($i = 0; $i < $stylesheetCount; ++$i) {
      $stylesheets .= "<link rel='stylesheet' href='{$this->stylesheets[$i]}'>";
    }
    $bodyClass = "{$this->getShortName()}-body";
    if ($user->isLoggedIn === true) {
      $bodyClass .= " logged-in";
    }
    $ariaRole = "document";
    if (strpos($this->getShortName(), "edit") !== false) {
      $ariaRole = "application";
    }
    return
      "<!doctype html>" .
      "<html id='nojs' lang='{$i18n->languageCode}' dir='{$i18n->direction}'>" .
      "<head>" .
        "<title>{$this->getHeadTitle()}</title>" .
        $stylesheets .
        "<link rel='icon' type='image/svg+xml' href='/assets/img/logo/vector.svg'>" .
        "<link rel='icon' type='image/png' sizes='256x256' href='/assets/img/logo/256.png'>" .
        "<link rel='icon' type='image/png' sizes='128x128' href='/assets/img/logo/128.png'>" .
        "<link rel='icon' type='image/png' sizes='64x64' href='/assets/img/logo/64.png'>" .
        "<link rel='icon' type='image/png' sizes='32x32' href='/assets/img/logo/32.png'>" .
        "<link rel='icon' type='image/png' sizes='24x24' href='/assets/img/logo/24.png'>" .
        "<link rel='icon' type='image/png' sizes='16x16' href='/assets/img/logo/16.png'>" .
        // @todo Add opensearch tag (rel="search").
        "<meta name='viewport' content='width=device-width,initial-scale=1.0'>" .
      "</head>" .
      "<body class='{$bodyClass}' role='{$ariaRole}'>"
    ;
  }

  /**
   * Get the HTML header, this includes the logo, navigations and search box.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance.
   * @return string
   *   The header ready for print.
   */
  public final function getHeader() {
    global $i18n;
    return
      "<a class='visuallyhidden' href='#content'>{$i18n->t("Skip to content")}</a>" .
      "<header id='header'>" .
        "<div class='container'>" .
          "<div class='row'>" .
            "<div class='span span--4'>{$this->getHeaderLogo()}</div>" .
            "<div class='span span--4'>{$this->getHeaderSearch()}{$this->getHeaderNavigation()}</div>" .
            "<div class='span span--4'>{$this->getHeaderUserNavigation()}</div>" .
          "</div>" .
        "</div>" .
      "</header>"
    ;
  }

  /**
   * Get the HTML header main navigation.
   *
   * @see \MovLib\View\HTML\AbstractView::getNavigation
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance.
   * @return string
   *   The main navigation ready for print.
   */
  public final function getHeaderNavigation() {
    global $i18n;
    return $this->getNavigation($i18n->t("Primary navigation"), "main", [
      /* 0 => */[
        "href" => $i18n->r("/movies"),
        "text" => $i18n->t("Movies"),
        "title" => $i18n->t("Browse all movies of this world, check out the latest additions or create a new entry yourself.")
      ],
      /* 1 => */[
        "href" => $i18n->r("/series"),
        "text" => $i18n->t("Series"),
        "title" => $i18n->t("Browse all series of this world, check out the latest additions or create a new entry yourself.")
      ],
      /* 2 => */[
        "href" => $i18n->r("/persons"),
        "text" => $i18n->t("Persons"),
        "title" => $i18n->t("Browse all movie related persons of this world, check out the latest additions or create a new entry yourself.")
      ],
      /* 3 => */[
        "href" => $i18n->r("/marketplace"),
        "text" => $i18n->t("Marketplace"),
        "title" => $i18n->t("Searching for a specific release of a movie or soundtrack, this is the place to go, for free of course.")
      ],
    ], $this->activeHeaderNavigationPoint, " <span role='presentation'>/</span> ");
  }

  /**
   * Get the HTML header search.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance.
   * @return string
   *   The header search ready for print.
   */
  public final function getHeaderSearch() {
    global $i18n;
    return
      "<form action='{$i18n->r("/search")}' class='search search-header' method='post' role='search'>" .
        "<input accesskey='f' class='input input-text input-search search-header__input-search' placeholder='{$i18n->t("Search…")}' role='textbox' tabindex='{$this->getTabindex()}' title='{$i18n->t("Enter the search term you wish to search for and hit enter. [alt-shift-f]")}' type='search'>" .
        "<button class='input input-submit search-header__input-submit' title='{$i18n->t("Start searching for the entered keyword.")}' type='submit'>" .
          "<i class='icon icon--search search-header__icon--search inline transition'></i>" .
        "</button>" .
      "</form>"
    ;
  }

  /**
   * Get the HTML header user navigation.
   *
   * @see \MovLib\View\HTML\AbstractView::getNavigation
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance.
   * @global \MovLib\Model\UserModel $user
   *   The global user model instance.
   * @return string
   *   The user navigation ready for print.
   */
  public final function getHeaderUserNavigation() {
    global $i18n, $user;
    if ($user->isLoggedIn === true) {
      // @todo Implement logged in user navigation.
    }
    return $this->getNavigation($i18n->t("User navigation"), "user", [
      /* 0 => */[
        "href" => $i18n->r("/user/sign-up"),
        "text" => $i18n->t("Sign up"),
        "title" => $i18n->t("Click here to sign up for a new and free account."),
      ],
      /* 1 => */[
        "href" => $i18n->r("/user/sign-in"),
        "text" => $i18n->t("Sign in"),
        "title" => $i18n->t("Already have an account? Click here to sign in."),
      ],
      /* 2 => */[
        "href" => $i18n->r("/help"),
        "text" => $i18n->t("Help"),
        "title" => $i18n->t("If you have questions click here to find our help articles."),
        "attributes" => [ "rel" => "help" ],
      ],
    ], $this->activeHeaderUserNavigationPoint, " ", [ "class" => "pull-right" ]);
  }

  /**
   * Helper method to generate a navigation.
   *
   * @param string $title
   *   The title of the section, this will be wrapped in a <code>&lt;h2&gt;</code>.
   * @param string $role
   *   The logic role of this navigation menu (e.g. <em>main</em>, <em>footer</em>, ...).
   * @param array $points
   *   Numeric array containing the navigation points.
   * @param int $activePointIndex
   *   Index of the element within the array that should be marked active.
   * @param string $glue
   *   The string that is used to combine the various navigation points.
   * @param array $attributes
   *   [optional] The attributes that should be applied to the HTML nav element.
   * @param boolean $hideTitle
   *   [optional] Defines if the title should be hidden or not, default is to hide the title on navigation elements.
   * @return string
   *   Fully rendered navigation.
   */
  public final function getNavigation($title, $role, $points, $activePointIndex, $glue, $attributes = [], $hideTitle = true) {
    $menu = "";
    $k = count($points);
    $attr = [ "class" => "menuitem {$role}-nav__menuitem", "role" => "menuitem" ];
    for ($i = 0; $i < $k; ++$i) {
      if (!isset($points[$i]["attributes"])) {
        $points[$i]["attributes"] = [];
      }
      $this->addClass($attr["class"], $points[$i]["attributes"]);
      if ($i !== 0) {
        $menu .= $glue;
      }
      if ($i === $activePointIndex) {
        $this->addClass("active", $points[$i]["attributes"]);
      }
      if (isset($points[$i]["title"])) {
        $points[$i]["attributes"]["title"] = $points[$i]["title"];
      }
      $points[$i]["attributes"]["role"] = "menuitem";
      $menu .= $this->a($points[$i]["href"], $points[$i]["text"], $points[$i]["attributes"]);
    }
    $this->addClass("nav {$role}-nav", $attributes);
    $attributes["role"] = "menu";
    $attributes["aria-labelledby"] = "{$role}-nav__title";
    $titleClass = "";
    if ($hideTitle === true) {
      $titleClass = " class='visuallyhidden'";
    }
    return
      "<nav{$this->expandTagAttributes($attributes)}>" .
        "<h2 id='{$role}-nav__title'{$titleClass} role='presentation'>{$title}</h2>" .
        $menu .
      "</nav>"
    ;
  }

  /**
   * Get the (pure CSS) sticky header.
   *
   * @link http://uxdesign.smashingmagazine.com/2012/09/11/sticky-menus-are-quicker-to-navigate/
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance.
   * @return string
   *   Sticky header ready for print.
   */
  public final function getStickyHeader() {
    global $i18n;
    return
      "<header id='sticky-header'>" .
        "<div class='row'>" .
          "<div class='span span--3 sticky-header__span'>{$this->a("/", "MovLib", [ "class" => "logo-small inline" ])}</div>" .
          "<div class='span span--3 sticky-header__span'>" .
            "<form action='{$i18n->r("/search")}' class='search search-sticky-header' method='post' role='search'>" .
              "<input class='input input-text input-search search-sticky-header__input-search' placeholder='{$i18n->t("Search…")}' role='textbox' title='{$i18n->t("Enter the search term you wish to search for and hit enter.")}' type='search'>" .
              "<button class='button input input-submit search-sticky-header__input-submit' title='{$i18n->t("Start searching for the entered keyword.")}' type='submit'>" .
                "<i class='icon icon--search search-sticky-header__icon--search inline transition'></i>" .
              "</button>" .
            "</form>" .
          "</div>" .
        "</div>" .
      "</header>"
    ;
  }

  /**
   * Add new alert message to the output of the view.
   *
   * @link http://www.w3.org/TR/wai-aria/roles#alert
   * @link http://www.w3.org/TR/wai-aria/states_and_properties#aria-live
   * @param string $message
   *   The message that should be displayed to the user.
   * @param string $title
   *   [optional] [recommended] Short descriptive title that summarizes the alert, defaults to no title at all.
   * @param string $severity
   *   [optional] The severity level of this alert, defaults to <var>AbstractView::ALERT_SEVERITY_WARNING</var>.
   * @param boolean $block
   *   [optional] If your message is very long, or your alert is very important, increase the padding around the message
   *   and enclose the title in a level-4 heading instead of the bold tag.
   * @return \MovLib\View\HTML\AbstractView
   */
  public final function setAlert($message, $title = "", $severity = self::ALERT_SEVERITY_WARNING, $block = false) {
    if (empty($title) === false) {
      $tag = ($block === true) ? "h4" : "b";
      $title = "<{$tag} class='alert__title'>{$title}</{$tag}>";
    }
    $class = "";
    if ($block === true) {
      $class .= " alert--block";
    }
    $this->alerts[] = "<div class='alert alert--{$severity}{$class}' role='alert'>{$title} {$message}</div>";
    return $this;
  }


  // ------------------------------------------------------------------------------------------------------------------- Public Methods


  /**
   * Get the breadcrumb navigation.
   *
   * @see \MovLib\Presenter\AbstractPresenter::getBreadcrumb()
   * @see \MovLib\View\HTML\AbstractView::getNavigation()
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance.
   * @return string
   *   The breadcrumb ready for print.
   */
  public function getBreadcrumb() {
    global $i18n;
    $points = [[ "href" => "/", "text" => $i18n->t("Home"), "title" => $i18n->t("Go back to the home page.") ]];
    $trail = $this->presenter->getBreadcrumb();
    $trailCount = count($trail);
    if ($trailCount !== 0) {
      for ($i = 0; $i < $trailCount; ++$i) {
        $trail[$i]["text"] = String::shorten($trail[$i]["text"], 25, $i18n->t("…"));
        $points[] = $trail[$i];
      }
    }
    $points[] = [ "href" => $_SERVER["REQUEST_URI"], "text" => $this->title ];
    return "<div id='breadcrumb'>{$this->getNavigation($i18n->t("You are here: "), "breadcrumb", $points, -1, " › ", [ "class" => "container text-right" ])}</div>";
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
        "<div id='footer-links' class='container'>" .
          "<div id='footer-rows' class='row'>" .
            "<div class='span span--3'>" .
              "<h3>MovLib</h3>" .
              "<ul class='no-list'>" .
                "<li class='item-first'>{$this->a("/about", "About", $i18n->t("Find out more about us."))}</li>" .
                "<li>{$this->a("/blog", "Blog", $i18n->t("Stay up to date about the latest developments."))}</li>" .
                "<li>{$this->a("/contact", "Contact", $i18n->t("Feedback is always welcome, no matter if positive or negative."))}</li>" .
                "<li>{$this->a("/resources", "Logos and Badges", $i18n->t("If you want to create something awesome."))}</li>" .
                "<li class='item-last'>{$this->a("/legal", "Legal", $i18n->t("Collection of the various legal terms and conditions."))}</li>" .
              "</ul>" .
            "</div>" .
            "<div class='span span--3'>" .
              "<h3>{$i18n->t("Join in")}</h3>" .
              "<ul class='no-list'>" .
                "<li class='item-first item-last'>{$this->a("/user/sign-up", "Sign up", $i18n->t("Become a member and help building the biggest free movie library in this world."))}</li>" .
              "</ul>" .
            "</div>" .
            "<div class='span span--3'></div>" .
            "<div class='span span--3'>" .
              "<h3>{$i18n->t("Get help")}</h3>" .
              "<ul class='no-list'>" .
                "<li class='item-first item-last'>{$this->a("/help", "Help", $i18n->t("If you have questions click here to find our help articles."))}</li>" .
              "</ul>" .
            "</div>" .
          "</div>" .
        "</div>" .
        "<div id='footer-copyright'>" .
          "<div class='container'>" .
            "<i class='icon icon--cc'></i> <i class='icon icon--cc-zero'></i> {$i18n->t(
              "Database data is available under the {0}Creative Commons — CC0 1.0 Universal{1} license.",
              [ "<a href='http://creativecommons.org/publicdomain/zero/1.0/deed.{$i18n->languageCode}' rel='license'>", "</a>" ]
            )}<br>" .
            "{$i18n->t(
              "Additional terms may apply for third-party content, please refer to any license or copyright information that is additionaly stated."
            )}<br>" .
            $i18n->t(
              "By using this site, you agree to the {0} and {1}.",
              [ $this->a("/terms-of-use", "Terms of Use"), $this->a("/privacy-policy", "Privacy Policy") ],
              "<tt>{0}</tt> is <em>Terms of Use</em> and <tt>{1}</tt> is <em>Privacy Policy</em>."
            ) .
          "</div>" .
        "</div>" .
      "</footer>"
      // @todo Add aggregated scripts
    ;
    // Please note that a closing body or html tag is not necessary!
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
    return $this->a("/", "MovLib <small>the <em>free</em> movie library</small>", [
      "id" => "logo",
      "class" => "inline",
      "title" => $i18n->t("Go back to the home page.")
    ]);
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
      $i18n->t(" — ", null, "The em dash is used as separator character in the header title to denoate the source of the document (like in a quote the author), this should be translated to the equivalent character in your language. More information on this specific character can be found at <a href='//en.wikipedia.org/wiki/Dash#Em_dash'>Wikipedia</a>.") .
      "MovLib"
    ;
  }

  /**
   * Get the content wrapped in the outter content <tt>div</tt>.
   *
   * @return string
   *   The rendered content ready for print.
   */
  public function getRenderedContent() {
    return
      "<div id='content' class='{$this->getShortName()}-content' role='main'>" .
        "<div id='content__header'>" .
          "<div class='container'>" .
            "<h1 id='content__header__title'>{$this->title}</h1>" .
          "</div>" .
        "</div>" .
        $this->getAlerts() .
        $this->getContent() .
      "</div>"
    ;
  }

  /**
   * Get the full rendered view.
   *
   * @return string
   *   The rendered view ready for print.
   */
  public function getRenderedView() {
    return
      $this->getHead() .
      $this->getHeader() .
      $this->getStickyHeader() .
      $this->getBreadcrumb() .
      $this->getRenderedContent() .
      $this->getFooter()
    ;
  }

}
