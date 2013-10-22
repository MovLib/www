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
namespace MovLib\Presentation\Tool;

use \MovLib\Tool\Configuration;

/**
 * Reference implementation for tools pages.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Page extends \MovLib\Presentation\Page {

  /**
   * @inheritdoc
   */
  protected function init($title) {
    global $config;
    parent::init($title);
    $config = new Configuration();
    return $this;
  }

  /**
   * @inheritdoc
   */
  protected function getFooter() {
    global $config;
    $year  = date("Y");
    $links = null;
    foreach ([
      "Contact"        => "mailto:{$config->emailWebmaster}",
      "CA Certificate" => "//{$config->domainStatic}/asset/ssl/ca.crt",
      "GitHub Project" => "https://github.com/MovLib/tools",
    ] as $text => $href) {
      $links .= " <a target='_blank' href='{$href}'>{$text}</a>";
    }
    return
      "<footer id='footer'><div class='container'><p>© {$year} MovLib™{$links}</p></div></footer>"
    ;
  }

  /**
   * @inheritdoc
   */
  protected function getHeader() {
    global $config, $i18n;
    return
      "<header id='header'><div class='container'><a href='/' id='header__logo'>" .
        "<img alt='' height='42' src='{$_SERVER["SCHEME"]}://{$config->domainStatic}/asset/img/logo/tools-vector.svg' width='42'> {$config->siteName}" .
      "</a></div></header>"
    ;
  }

}