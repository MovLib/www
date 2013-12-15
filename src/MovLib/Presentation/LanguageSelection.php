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

use \MovLib\Data\User\Full as UserFull;
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
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class LanguageSelection extends \MovLib\Presentation\Page {


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new language selection presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @global \MovLib\Data\User\Session $session
   */
  public function __construct() {
    global $i18n, $kernel, $session;

    // If a signed in user is requesting this page we know where to send her or him.
    if ($session->isAuthenticated === true) {
      $user = new UserFull(UserFull::FROM_ID, $session->userId);
      throw new RedirectTemporaryException("{$kernel->scheme}://{$user->systemLanguageCode}.{$kernel->domainDefault}/");
    }

    // If not render the page.
    $this->init($i18n->t("Language Selection"));
    $kernel->stylesheets[] = "language-selection";
  }

  /**
   * Format a single system language.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @param string $languageCode
   *   The language code of the system language.
   * @return array
   */
  public function formatSystemLanguage($languageCode) {
    global $i18n, $kernel;
    $attributes = [ "rel" => "prerender" ];
    if ($languageCode != $i18n->languageCode) {
      $attributes["lang"] = $languageCode;
    }
    return [ "//{$languageCode}.{$kernel->domainDefault}/", \Locale::getDisplayLanguage($languageCode, $languageCode), $attributes ];
  }

  /**
   * @inheritdoc
   */
  protected function getFooter() {
    global $i18n, $kernel;
    return
      "<footer id='f'><div class='c'><div class='r'><p>{$i18n->t(
        "Is your language missing from our list? Help us translate {0} to your language. More information can be found at {1}our translation portal{2}.",
        [ $kernel->siteName, "<a href='//{$kernel->domainLocalize}/'>", "</a>" ]
      )}</p></div></div></footer>"
    ;
  }

  /**
   * @inheritdoc
   */
  protected function getHeader() {
    return "";
  }

  /**
   * @inheritdoc
   */
  protected function getMainContent() {
    global $i18n, $kernel;

    // Build the navigation.
    $navigation           = new Navigation($i18n->t("Available Languages"), array_keys($kernel->systemLanguages), [ "class" => "well well-lg" ]);
    $navigation->glue     = " / ";
    $navigation->callback = [ $this, "formatSystemLanguage" ];

    return
      "<main class='{$this->id}-content' id='m' role='main'><div class='c'>" .
        "<h1 class='cf'>" .
          "<img height='192' src='{$kernel->getAssetURL("logo/vector", "svg")}' width='192'>" .
          "<span>{$kernel->siteNameAndSloganHTML}</span>" .
        "</h1>" .
        "<p>{$i18n->t("Please select your preferred language from the following list.")}</p>{$navigation}" .
      "</div></main>"
    ;
  }

}
