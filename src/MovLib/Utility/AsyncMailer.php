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
namespace MovLib\Utility;

use \MovLib\Utility\AsyncAbstractWorker;
use \MovLib\Utility\AsyncSendMail;

/**
 * Asynchronous mailing system.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class AsyncMailer extends AsyncAbstractWorker {

  /**
   * Send email.
   *
   * @param string $to
   *   Receiver of the mail, must comply with RFC 2822.
   * @param string $subject
   *   Subject of the email to be sent, must comply with RFC 2047.
   * @param string $message
   *   Message to be sent.
   * @return $this
   * @throws \MovLib\Exception\MailException
   */
  public static function sendMail($to, $subject, $message) {
    $instance = self::getInstance();
    $instance->stack(new AsyncSendMail($to, $subject, $message));
    return $instance;
  }

  /**
   * Send password reset email.
   *
   * This method will generate everything that is necessary for reseting a password.
   *
   * @param string $userEmail
   *   Email address of the user that requested the password reset.
   * @return $this
   * @throws \MovLib\Exception\MailException
   */
  public static function resetPassword($userEmail) {
    $instance = self::getInstance();
    try {
      $user = (new User())->constructUserFromEmail($userEmail);
      $instance->stack(new AsyncSendMail(
        $userEmail,
        __(""),
        "<p>" . __("You (or someone else)") . "</p>"
      ));
    } catch (UserException $e) {
      // @todo No need to do anything but logging.
    }
    return $instance;
  }

}
