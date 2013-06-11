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

use \MovLib\Entity\User;

/**
 * Delayed mail system.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class DelayedMailer {


  // ------------------------------------------------------------------------------------------------------------------- Static Properties


  /**
   * Array used to collect all mails that should be sent.
   *
   * @var array
   */
  private static $mails = [];


  // ------------------------------------------------------------------------------------------------------------------- Public Static Methods


  /**
   * Send the mails.
   *
   * @todo Send plain text and HTML mail with the MovLib logo as attachment.
   */
  public static function sendMails() {
    foreach (self::$mails as list($to, $subject, $message)) {
      mail($to, $subject, $message, "MIME-Version: 1.0\r\nContent-Type: text/html; charset=utf-8\r\nFrom: \"MovLib, the free movie library.\" <noreply@movlib.org>");
    }
  }

  /**
   * Send mail.
   *
   * @see \MovLib\Utility\DelayedMailer::stack()
   * @param string $to
   *   Receiver of the mail, must comply with RFC 2822.
   * @param string $subject
   *   Subject of the email to be sent, must comply with RFC 2047.
   * @param string $message
   *   Message to be sent.
   */
  public static function sendMail($to, $subject, $message) {
    self::stack($to, $subject, $message);
  }

  /**
   * Send password reset email.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n instance.
   * @param string $userEmail
   *   Email address of the user that requested the password reset.
   */
  public static function sendPasswordReset($userEmail) {
    global $i18n;
    /* @var $user \MovLib\Entity\User */
    $user = (new User())->__constructFromEmail($userEmail);
    self::stack([
      "to" => $user->getEmail(),
      "subject" => $i18n->t("Password reset request"),
      "message" => $i18n->t("Hello {0}!

You (or someone else) has requested to reset the password for your account.

You may now log in by clicking this link or copying and pasting it to your browser.

{1}

This link can only be used once to log in and will lead you to a page where you can set your password. If you have not requested this password reset simply ignore this email.

— {2}", [ $user->name , "https://{$_SERVER["SERVER_NAME"]}/", $i18n->t("MovLib, the free movie library.") ])
    ]);
  }

  /**
   * Validate the given email address.
   *
   * <b>IMPORTANT!</b> This method can not be used within the async mail thread, because it relies on the global i18n
   * instance which isn't available in any other thread. Validate email addresses as early as possible. Any address
   * passed to the async mailer should already be valid.
   *
   * @link http://api.drupal.org/api/drupal/core!modules!user!user.module/function/user_validate_name/8
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n instance.
   * @param string $email
   *   The email address to validate.
   * @return null|string
   *   <tt>NULL</tt> if the email address is valid. If invalid a translated error message describing the problem.
   */
  public static function validateEmail($email) {
    global $i18n;
    if (empty($email)) {
      return $i18n->t("You must enter a email address.");
    }
    if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
      return $i18n->t("The email address is not valid.");
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Private Static Methods


  /**
   * Add new mail message to send after the response was sent to the user to the stack.
   *
   * @global array $delayed
   *   The array to collect delayed class names and method names.
   * @param string $to
   *   Receiver of the mail, must comply with RFC 2822.
   * @param string $subject
   *   Subject of the email to be sent, must comply with RFC 2047.
   * @param string $message
   *   Message to be sent.
   */
  private static function stack($to, $subject, $message) {
    global $delayed;
    $delayed[__CLASS__] = "sendMails";
    self::$mails[] = [ $to, $subject, $message ];
  }

}
