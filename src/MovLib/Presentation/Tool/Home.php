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
   *
   */
  public function __construct() {
    $kernel->stylesheets[] = "tool";
    $this->initPage("Tools");
    $this->initBreadcrumb();
    unset($this->breadcrumb->menuitems[1]);
    if (!empty($_SERVER["SSL_CLIENT_VERIFY"])) {
      $this->sslClientVerified = $_SERVER["SSL_CLIENT_VERIFY"] == "SUCCESS";
    }
  }

  /**
   * @inheritdoc
   */
  protected function getContent() {
    $tools = new Navigation("Tools", [
      [ "ApiGen", "public/doc/", "{$this->intl->t("Have a look at the source code documentation.")} {$this->intl->t("Generated once a day.")}", false ],
      [ "PHPInfo", "phpinfo", $this->intl->t("Have a look at the current PHP configuration, extensions, etc."), false ],
      [ "phpMyAdmin", "phpmyadmin/", $this->intl->t("Easily manage the database via the phpMyAdmin web interface."), true ],
      [ "Coverage", "public/coverage/", "{$this->intl->t("Have a look at the unit test code coverage reports.")} {$this->intl->t("Generated once a day.")}", false ],
      // @todo Either our tests are broken or VisualPHPUnit is broken ... impossible to get this working.
      //[ "VisualPHPUnit", $this->intl->t("Run PHPUnit tests via the VisualPHPUnit web interface."), true ],
    ], [ "class" => "list-group" ]);
    $tools->callback = function ($tool) {
      $route = "/{$tool[1]}";
      $label = [ "info", "open" ];
      if ($tool[3] === true) {
        $route = "//{$kernel->domainSecureTools}{$route}";
        $label = $this->sslClientVerified === true ? [ "success", "verified" ] : [ "danger", "not verified" ];
      }
      return [ $route, "<span class='label label-{$label[0]} fr'>{$label[1]}</span><h4 class='title'>{$tool[0]}</h4><p>{$tool[2]}</p>", [
        "class" => "list-group-item"
      ]];
    };

    // Generate single developer report links if there are any.
    $devs    = null;
    $reports = glob("{$kernel->documentRoot}/public/coverage/devs/*", GLOB_ONLYDIR);
    foreach ($reports as $directory) {
      $route = str_replace("{$kernel->documentRoot}/public", "", $directory);
      $text  = basename($route);
      if ($devs) {
        $devs .= ", ";
      }
      $devs .= "<a href='{$route}'>{$text}</a>";
    }
    if ($devs) {
      $devs = "<small>{$this->intl->t("Developer specific coverage reports:")}</small> {$devs}</small>";
    }

    return "<div class='c'>{$tools}{$devs}</div>";
  }

}
