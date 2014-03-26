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
class Page extends \MovLib\Presentation\AbstractPresenter {

  /**
   * @inheritdoc
   */
  protected function getFooter() {
    $year = date("Y");
    return
      "<footer id='f' role='contentinfo'><div class='c'><p>" .
        "© {$year} {$this->config->siteName}™ " .
        "<a href='mailto:{$kernel->emailWebmaster}'>{$this->intl->t("Contact")}</a> " .
        "<a href='//{$kernel->domainStatic}/asset/ssl/ca.crt'>{$this->intl->t("CA Certificate")}</a> " .
        "<a target='_blank' href='https://github.com/MovLib'>GitHub {$this->intl->t("Project")}</a>" .
      "</p></div></footer>"
    ;
  }

  /**
   * @inheritdoc
   */
  protected function getHeader() {
    $l = $this->getURL("asset://img/logo/tools-vector.svg");
    return
      "<header id='header' role='banner'><div class='c'><div class='r'>" .
        "<h1 class='s s3'>{$this->a("/", "<img height='42' src='{$l}' width='42'> {$this->config->siteName}", [ "id" => "logo" ])}</h1>" .
        "<div class='s s9'><h2><a href='//{$kernel->domainDefault}/'>{$this->config->siteName}</a></h2></div>" .
      "</div></div></header>"
    ;
  }

}
