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
namespace MovLib\Mail;

/**
 * Mailer system.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Mailer {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Default width for word wrapping in plain text mails.
   *
   * @var integer
   */
  const WORDWRAP = 75;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Active global configuration instance.
   *
   * @var \MovLib\Core\Config
   */
  protected $config;

  /**
   * The email we are currently sending.
   *
   * @var \MovLib\Presentation\Email\AbstractEmail
   */
  protected $email;

  /**
   * Used to stack the emails that should be sent after the response was sent to the client.
   *
   * @var array
   */
  static protected $emailStack = [];

  /**
   * The current email's unique message ID.
   *
   * @var string
   */
  protected $messageID;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Replace non-quotation markers from a given piece of indentation with spaces.
   *
   * @param string $indent
   *   The string to indent.
   * @return string
   *   <var>$indent</var> with non-quotation markers replaced by spaces.
   */
  protected function cleanIndent($indent) {
    return preg_replace("/[^>]/", " ", $indent);
  }

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
      // @codingStandardsIgnoreStart
      "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>" .
      "<html>" .
        "<head>" .
          "<meta http-equiv='Content-Type' content='text/html;charset=utf-8'/>" .
          "<title>{$this->email->subject}</title>" .
        "</head>" .
        "<body style='font:\"open sans\",arial,sans-serif'>{$this->email->getHTML()}</body>" .
      "</html>"
      // @codingStandardsIgnoreEnd
    );
  }

  /**
   * Get the base64 encoded plain text message.
   *
   * @return string
   *   The base64 encoded plain text message.
   */
  protected function getBase64EncodedPlainText() {
    return base64_encode($this->wordwrap(
      "{{$this->email->getPlainText()}\n\n--\n{$this->config->siteName}\n"
    ));
  }

  /**
   * Get the default from name.
   *
   * @return string
   *   The default from name.
   */
  protected function getFromName() {
    return mb_encode_mimeheader($this->config->siteNameAndSlogan);
  }

  /**
   * Get the additional email headers.
   *
   * @return string
   *   The additional email headers.
   */
  protected function getHeaders() {
    $headers = <<<EOT
Auto-Submitted: auto-generated
Content-Type: multipart/alternative;
\tboundary="{$this->messageID}"
From: "{$this->getFromName()}" <{$this->config->emailFrom}>
Message-ID: <{$this->messageID}@{$this->config->hostname}>
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
   * @return string
   *   The additional <code>mail()</code> parameters.
   */
  protected function getParameters() {
    return "-f {$this->config->emailFrom}";
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
   * Transform HTML string to plain-text string for mail.
   *
   * @param string $html
   *   The HTML string to transform.
   * @return string
   *   <var>$html</var> as plain-text string for mail.
   */
  protected function htmlToText($html) {
    // Apply inline styles.
    $html = preg_replace("#</?(em|i)((?> +)[^>]*)?>#i", "*", $html);
    $html = preg_replace("#</?(strong|b)((?> +)[^>]*)?>#i", "**", $html);

    // Replace inline <a> tags with the text of the link and a footnote.
    $footnotes = null;
    $html = preg_replace_callback("#(<a[^>]+?href=[\"|']([^\"|']*)[\"|'][^>]*?>(.+?)</a>)#i", function ($match) use (&$footnotes) {
      static $urlCount = 0;
      // Only return the URL if linked text and URL are identical.
      if ($match[2] == $match[3]) {
        return $match[2];
      }
      // Return the text with a placeholder token appended otherwise and collect the linked URL for the footnotes.
      else {
        if ($urlCount === 0) {
          $footnotes = "\n";
        }
        ++$urlCount;
        $footnotes .= "[{$urlCount}] {$match[2]}";
        return "{$match[3]} [{$urlCount}]";
      }
    }, $html);

    // Split HTML tags.
    // NOTE: PHP ensures that the array consists of alternating delimiters and literals and begins and ends with a
    //       literal (inserting NULL as required).
    $splitted = preg_split("/<([^>]+?)>/", $html, -1, PREG_SPLIT_DELIM_CAPTURE);

    $tag = false;
    $caseing = null;
    $output = null;
    $indent = [];
    $lists = [];

    foreach ($splitted as $value) {
      $chunk = null;

      if ($tag) {
        list($tagname) = explode(" ", strtolower($value), 2);
        switch ($tagname) {
          case "ul":
            array_unshift($lists, "*");
            break;

          case "ol":
            array_unshift($lists, 1);
            break;

          case "/ul":
          case "/ol":
            array_shift($lists);
            $chunk = "";
            break;

          case "blockquote":
            $indent[] = count($lists) ? " \"" : ">";
            break;

          case "li":
            $indent[] = isset($lists[0]) && is_numeric($lists[0]) ? " " . $lists[0]++ . ") " : " * ";
            break;

          case "dd":
            $indent[] = "    ";
            break;

          case "/blockquote":
            if (count($lists)) {
              $output = rtrim($output, "> \n") . "\"\n";
              $chunk  = "";
            }
            // no break

          case "/li":
          case "/dd":
            array_pop($indent);
            break;

          case "h1":
          case "h2":
            $caseing = "mb_strtoupper";
            // no break

          case "h3":
          case "h4":
          case "h5":
          case "h6":
            $indent[] = str_repeat("#", (integer) substr($tagname, -1)) . " ";
            break;

          case "/h1":
          case "/h2":
            $caseing = null;
            // no break

          case "/h3":
          case "/h4":
          case "/h5":
          case "/h6":
            array_pop($indent);
            // no break

          case "/p":
          case "/dl":
            $chunk = "";
            break;

          case "hr":
            $output .= "\n\n" . str_repeat("-", self::WORDWRAP) . "\n\n";
            break;
        }
      }
      else {
        $value = trim($this->htmlDecodeEntities($value));
        if (mb_strlen($value)) {
          $chunk = $value;
        }
      }

      if ($chunk) {
        if ($caseing) {
          $chunk = $caseing($chunk);
        }
        $output .= "{$this->indentedWordwrap($chunk, implode($indent))}\n";
        $indent  = array_map([ $this, "cleanIndent" ], $indent);
      }

      $tag = !$tag;
    }

    return "{$output}{$footnotes}";
  }

  /**
   * Performs <code>format=flowed</code> soft wrapping for mail (RFC 3676).
   *
   * We use <code>delsp=yes</code> wrapping, but only break non-spaced languages when absolutely necessary to avoid
   * compatibility issues.
   *
   * @param string $text
   *   The plain-text to process.
   * @param string $indent
   *   A string to indent the text with. Only <code>">"</code> characters are repeated on subsequent wrapped lines.
   *   Others are replaced by spaces.
   * @return string
   *   <var>$text</var> with formatting applied.
   */
  protected function indentedWordwrap($text, $indent) {
    // See if soft-wrapping is allowed.
    $cleanIndent = $this->cleanIndent($indent);
    $soft        = strpos($cleanIndent, " ") === false;

    // Check if text contains line feeds.
    if (strpos($text, "\n") === false) {
      $this->lineWordwrap($text, 0, [ "soft" => $soft, "length" => strlen($indent) ]);
    }
    else {
      // Remove trailing spaces to make existing breaks hard, but leave signature marker untouched (RFC 3676, Section 4.3).
      $text = preg_replace("/(?(?<!^--) +\n|  +\n)/m", "\n", $text);

      // Wrap each line at the needed width.
      $lines = explode("\n", $text);
      array_walk($lines, [ $this, "lineWordwrap" ], [ "soft" => $soft, "length" => strlen($indent) ]);
      $text = implode("\n", $lines);
    }

    // Empty lines with nothing but spaces.
    $text = preg_replace("/^ +\n/m", "\n", $text);

    // Space-stuff special lines.
    $text = preg_replace("/^(>| |From)/m", " $1", $text);

    // Apply indentation. We only include non-">" identation on the first line.
    $text = $indent . substr(preg_replace("/^/m", $cleanIndent, $text), strlen($indent));

    return $text;
  }

  /**
   * Wrap words on a single mail line.
   *
   * @param string $line
   *   The plain-text line to process.
   * @param integer $key
   *   The array key (unused).
   * @param array $options
   *   Associative array containing the wordwrap options.
   */
  protected function lineWordwrap(&$line, $key, array $options) {
    $line = $this->wordwrap($line, 77 - $options["length"], $options["soft"] ? " \n" : "\n");
  }

  /**
   * Send the given email.
   *
   * @param \MovLib\Core\DIContainer $diContainer
   *   The dependency injection container.
   * @param \MovLib\Data\AbstractEmail $email
   *   The email to send.
   * @return this
   */
  public function send(\MovLib\Core\DIContainer $diContainer, \MovLib\Mail\AbstractEmail $email) {
    if (empty(self::$emailStack)) {
      /* @var $kernel \MovLib\Core\Kernel */
      $diContainer->kernel->delayMethodCall([ $this, "sendEmailStack" ], [ $diContainer->config, $diContainer->log ]);
    }
    self::$emailStack[] = $email;
    return $this;
  }

  /**
   * Send all stacked emails.
   *
   * @param \MovLib\Core\Config $config
   *   Active global configuration instance.
   * @param \MovLib\Core\Log $log
   *   Active log instance.
   * @return this
   */
  public function sendEmailStack(\MovLib\Core\Config $config, \MovLib\Core\Log $log) {
    $this->config = $config;
    /* @var $email \MovLib\Mail\AbstractEmail */
    foreach (self::$emailStack as $email) {
      try {
        $this->email     = $email;
        $this->messageID = uniqid("movlib");
        if (method_exists($email, "init")) {
          $email->init();
        }
        mail($this->getRecipient(), $this->getSubject(), $this->getMessage(), $this->getHeaders(), $this->getParameters());
      }
      catch (\Exception $e) {
        $log->error($e);
      }
    }
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
   * @param string $break [optional]
   *   Character to break the line with.
   * @param boolean $cut [optional]
   *   If set to <code>TRUE</code>, the string is always wrapped at or before the specified width. So if you have a word
   *   that is larger than the given width, it is broken apart. Defaults to <code>FALSE</code>.
   * @return string
   *   The string wrapped at the specified length.
   */
  protected function wordwrap($string, $width = self::WORDWRAP, $break = "\n", $cut = false) {
    $strlen = mb_strlen($string);

    // Use native function if we aren't dealing with a multi-byte string.
    if (strlen($string) === $strlen) {
      return wordwrap($string, $width, $break, $cut);
    }

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
          $result .= mb_substr($string, $lastStart, $i - $lastStart) . $break;
          $lastStart = $i + 1;
        }
        $lastSpace = $i;
        continue;
      }

      if ($i - $lastStart >= $width && $cut === true && $lastStart >= $lastSpace) {
        $result .= mb_substr($string, $lastStart, $i - $lastStart) . $break;
        $lastStart = $lastSpace = $i;
        continue;
      }

      if ($i - $lastStart >= $width && $lastStart < $lastSpace) {
        $result .= mb_substr($string, $lastStart, $lastSpace - $lastStart) . $break;
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
