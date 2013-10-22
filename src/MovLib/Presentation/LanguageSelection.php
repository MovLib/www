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

use \Locale;
use \MovLib\Data\User\Full as User;
use \MovLib\Data\SystemLanguages;
use \MovLib\Exception\Client\RedirectTemporaryException;
use \MovLib\Presentation\Partial\Navigation;

/**
 * The global language selection page.
 *
 * The language selection page is displayed if a user accesses MovLib without any language code specific subdomain. We
 * don't know what language the user might prefer and we don't want to guess (like many others are doing it, e.g. Google
 * and they're doing a really awful job there). Freedom of joice is the motto. This page is different than most other
 * MovLib page's and therefor we extend the abstract page and not the reference implementation.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class LanguageSelection extends \MovLib\Presentation\AbstractPage {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Numeric array containing all supported languages.
   *
   * @var \MovLib\Presentation\Partial\Navigation
   */
  private $navigation;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new language selection presentation.
   *
   * @global \MovLib\Configuration $config
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\User\Session $session
   */
  public function __construct() {
    global $config, $i18n, $session;

    // If a signed in user is requesting this page we know where to send her or him.
    if ($session->isAuthenticated === true) {
      $user = new User(User::FROM_ID, $session->userId);
      throw new RedirectTemporaryException("{$_SERVER["SCHEME"]}://{$user->systemLanguageCode}.{$config->domainDefault}/");
    }

    // If not render the page.
    $this->init($i18n->t("Language Selection"));
    $this->stylesheets[]                   = "modules/language-selection.css";
    $this->navigation                      = new Navigation($this->id, $i18n->t("Available Languages"), new SystemLanguages());
    $this->navigation->attributes["class"] = "well well--large";
    $this->navigation->glue                = " / ";
    $this->navigation->closure             = [ $this, "formatSystemLanguage" ];
  }

  /**
   * Format a single system language.
   *
   * @global \MovLib\Configuration $config
   * @param \MovLib\Data\SystemLanguage $systemLanguage
   *   The system language to format.
   * @return array
   */
  public function formatSystemLanguage($systemLanguage) {
    global $config;
    return [
      "//{$systemLanguage->languageCode}.{$config->domainDefault}/",
      $systemLanguage->nameNative,
      [ "lang" => $systemLanguage->languageCode, "rel" => "prefetch", "tabindex" => $this->getTabindex() ],
    ];
  }

  /**
   * @inheritdoc
   */
  public function getPresentation() {
    global $config, $i18n;
    $html = parent::getPresentation();
    return
      "{$html}<div class='{$this->id}-content' id='content' role='main'><div class='container'>" .
        "<h1 class='clear-fix' id='logo-big'>" .
          "<img alt='MovLib {$i18n->t("logo")}' height='192' src='//{$config->domainStatic}/asset/img/logo/vector.svg' width='192'>" .
          "<span>MovLib <small>{$i18n->t("the {0}free{1} movie library.", [ "<em>", "</em>" ])}</small></span>" .
        "</h1>" .
        "<p>{$i18n->t("Please select your preferred language from the following list.")}</p>{$this->navigation}" .
      "</div></div>" .
      "<footer id='footer'><div class='container'><p>{$i18n->t(
        "Is your language missing from our list? Help us translate {0} to your language. More information can be found at {1}our translation portal{2}.",
        [ "MovLib", "<a href='//{$config->domainLocalize}/'>", "</a>" ]
      )}</p></div></footer>"
    ;
  }

}
