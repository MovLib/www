#!/usr/bin/env php
<?php

/*!
 *  This file is part of {@link https://github.com/MovLib MovLib}.
 *
 *  Copyright © 2013-present {@link http://movlib.org/ MovLib}.
 *
 *  MovLib is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public
 *  License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later
 *  version.
 *
 *  MovLib is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 *  of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License along with MovLib.
 *  If not, see {@link http://www.gnu.org/licenses/ gnu.org/licenses}.
 */

/**
 * Cron jobs for things that should be done on a regular basis.
 *
 * Add the following line to your crontab: <pre>@daily php /var/www/bin/cron.php</pre>
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */

$mysqli = new \MySQLi();
if ($mysqli->real_connect() === false) {
  exit("{$mysqli->error} ({$mysqli->errno})\n");
}

// User activation link garbage collection
if ($mysqli->query("DELETE FROM `tmp` WHERE COLUMN_EXISTS(`dyn_data`, 'time') = 1 AND DATEDIFF(NOW(), COLUMN_GET(`dyn_data`, 'time' AS DATE)) > 1") === false) {
  exit("{$mysqli->error} ({$mysqli->errno})\n");
}
