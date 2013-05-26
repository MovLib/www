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

use \MovLib\Exception\MailException;
use \MovLib\Utility\String;
use \Stackable;

/**
 * Send asynchronous mail.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class AsyncSendMail extends Stackable {

  /**
   * Receiver, or receivers of the mail, must comply with RFC 2822.
   *
   * @var string
   */
  private $to;

  /**
   * Subject of the mail to be sent, must comply with RFC 2047.
   *
   * @var string
   */
  private $subject;

  /**
   * Message to be sent.
   *
   * @var string
   */
  private $message;

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
    $this->to = $to;
    $this->subject = $subject;
    $this->message = $message;
//    $this
//      ->setTo($to)
//      ->setSubject($subject)
//      ->setMessage($message)
//    ;
  }

  /**
   * Get additional email headers.
   *
   * @return string
   */
  private function getHeaders() {
    $headers = [
      "MIME-Version: 1.0",
      "Content-Type: text/html; charset=utf-8",
      "From: MovLib, the free movie library. <noreply@movlib.org>",
    ];
    return implode("\r\n", $headers);
  }

  /**
   * Send the mail.
   *
   * @return $this
   */
  public function run() {
    for ($i = 0; $i < 1000000; ++$i) {
      file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . "mail.log", "AsyncSendMail::run({$i})" . PHP_EOL, FILE_APPEND);
    }
//    mail($this->to, $this->subject, $this->message, $this->getHeaders());
  }

  /**
   * Set the message; this method will format the message according to our needs.
   *
   * @param string $message
   *   Message to be sent.
   * @return $this
   */
  private function setMessage($message) {
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
  private function setSubject($subject) {
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
  private function setTo($to) {
    if (is_array($to) === true) {
      $receiverCount = count($to);
      for ($i = 0; $i < $receiverCount; ++$i) {
        if (empty($to[$i]) === true || filter_var($to[$i], FILTER_VALIDATE_EMAIL) === false) {
          throw new MailException("The supplied email address '{$to[$i]}' is not valid.");
        }
      }
      $to = implode(",", $to);
    }
    elseif (empty($to) === true || filter_var($to, FILTER_VALIDATE_EMAIL) === false) {
      throw new MailException("The supplied email address '{$to}' is not valid.");
    }
    $this->to = $to;
    return $this;
  }

}
