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
namespace MovLib\Presenter;

use \MovLib\Presenter\AbstractPresenter;
use \MovLib\Utility\HTTP;

/**
 * The no subdomain presenter is called from nginx if a URI is accessed without any subdomain.
 *
 * All MovLib content is available via subdomains. They mainly identify the display language, some subdomains are used
 * for special pages (e.g. the API or the localization site). This presenter simply redirects the user to a subdomain
 * based on the user's preferred language set in his account, the HTTP accept language header, or if none of these
 * values are present to the default language's subdomain.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class NoSubdomainPresenter extends AbstractPresenter {

  /**
   * Redirect the user to the best matching subdomain that might contain the requested content.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance.
   */
  public function __construct() {
    global $i18n;
    HTTP::redirect($_SERVER["REQUEST_URI"], 302, "$i18n->languageCode.{$_SERVER["SERVER_NAME"]}");
  }

  /**
   * No need for a breadcrumb.
   */
  public function getBreadcrumb() {}

}
