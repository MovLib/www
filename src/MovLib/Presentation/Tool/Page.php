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
namespace MovLib\Presentation\Tool;

use \MovLib\Tool\Configuration;

/**
 * Reference implementation for tools pages.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Page extends \MovLib\Presentation\Page {

  /**
   * @inheritdoc
   */
  protected function init($title) {
    global $kernel;
    parent::init($title);
    $kernel = new Configuration();
    return $this;
  }

  /**
   * @inheritdoc
   */
  protected function getFooter() {
    global $kernel, $i18n;
    $year  = date("Y");
    return
      "<footer id='footer'><div class='container'><p>" .
        "© {$year} {$kernel->siteName}™ " .
        "<a href='mailto:{$kernel->emailWebmaster}'>{$i18n->t("Contact")}</a> " .
        "<a href='//{$kernel->domainStatic}/asset/ssl/ca.crt'>{$i18n->t("CA Certificate")}</a> " .
        "<a target='_blank' href='https://github.com/MovLib'>GitHub {$i18n->t("Porject")}</a>" .
      "</p></div></footer>"
    ;
  }

  /**
   * @inheritdoc
   */
  protected function getHeader() {
    global $kernel;
    return
      "<header id='header'><div class='container'><a href='/' id='header__logo'>" .
        "<img alt='' height='42' src='//{$kernel->domainStatic}/asset/img/logo/tools-vector.svg' width='42'> {$kernel->siteName}" .
      "</a></div></header>"
    ;
  }

}
