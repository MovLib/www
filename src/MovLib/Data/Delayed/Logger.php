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
namespace MovLib\Data\Delayed;

use \Exception;
use \MovLib\Data\Delayed\Mailer;
use \MovLib\Presentation\Email\Email;

/**
 * Delayed log facility.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Logger {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * System might be unusable, action must be taken immediately (email is sent to all developers).
   *
   * @var string
   */
  const FATAL = "FATAL";

  /**
   * An error condition was encountered (email is sent to webmaster).
   *
   * @var string
   */
  const ERROR = "ERROR";

  /**
   * Normal but significant condition was encountered (no email is sent).
   *
   * @var string
   */
  const WARNING = "WARNING";

  /**
   * Debug or informational condition.
   *
   * @var string
   */
  const DEBUG = "DEBUG";

  /**
   * Written to special slow-log file.
   *
   * @var string
   */
  const SLOW = "SLOW";


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Array used to collect all log entries.
   *
   * @var array
   */
  private static $entries = [];


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Send all log entries to the system logger.
   */
  public static function run() {
    // Go through all stacked log entries.
    foreach (self::$entries as $priority => $entries) {
      $email = "";

      // Log all entries to the appropriate facility.
      $c = count($entries);
      for ($i = 0; $i < $c; ++$i) {
        $time = date("Y-m-d H:i:s e", $entries[$i]["time"]);
        if ($entries[$i]["entry"] instanceof Exception) {
          $entries[$i]["entry"] = "{$entries[$i]["entry"]->getMessage()} (code: {$entries[$i]["entry"]->getCode()})\n#### Stacktrace ####\n{$entries[$i]["entry"]->getTraceAsString()}";
        }
        $entry = "[{$time}] {$priority}: {$entries[$i]["entry"]}\n";
        error_log($entry, 3, ini_get("error_log"));
        // Slow log entries are also written to a seperate log file.
        if ($priority === self::SLOW) {
          error_log($entry, 3, $GLOBALS["movlib"]["slowlog"]);
        }
        $email .= $entry;
      }

      // Fatal log entries go to all developers.
      if ($priority === self::FATAL || $priority === self::ERROR) {
        $emailHtml = htmlspecialchars(print_r($email, true), ENT_QUOTES);
        if ($priority === self::FATAL) {
          $email = new Email(
            $GLOBALS["movlib"]["developer_mailinglist"],
            "IMPORTANT! A fatal error was just logged!",
            "<p>Hi devs!</p><p>A fatal log entry was just written, the system might be unusable, action must be taken immediately!</p><pre style='background:#eaeaea;padding:5px'>{$emailHtml}</pre>",
            "Hi devs!\n\nA fatal log entry was just written, the system might be unusable, action must be taken immediately!\n\n{$email}"
          );
        }
        // Error log entries only to the webmaster.
        elseif ($priority === self::ERROR) {
          $email = new Email(
            $GLOBALS["movlib"]["webmaster_mail"],
            "IMPORTANT! An error was just logged!",
            "<p>Hi Webmaster!</p><p>An error log entry was just written, the system might be in trouble, please review the attached entries:</p><pre style='background:#eaeaea;padding:5px'>{$emailHtml}</pre>",
            "An error log entry was just written, the system might be in trouble, please review the attached entries:\n\n{$email}"
          );
        }
        (new Mailer())->send($email);
      }
    }
  }

  /**
   * Stack log entry.
   *
   * @param string|\Exception $entry
   *   The log entry, either a string or an instance of PHP's <code>\Exception</code> class.
   * @param string $level [optional]
   *   The message's log level, defaults to <var>Logger::WARNING</var>.
   */
  public static function stack($entry, $priority = self::WARNING) {
    self::$entries[$priority][] = [ "time" => time(), "entry" => $entry ];
  }

}
