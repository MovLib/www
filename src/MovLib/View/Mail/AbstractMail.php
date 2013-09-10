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
namespace MovLib\View\Mail;

/**
 * Represents the base for a single mail that can be stacked and/or sent with the delayed mailer system.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractMail {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The recipient of this mail.
   *
   * @var string
   */
  public $recipient;

  /**
   * The mail's translated subject.
   *
   * @var string
   */
  public $subject;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Instantiate new mail.
   *
   * @param string $recipient
   *   The recipient's email address, must comply with RFC 2822. If the string contains a comma an exception will be
   *   thrown.
   * @param string $subject
   *   The (translated) subject of this mail. Must comply with RFC 2047.
   */
  public function __construct($recipient, $subject) {
    if (strpos($recipient, ",") !== false) {
      throw new MailerException("The recipient cannot contain a comma.");
    }
    $this->recipient = $recipient;
    $this->subject = $subject;
  }

  /**
   * Get the translated HTML body of the mail.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   Global i18n model instance.
   * @return string
   *   The translated HTML body of the mail.
   */
  protected abstract function getHtmlBody();

  /**
   * Set the HTML body of the mail.
   *
   * Wraps the given HTML into our mailing template.
   *
   * @link http://www.emailonacid.com/blog/details/C13/doctype_-_the_black_sheep_of_html_email_design
   * @todo We should test if the usage of single quotes in the HTML is a problem for email clients.
   * @global \MovLib\Model\I18nModel $i18n
   * @return string
   *   The translated HTML body of the mail.
   */
  public function getHtml() {
    global $i18n;
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
   * Set the translated plain text body of the mail.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @return string
   *   The translated plain text body of the mail.
   */
  protected abstract function getPlainBody();

  /**
   * Set the plain text body of the mail.
   *
   * Appends the MovLib signature to the mail.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @return string
   *   The translated plain text body of the mail.
   */
  public function getPlain() {
    global $i18n;
    return "{$this->getPlainBody()}\n\n--\n{$i18n->t("MovLib, the free movie library.")}\n";
  }

}
