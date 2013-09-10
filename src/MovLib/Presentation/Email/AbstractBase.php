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
namespace MovLib\Presentation\Email;

use \MovLib\Exception\MailerException;

/**
 * Abstract base reference implementation for emails. All email templates have to extend this class in order to work
 * with our mailing system.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractBase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The email's recipient.
   *
   * @var string
   */
  public $recipient;

  /**
   * The email's subject.
   *
   * @var string
   */
  public $subject;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Instantiate new email.
   *
   * @param string $recipient
   *   The email's recipient email address, must comply at least with RFC 2822. If the string contains a comma an
   *   exception will be thrown.
   * @param string $subject
   *   The email's subject, must comply with RFC 2047.
   * @throws \MovLib\Exception\MailerException
   */
  public function __construct($recipient, $subject) {
    if (strpos($recipient, ",") !== false) {
      throw new MailerException("An email recipient cannot contain a comma.");
    }
    $this->recipient = $recipient;
    $this->subject = $subject;
  }

  /**
   * Get the translated HTML body of the email.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return string
   *   The translated HTML body of the email.
   */
  abstract protected function getHtmlBody();

  /**
   * Get the translated HTML body of the email wrapped with the mail template.
   *
   * @link http://www.emailonacid.com/blog/details/C13/doctype_-_the_black_sheep_of_html_email_design
   * @todo We should test if the usage of single quotes in the HTML is a problem for email clients.
   * @return string
   *   The translated HTML body of the email wrapped with the email template.
   */
  public function getHtml() {
    return
      "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>" .
      "<html>" .
        "<head>" .
          "<meta http-equiv='Content-Type' content='text/html;charset=utf-8'/>" .
          "<title>{$this->subject}</title>" .
        "</head>" .
        "<body style='font:\"open sans\",arial,sans-serif'>{$this->getHtmlBody()}</body>" .
      "</html>"
    ;
  }

  /**
   * Get the translated plain text body of the email.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return string
   *   The translated plain text body of the email.
   */
  protected abstract function getPlainBody();

  /**
   * Get the translated plain text body of the email wrapped with the email template.
   *
   * Appends the MovLib signature to the mail.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return string
   *   The translated plain text body of the email wrapped with the email template
   */
  public function getPlain() {
    global $i18n;
    return "{$this->getPlainBody()}\n\n--\n{$i18n->t("{0}, the free movie library.", [ $i18n->t("MovLib") ])}\n";
  }

}
