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

use \MovLib\Presentation\Partial\Navigation;
use \MovLib\Presentation\Partial\Lists\GlueSeparated;

/**
 * @todo Description of Home
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Home extends \MovLib\Presentation\Tool\Page {

  /**
   * Whether the client has verified via PKCS #12 certificate or not.
   *
   * @var boolean
   */
  protected $sslClientVerified = false;

  /**
   * Instantiate new tools homepage.
   */
  public function __construct() {
    $this->init("Tools");
    if (!empty($_SERVER["SSL_CLIENT_VERIFY"])) {
      $this->sslClientVerified = $_SERVER["SSL_CLIENT_VERIFY"] == "SUCCESS";
    }
  }

  /**
   * @inheritdoc
   */
  protected function getContent() {
    global $kernel, $i18n;

    $tools = new Navigation("Tools", [
      [ "ApiGen", "public/doc/", "{$i18n->t("Have a look at the source code documentation.")} {$i18n->t("Generated once a day.")}", false ],
      [ "PHPInfo", "phpinfo", $i18n->t("Have a look at the current PHP configuration, extensions, etc."), false ],
      [ "phpMyAdmin", "phpmyadmin/", $i18n->t("Easily manage the database via the phpMyAdmin web interface."), true ],
      [ "Coverage", "public/coverage/", "{$i18n->t("Have a look at the unit test code coverage reports.")} {$i18n->t("Generated once a day.")}", false ],
      // @todo Either our tests are broken or VisualPHPUnit is broken ... impossible to get this working.
      //[ "VisualPHPUnit", $i18n->t("Run PHPUnit tests via the VisualPHPUnit web interface."), true ],
    ]);
    $tools->callback = function ($tool) {
      global $kernel;
      $route = "/{$tool[1]}";
      $label = [ "info", "open" ];
      if ($tool[3] === true) {
        $route = "//{$kernel->domainSecureTools}{$route}";
        $label = $this->sslClientVerified === true ? [ "success", "verified" ] : [ "danger", "not verified" ];
      }
      return [ $route, "<span class='label label-{$label[0]} pull-right'>{$label[1]}</span><h4>{$tool[0]}</h4><p>{$tool[2]}</p>", [
        "class" => "list-group-item"
      ]];
    };

    $devs = new GlueSeparated(glob("{$kernel->documentRoot}/public/coverage/devs/*", GLOB_ONLYDIR));
    $devs->closure = function ($listitem) {
      global $kernel;
      $route = str_replace($kernel->documentRoot, "", $listitem);
      $text  = basename($listitem);
      return "<a href='{$route}'>{$text}</a>";
    };

    return "<div class='container'><div class='list-group'>{$tools}</div><small>{$i18n->t("Developer specific coverage reports:")} {$devs}</small></div>";
  }

}
