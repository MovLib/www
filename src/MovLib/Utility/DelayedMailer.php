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

use \MovLib\Exception\UserException;
use \MovLib\Model\UserModel;

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

  /**
   * Array used to collect all mailer methods that should be called before sending a mail
   *
   * @var array
   */
  private static $methods = [];


  // ------------------------------------------------------------------------------------------------------------------- Public Static Methods


  /**
   * Send the mails.
   *
   * @todo Send plain text and HTML mail with the MovLib logo as attachment.
   */
  public static function run() {
    global $i18n;
    foreach (self::$methods as list($method, $params)) {
      call_user_func_array("self::{$method}", $params);
    }
    $headers = implode("\r\n", [
      "MIME-Version: 1.0",
      "Content-Type: text/plain; charset=utf-8"
    ]);
    $signature = "\n\n— {$i18n->t("MovLib, the free movie library.")}";
    foreach (self::$mails as list($to, $subject, $message)) {
      mail($to, $subject, $message . $signature, $headers);
    }
  }

  /**
   * Add a mail to the stack.
   *
   * @param string $to
   *   Receiver of the mail, must comply with RFC 2822.
   * @param string $subject
   *   Subject of the mail to be sent, must comply with RFC 2047.
   * @param string $message
   *   Message to be sent.
   */
  public static function stackMail($to, $subject, $message) {
    delayed_register(__CLASS__, 0);
    self::$mails[] = [ $to, $subject, $message ];
  }

  /**
   * Add a mail method to the stack that should be called before sending the mail.
   *
   * @param string $method
   *   The name of the method within the delayed mailer class that should be called before sending the mail.
   * @param array $params
   *   Array containing the parameters that should be passed to the method.
   */
  public static function stackMethod($method, $params = []) {
    delayed_register(__CLASS__, 0);
    self::$methods[] = [ $method, $params ];
  }

  /**
   * Set the activation mail.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   Global i18n model instance.
   * @param string $hash
   *   The activation hash of the user.
   * @param string $name
   *   The valid name of the user.
   * @param string $mail
   *   The valid mail of the user.
   */
  public static function stackActivationMail($hash, $name, $mail) {
    global $i18n;
    self::$mails[] = [
      $mail,
      $i18n->t("Welcome to MovLib!"),
      $i18n->t(
"Hi {0}!

Thank you for registering at MovLib. You may now log in by clicking this link or copying and pasting it to your browser:

{1}

This link can only be used once to log in and will lead you to a page where you can set your password.

After setting your password, you will be able to log in at MovLib in the future using:

Email address:  {2}
Password:       Your password",
        [ $name, $i18n->r("/user/register={0}", [ $hash ]) , $mail ]
      )
    ];
  }

  /**
   * Set the mail that should be sent if somebody requests a new account for a mail that is already registered.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance.
   * @param string $mail
   *   The already registered valid mail.
   */
  public static function stackActivationMailExists($mail) {
    global $i18n;
    try {
      $user = new UserModel("mail", $mail);
      DelayedMailer::stackMail(
        $mail,
        $i18n->t("Forgot your password?"),
        $i18n->t(
"Hi {0}!

You (or someone else) requested a new account with this email address. If you forgot your password visit the “reset password” page:

{1}

If it wasn’t you who requested a new account ignore this message.",
          [ $user->name, $i18n->r("/user/reset-password") ]
        )
      );
    } catch (UserException $e) {
      DelayedLogger::logException($e);
    }
  }

  /**
   * Set the mail that should be sent if somebody requests a new password.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance.
   * @param string $hash
   *   The reset hash.
   * @param string $mail
   *   The valid mail.
   */
  public static function stackPasswordReset($hash, $mail) {
    global $i18n;
    try {
      $user = new UserModel("mail", $mail);
      DelayedMailer::stackMail(
        $mail,
        $i18n->t("Password reset request"),
        $i18n->t(
"Hi {0}!

You (or someone else) requested a password reset for your account. You may now reset your password by clicking this link or copying and pasting it to your browser:

{1}

This link can only be used once to log in and will lead you to a page where you can set your password.

If it wasn’t you who requested a new password ignore this message.",
          [ $user->name, $i18n->r("/user/reset-password={0}", [ $hash ]) ]
        )
      );
    } catch (UserException $e) {
      DelayedLogger::logException($e);
    }
  }

  /**
   * Validate the given mail.
   *
   * @param string $mail
   *   The mail to validate.
   * @return string|false
   *   The mail if it is valid, otherwise <tt>FALSE</tt>.
   */
  public static function validate($mail) {
    if (($mail = filter_var($mail, FILTER_VALIDATE_EMAIL)) === false || empty($mail)) {
      return false;
    }
    return $mail;
  }

  /**
   * Validate the mail from the given input.
   *
   * @param string $name
   *   The value of the name attribute of the input element. Defaults to <em>mail</em>.
   * @param int $type
   *   One of the PHP <var>INPUT_*</var> constants. Defaults to <var>INPUT_POST</var>.
   * @return string|false
   *   The mail if it is valid, otherwise <tt>FALSE</tt>.
   */
  public static function validateInput($name = "mail", $type = INPUT_POST) {
    if (($mail = filter_input($type, $name, FILTER_VALIDATE_EMAIL)) === false || empty($mail)) {
      return false;
    }
    return $mail;
  }

}
