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
use \MovLib\Utility\AsyncAbstractWorker;
use \MovLib\Exception\MailException;
use \MovLib\Exception\UserException;
use \Stackable;

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
   * Get additional email headers.
   *
   * @staticvar string $headers
   *   The additional headers will only be computed once.
   * @return string
   *   The additional headers for the email.
   */
  public function getHeaders() {
    static $headers = null;
    if ($headers === null) {
      $headers = implode("\r\n", [
        "MIME-Version: 1.0",
        "Content-Type: text/html; charset=utf-8",
        "From: \"MovLib, the free movie library.\" <noreply@movlib.org>",
      ]);
    }
    return $headers;
  }

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
    /* @var $instance AsyncMailer */
    $instance = self::getInstance();
    $instance->stack(new AsyncSendMail($to, $subject, $message));
    return $instance;
  }

  /**
   * Send password reset email.
   *
   * @param string $userEmail
   *   Email address of the user that requested the password reset.
   * @return $this
   * @throws \MovLib\Exception\MailException
   */
  public static function resetPassword($userEmail) {
    /* @var $instance AsyncMailer */
    $instance = self::getInstance();
    try {
      /* @var $user \MovLib\Entity\User */
      $user = (new User())->constructFromEmail($userEmail);
      $instance->stack(new AsyncMail(
        $user->getEmail(),
        __("Password reset request"),
        "<p>" . sprintf(__("Hello %s!"), $user->getName()) . "</p>" .
        "<p>" . __("You (or someone else)") . "</p>"
      ));
    } catch (UserException $e) {
      // @todo No need to do anything but logging.
    }
    return $instance;
  }

}

class AsyncMail extends Stackable {

  /**
   * Message to be sent.
   *
   * @var string
   */
  private $message;

  /**
   * Subject of the mail to be sent, must comply with RFC 2047.
   *
   * @var string
   */
  private $subject;

  /**
   * Receiver, or receivers of the mail, must comply with RFC 2822.
   *
   * @var string
   */
  private $to;

  /**
   * Send new mail.
   *
   * @param string $to
   *   Receiver of the mail, must comply with RFC 2822.
   * @param string $subject
   *   Subject of the mail to be sent, must comply with RFC 2047.
   * @param string $message
   *   Message to be sent.
   * @throws \MovLib\Exception\MailException
   */
  public function __construct($to, $subject, $message) {
    $this
      ->setTo($to)
      ->setSubject($subject)
      ->setMessage($message)
    ;
  }

  /**
   * Send the mail.
   */
  public function run() {
    mail($this->to, $this->subject, $this->message, $this->worker->getHeaders());
  }

  /**
   * Set the message; this method will format the message according to our needs.
   *
   * @param string $message
   *   Message to be sent.
   * @return $this
   * @throws \MovLib\Exception\MailException
   */
  protected function setMessage($message) {
    if (empty($message) === true) {
      throw new MailException("The email body can not be empty!");
    }
    $this->message = $message;
    return $this;
  }

  /**
   * Set the subject of the mail, must comply with RFC 2047.
   *
   * @param string $subject
   *   Subject of the mail to be sent, must comply with RFC 2047.
   * @return $this
   * @throws \MovLib\Exception\MailException
   */
  protected function setSubject($subject) {
    // Checking empty after calling checkPlain is important, because the subject might be empty after the check!
    $subject = String::checkPlain($subject);
    if (empty($subject) === true) {
      throw new MailException("The supplied subject is not valid.");
    }
    $this->subject = $subject;
    return $this;
  }

  /**
   * Set the receiver of the mail, must comply with RFC 2822.
   *
   * @param string $to
   *   Receiver of the mail, must comply with RFC 2822.
   * @return $this
   * @throws \MovLib\Exception\MailException
   */
  protected function setTo($to) {
    if (is_array($to) === true) {
      $receiverCount = count($to);
      for ($i = 0; $i < $receiverCount; ++$i) {
        $this->validateEmail($to[$i]);
      }
      $to = implode(",", $to);
    }
    else {
      $this->validateEmail($to);
    }
    $this->to = $to;
    return $this;
  }

  /**
   * Validate the given email address.
   *
   * @param string $email
   *   The email address to validate.
   * @return string
   *   The email address.
   * @throws MailException
   *   If the supplied email address is not set, empty or invalid.
   */
  protected function validateEmail($email) {
    if (empty($email) === true) {
      throw new MailException("Email address can not be empty!");
    }
    if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
      throw new MailException("The supplied email address is not valid!");
    }
    return $email;
  }

}
