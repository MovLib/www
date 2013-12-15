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
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Tool\Kernel $kernel
   */
  protected function getFooter() {
    global $i18n, $kernel;
    $year = date("Y");
    return
      "<footer id='f' role='contentinfo'><div class='c'><p>" .
        "© {$year} {$kernel->siteName}™ " .
        "<a href='mailto:{$kernel->emailWebmaster}'>{$i18n->t("Contact")}</a> " .
        "<a href='//{$kernel->domainStatic}/asset/ssl/ca.crt'>{$i18n->t("CA Certificate")}</a> " .
        "<a target='_blank' href='https://github.com/MovLib'>GitHub {$i18n->t("Project")}</a>" .
      "</p></div></footer>"
    ;
  }

  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Tool\Kernel $kernel
   */
  protected function getHeader() {
    global $kernel;
    return
      "<header id='header' role='banner'><div class='c'><div class='r'>" .
        "<h1 class='s s3'>{$this->a("/", "<img height='42' src='{$kernel->getAssetURL("logo/tools-vector", "svg")}' width='42'> {$kernel->siteName}", [ "id" => "logo" ])}</h1>" .
        "<div class='s s9'><h2><a href='//{$kernel->domainDefault}/'>{$kernel->siteName}</a></h2></div>" .
      "</div></div></header>"
    ;
  }

}
