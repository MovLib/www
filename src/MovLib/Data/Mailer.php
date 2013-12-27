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
namespace MovLib\Data;

use \MovLib\Presentation\Email\AbstractEmail;

/**
 * Mailer system.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Mailer {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The email we are currently sending.
   *
   * @var \MovLib\Presentation\Email\AbstractEmail
   */
  protected $email;

  /**
   * The current email's unique message ID.
   *
   * @var string
   */
  protected $messageID;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new mailer system.
   *
   * @param array $emails [optional]
   *   Numeric array containing concrete {@see AbstractEmail} instances which will be sent automatically.
   */
  public function __construct(array $emails = null) {
    if ($emails) {
      foreach ($emails as $email) {
        $this->send($email);
      }
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get the base64 encoded HTML message.
   *
   * @link http://www.emailonacid.com/blog/details/C13/doctype_-_the_black_sheep_of_html_email_design
   * @todo We should test if the usage of single quotes in the HTML is a problem for email clients.
   * @return string
   *   The base64 encoded HTML message.
   */
  protected function getBase64EncodedHTML() {
    return base64_encode(
      "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>" .
      "<html>" .
        "<head>" .
          "<meta http-equiv='Content-Type' content='text/html;charset=utf-8'/>" .
          "<title>{$this->email->subject}</title>" .
        "</head>" .
        "<body style='font:\"open sans\",arial,sans-serif'>{$this->email->getHTML()}</body>" .
      "</html>"
    );
  }

  /**
   * Get the base64 encoded plain text message.
   *
   * @global \MovLib\Kernel $kernel
   * @return string
   *   The base64 encoded plain text message.
   */
  protected function getBase64EncodedPlainText() {
    global $kernel;
    return base64_encode($this->wordwrap(
      "{{$this->email->getPlainText()}\n\n--\n{$kernel->siteName}\n"
    ));
  }

  /**
   * Get the default from name.
   *
   * @global \MovLib\Kernel $kernel
   * @return string
   *   The default from name.
   */
  protected function getFromName() {
    global $kernel;
    return mb_encode_mimeheader($kernel->siteNameAndSlogan);
  }

  /**
   * Get the additional email headers.
   *
   * @global \MovLib\Kernel $kernel
   * @return string
   *   The additional email headers.
   */
  protected function getHeaders() {
    global $kernel;

    $headers = <<<EOT
Auto-Submitted: auto-generated
Content-Type: multipart/alternative;
\tboundary="{$this->messageID}"
From: "{$this->getFromName()}" <{$kernel->emailFrom}>
Message-ID: <{$this->messageID}@{$kernel->domainDefault}>
MIME-Version: 1.0
Precedence: bulk
EOT;

    if ($this->email->priority === 1) {
      $headers .= <<<EOT
X-Priority: 1 (Highest)
X-MSMail-Priority: High
Importance: High
EOT;
    }

    return $headers;
  }

  /**
   * Get the email's multipart message.
   *
   * @return string
   *   The email's multipart message.
   */
  protected function getMessage() {
    return <<<EOT
--{$this->messageID}
Content-Type: text/plain; charset=utf-8
Content-Transfer-Encoding: BASE64

{$this->getBase64EncodedPlainText()}

--{$this->messageID}
Content-Type: text/html; charset=utf-8
Content-Transfer-Encoding: BASE64

{$this->getBase64EncodedHTML()}

--{$this->messageID}--
EOT;
  }

  /**
   * Get the additional <code>mail()</code> parameters.
   *
   * @global \MovLib\Kernel $kernel
   * @return string
   *   The additional <code>mail()</code> parameters.
   */
  protected function getParameters() {
    global $kernel;
    return "-f {$kernel->emailFrom}";
  }

  /**
   * Get the email's recipient.
   *
   * @return string
   *   The email's recipient.
   * @throws \RuntimeException
   */
  protected function getRecipient() {
    if (strpos($this->email->recipient, ",") !== false) {
      throw new \RuntimeException("An email recipient cannot contain a comma.");
    }
    return $this->email->recipient;
  }

  /**
   * Get the MIME header encoded subject.
   *
   * @return string
   *   The MIME header encoded subject.
   */
  protected function getSubject() {
    return mb_encode_mimeheader($this->email->subject);
  }

  /**
   * Send the given email.
   *
   * @param \MovLib\Data\AbstractEmail $email
   *   The email to send.
   * @return this
   */
  public function send(AbstractEmail $email) {
    $this->email     = $email;
    $this->messageID = uniqid("movlib");
    if (method_exists($email, "init")) {
      try {
        $email->init();
      }
      catch (\Exception $e) {
        return $this;
      }
    }
    mail($this->getRecipient(), $this->getSubject(), $this->getMessage(), $this->getHeaders(), $this->getParameters());
    return $this;
  }

  /**
   * Multi-byte aware wordwrap implementation.
   *
   * Please note that this function will always normalize line feeds to LF.
   *
   * @see wordwrap()
   * @link https://api.drupal.org/api/drupal/core%21vendor%21zendframework%21zend-stdlib%21Zend%21Stdlib%21StringWrapper%21AbstractStringWrapper.php/function/AbstractStringWrapper%3A%3AwordWrap/8
   * @param string $string
   *   The string to wrap.
   * @param int $width [optional]
   *   The number of characters at which the string will be wrapped, defaults to <code>75</code>.
   * @param boolean $cut [optional]
   *   If set to <code>TRUE</code>, the string is always wrapped at or before the specified width. So if you have a word
   *   that is larger than the given width, it is broken apart. Defaults to <code>FALSE</code>.
   * @return string
   *   The string wrapped at the specified length.
   */
  protected function wordwrap($string, $width = 75, $cut = false) {
    // Use native function if we aren't dealing with a multi-byte string.
    if (strlen($string) === mb_strlen($string)) {
      return wordwrap($string, $width, "\n", $cut);
    }
    $strlen = mb_strlen($string);
    $result = "";
    $lastStart = $lastSpace = 0;
    for ($i = 0; $i < $strlen; ++$i) {
      $char = mb_substr($string, $i, 1);
      if ($char === "\n") {
        $result .= mb_substr($string, $lastStart, $i - $lastStart + 1);
        $lastStart = $lastSpace = $i + 1;
        continue;
      }
      if ($char === " ") {
        if ($i - $lastStart >= $width) {
          $result .= mb_substr($string, $lastStart, $i - $lastStart) . "\n";
          $lastStart = $i + 1;
        }
        $lastSpace = $i;
        continue;
      }
      if ($i - $lastStart >= $width && $cut === true && $lastStart >= $lastSpace) {
        $result .= mb_substr($string, $lastStart, $i - $lastStart) . "\n";
        $lastStart = $lastSpace = $i;
        continue;
      }
      if ($i - $lastStart >= $width && $lastStart < $lastSpace) {
        $result .= mb_substr($string, $lastStart, $lastSpace - $lastStart) . "\n";
        $lastStart = $lastSpace = $lastSpace + 1;
        continue;
      }
    }
    if ($lastStart !== $i) {
      $result .= mb_substr($string, $lastStart, $i - $lastStart);
    }
    return $result;
  }

}
