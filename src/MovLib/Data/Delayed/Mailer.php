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
namespace MovLib\Data\Delayed;

use \MovLib\Exception\MailerException;
use \MovLib\Presentation\Email\AbstractEmail;

/**
 * Delayed mailer system.
 *
 * @link https://github.com/PHPMailer/PHPMailer
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Mailer {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Array used to collect all emails that should be sent.
   *
   * @var array
   */
  private static $emails = [];


  // ------------------------------------------------------------------------------------------------------------------- Public Methods


  /**
   * Send the given mail.
   *
   * @todo Digitally sign with DKIM!
   * @global \MovLib\Data\I18n $i18n
   *   Global i18n instance.
   * @param \MovLib\Presentation\Email\AbstractEmail $mail
   *   The email to send.
   * @return this
   * @throws MailerException
   */
  public function send(AbstractEmail $mail) {
    global $i18n;
    $messageId = uniqid("movlib");
    mail(
      $mail->recipient,
      mb_encode_mimeheader($mail->subject),
      implode("\n", [
        "--{$messageId}",
        "Content-Type: text/plain; charset=utf-8",
        "Content-Transfer-Encoding: BASE64",
        "",
        base64_encode($mail->getPlain()),
        "",
        "--{$messageId}",
        "Content-Type: text/html; charset=utf-8",
        "Content-Transfer-Encoding: BASE64",
        "",
        base64_encode($mail->getHtml()),
        "",
        "--{$messageId}--",
      ]),
      implode("\n", [
        "Content-Type: multipart/alternative;",
        "\tboundary=\"{$messageId}\"",
        "From: \"" . mb_encode_mimeheader($i18n->t($GLOBALS["movlib"]["default_from_name"])) . "\" <{$GLOBALS["movlib"]["default_from"]}>",
        "Message-ID: <{$messageId}@{$GLOBALS["movlib"]["default_domain"]}>",
        "MIME-Version: 1.0",
      ]),
      "-f {$GLOBALS["movlib"]["default_from"]}"
    );
    return $this;
  }


  // ------------------------------------------------------------------------------------------------------------------- Public Static Methods


  /**
   * Sends all emails in the stack.
   */
  public static function run() {
    $mailer = new Mailer();
    $c = count(self::$emails);
    for ($i = 0; $i < $c; ++$i) {
      $mailer->send(self::$emails[$i]);
    }
  }

  /**
   * Add email to stack.
   *
   * @param \MovLib\Presentation\Email\AbstractEmail $email
   *   The email that should be sent.
   */
  public static function stack($email) {
    delayed_register(__CLASS__, 0);
    self::$emails[] = $email;
  }

}
