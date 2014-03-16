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

/**
 * Profile routes
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */

/* @var $this \MovLib\Tool\Console\Command\Production\NginxRoutes */
$routes = [
  "/profile" => [ "presenter" => "Show" ],
  "/profile/account-settings" => [],
  "/profile/collection" => [],
  "/profile/danger-zone" => [],
  "/profile/email-settings" => [],
  "/profile/join" => [ "cache" => true ],
  "/profile/messages" => [],
  "/profile/notification-settings" => [],
  "/profile/lists" => [],
  "/profile/password-settings" => [],
  "/profile/reset-password" => [ "cache" => true ],
  "/profile/sign-in" => [ "cache" => true ],
  "/profile/sign-out" => [],
  "/profile/wantlist" => [],
  "/profile/watchlist" => [],
];

foreach ($routes as $route => $options):
  $presenter = null;
  if (isset($options["presenter"])) {
    $presenter = $options["presenter"];
  }
  else {
    foreach (explode("-", str_replace("/profile/", "", $route)) as $k => $v) {
      $presenter .= ucfirst($v);
    }
  }
  $cache = null;
  if (!isset($options["cache"])) {
    $cache = false;
  }
?>

location = <?= $this->r($route) ?> {
  <?= $this->set($presenter) ?>
  <?= $this->cache($cache) ?>
}

<?php
endforeach;
