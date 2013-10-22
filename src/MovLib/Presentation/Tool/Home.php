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

use \MovLib\Presentation\Partial\Navigation;

/**
 * Description of Home
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Home extends \MovLib\Presentation\Tool\Page {

  /**
   * Instantiate new tools homepage.
   */
  public function __construct() {
    $this->init("Tools");
  }

  /**
   * @inheritdoc
   */
  protected function getContent() {
    global $i18n;
    $tools = new Navigation("tools", "Tools", [
      [ "ApiGen", "{$i18n->t("Have a look at the source code documentation.")} {$i18n->t("Generated once a day.")}", false ],
      [ "PHPInfo", $i18n->t("Have a look at the current PHP configuration, extensions, etc."), false ],
      [ "phpMyAdmin", $i18n->t("Easily manage the database via the phpMyAdmin web interface."), true ],
      [ "Coverage", "{$i18n->t("Have a look at the unit test code coverage reporst.")} {$i18n->t("Generated once a day.")}", false ],
      // @todo Either our tests are broken or VisualPHPUnit is broken ... impossible to get this working.
      //[ "VisualPHPUnit", $i18n->t("Run PHPUnit tests via the VisualPHPUnit web interface."), true ],
    ]);
    $tools->closure = [ $this, "formatListItem" ];
    return "<div class='container'><div class='list-group'>{$tools}</div></div>";
  }

  /**
   * Format tool list item.
   *
   * @global \MovLib\Tool\Configuration $config
   * @param array $tool
   *   A single tool entry from the Tools Config tools array.
   * @return string
   *   The formatted tool list item.
   */
  public function formatListItem($tool) {
    global $config;
    $route = "/" . str_replace(" ", "-", mb_strtolower($tool[0]));
    $label = [ "info", "open" ];
    if ($tool[2] === true) {
      $route = "//{$config->domainSecureTools}{$route}";
      $label = $config->sslClientVerify === true ? [ "success", "verified" ] : [ "danger", "not verified" ];
    }
    return [ $route, "<span class='label label-{$label[0]} pull-right'>{$label[1]}</span><h4>{$tool[0]}</h4><p>{$tool[1]}</p>", [
      "class" => "list-group-item"
    ]];
  }

}
