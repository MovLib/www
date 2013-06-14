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
 * Simple script to start our memcached servers.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */

$daemonname = "memcached";
$daemon = "/usr/local/bin/{$daemonname}";
$run = "/run/{$daemonname}/";
$user = "www-data";

if (!file_exists($daemon) || !is_executable($daemon)) {
  exit("{$daemon} does't exist or is not executable, exiting!\n");
}

$socket = function ($name) {
  global $run;
  return "{$run}{$name}.sock";
};

$pid = function ($name) {
  global $run;
  return "{$run}{$name}.pid";
};

$servers = [
  "session" => "-m 64 -t 1",
  "server1" => "-m 64 -t 1",
];

if ($argc === 1) {
  exit("Usage /etc/init.d/{$daemonname} (start|stop|restart)\n");
}

if (!is_dir($run) || !is_readable($run)) {
  system("mkdir {$run} && chown {$user}:{$user} {$run}");
}

foreach ($servers as $server => $params) {
  $servers[$server] = "{$params} -d -r -u {$user} -s {$socket($server)} -P {$pid($server)}";
}

switch ($argv[1]) {
  case "start":
    foreach ($servers as $server => $params) {
      echo "Starting {$daemonname}: ";
      system("{$daemon} {$params}");
      echo "{$server}.\n";
    }
    break;

  case "stop":
    foreach ($servers as $server => $params) {
      echo "Stopping {$daemonname}: ";
      system("start-stop-daemon --stop --quiet --oknodo --pidfile {$pid($server)} --exec {$daemon}");
      @unlink($socket($server));
      @unlink($pid($server));
      echo "{$server}.\n";
    }
    break;

  case "restart":
    foreach ($servers as $server => $params) {
      echo "Restarting {$daemonname}: ";
      system("start-stop-daemon --stop --quiet --oknodo --pidfile {$pid($server)} --exec {$daemon}");
      @unlink($socket($server));
      @unlink($pid($server));
      sleep(1);
      system("{$daemon} {$params}");
      echo "{$server}.\n";
    }
    break;
}
