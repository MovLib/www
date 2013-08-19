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

use \DateTime;
use \MovLib\Exception\UserException;
use \MovLib\Exception\ErrorException;
use \MovLib\Exception\MailerException;
use \MovLib\Model\UserModel;
use \MovLib\Utility\DelayedLogger;
use \MovLib\Utility\String;

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
class DelayedMailer {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * If set to <code>TRUE</code> all SMTP responses will be written in real-time to the debug log.
   *
   * @var boolean
   */
  const DEBUG = false;

  /**
   * SMTP connect timeout in seconds.
   *
   * @var int
   */
  const CONNECT_TIMEOUT = 30;

  const HEADER_ENCODE_PHRASE = 0;

  const HEADER_ENCODE_COMMENT = 1;

  const HEADER_ENCODE_TEXT = 2;

  /**
   * Each header line must have length <= 75, including start (=?utf-8?B?) and end (?=).
   *
   * @var int
   */
  const HEADER_MAX_LENGTH = 63;

  /**
   * SMTP response timeout in seconds.
   *
   * @var int
   */
  const RESPONSE_TIMEOUT = 15;

  /**
   * SMTP read timeout in seconds.
   *
   * @var int
   */
  const READ_TIMEOUT = 30;


  // ------------------------------------------------------------------------------------------------------------------- Public Properties


  /**
   * The email address from which this mail was sent.
   *
   * @var string
   */
  public $from;

  /**
   * The name that appears in the email client of the recipient along the from email address.
   *
   * @var string
   */
  public $fromName;

  /**
   *
   * @var boolean
   */
  public $keepAlive = true;


  // ------------------------------------------------------------------------------------------------------------------- Private Properties


  /**
   * Socket connection to the server.
   *
   * @var resource
   */
  private $connection;

  /**
   * The SMTP host.
   *
   * @var string
   */
  private $host;

  /**
   * The SMTP port.
   *
   * @var int
   */
  private $port;

  /**
   * The SMTP user's name.
   *
   * @var string
   */
  private $username;

  /**
   * The SMTP user's password.
   *
   * @var string
   */
  private $password;

  /**
   * The last response message from the server.
   *
   * @var string
   */
  private $responseMessage = "";

  /**
   * The last response code from the server.
   *
   * @var int
   */
  private $responseCode = 0;


  // ------------------------------------------------------------------------------------------------------------------- Static Properties


  /**
   * Array used to collect all mails that should be sent.
   *
   * @var array
   */
  private static $mails = [];


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Create new Mailer instance.
   *
   * <b>IMPORTANT:</b> This will not connect to the SMTP server!
   *
   * @global type $i18n
   *   The global i18n object.
   */
  public function __construct() {
    global $i18n;
    // Export all ini values to class scope.
    foreach (parse_ini_file("{$_SERVER["HOME"]}/conf/mail/mail.ini") as $prop => $value) {
      $this->{$prop} = $value;
    }
    // Translate the from name to the current display language and protect it with quotes.
    $this->fromName = '"' . $i18n->t($this->fromName) . '"';
  }

  /**
   * Disconnect gracefully from SMTP server if a connection is open upon destruction of this instance.
   */
  public function __destruct() {
    if ($this->connected()) {
      $this->disconnect();
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Public Methods


  /**
   * Establish TLS encrypted and authenticated connection to SMTP server.
   *
   * @return $this
   * @throws \MovLib\Exception\MailerException
   */
  public function connect() {
    if (!$this->connected()) {
      try {
        $this
          ->debug("Establishing stream socket connection to SMTP host {$this->host} on port {$this->port}.")
          ->connection = stream_socket_client("{$this->host}:{$this->port}", $errno, $errstr, self::CONNECT_TIMEOUT, STREAM_CLIENT_CONNECT, stream_context_create())
        ;
      } catch (ErrorException $e) {
        throw new MailerException("Could not connect to SMTP server. SOCKET ERROR {$errno}: {$errstr}", $e);
      }
      // Check if server sent proper announce.
      if ($this->serverResponse()->responseCode !== 220) {
        throw new MailerException("Did not receive SMTP announcement. SMTP ERROR {$this->responseCode}: {$this->responseMessage}");
      }

      // Snychronize with SMTP server and encrypt connection.
      $this->hello()->clientSend("STARTTLS\r\n");
      if ($this->responseCode !== 220) {
        throw new MailerException("Could not innitiate STARTTLS communication with SMTP server. SMTP ERROR {$this->responseCode}: {$this->responseMessage}");
      }
      if (!stream_socket_enable_crypto($this->connection, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
        throw new MailerException("Could not encrypt SMTP stream.");
      }

      // We have to re-synch after establishing the encrypted communication and afterwards we can log in with our user.
      $this->hello()->clientSend("AUTH CRAM-MD5\r\n");
      if ($this->responseCode !== 334) {
        throw new MailerException("Authentication method not accepted from SMTP server. SMTP ERROR {$this->responseCode}: {$this->responseMessage}");
      }
      $challenge = base64_decode(substr($this->responseMessage, 4));
      $password = hash_hmac("md5", $challenge, $this->password);
      $this->clientSend(base64_encode("{$this->username} {$password}") . "\r\n");
      if ($this->responseCode !== 235) {
        throw new MailerException("Credentials not accepted from SMTP server. SMTP ERROR {$this->responseCode}: {$this->responseMessage}");
      }
    }
    return $this;
  }

  /**
   * Check if we are connected to the SMTP server.
   *
   * @return boolean
   *   <code>TRUE</code> if connected, otherwise <code>FALSE</code>.
   */
  public function connected() {
    if (is_resource($this->connection)) {
      if (!stream_get_meta_data($this->connection)["eof"]) {
        return true;
      }
      $this->debug("EOF caught while checking if connected to SMTP server.");
      $this->closeConnection();
    }
    return false;
  }

  /**
   * Send quit command to SMTP server and then closes the socket if there is no error.
   *
   * Implements <code>QUIT <CRLF></code> from RFC 821.
   *
   * @link http://www.ietf.org/rfc/rfc2821.txt
   * @return $this
   * @throws \MovLib\Exception\MailerException
   */
  public function disconnect() {
    if (is_resource($this->connection)) {
      $this->clientSend("quit\r\n");
      if ($this->responseCode !== 221) {
        throw new MailerException("SMTP server rejected quit command. SMTP ERROR {$this->responseCode}: {$this->responseMessage}");
      }
      $this->closeConnection();
    }
    return $this;
  }

  /**
   * Send the given mail.
   *
   * @todo Digitally sign with DKIM!
   * @param \MovLib\View\Mail\AbstractMail $mail
   *   The mail to send.
   * @return $this
   * @throws MailerException
   */
  public function send($mail) {
    if (method_exists($mail, "init")) {
      $mail->init();
    }

    $this->clientSend("MAIL FROM:<{$this->from}>\r\n");
    if ($this->responseCode !== 250) {
      throw new MailerException("SMTP server refused mail from ({$this->from}). SMTP ERROR {$this->responseCode}: {$this->responseMessage}");
    }

    $this->clientSend("RCPT TO:<{$mail->recipient}>\r\n");
    if ($this->responseCode !== 250 && $this->responseCode !== 251) {
      throw new MailerException("SMTP server refused recipient to ({$mail->recipient}). SMTP ERROR {$this->responseCode}: {$this->responseMessage}");
    }

    $this->clientSend("DATA\r\n");
    if ($this->responseCode !== 354) {
      throw new MailerException("SMTP server refused data command. SMTP ERROR {$this->responseCode}: {$this->responseMessage}");
    }

    // Build the message ID following common best practices.
    $messageId = String::base36encode(microtime(true)) . "." . String::base36encode(md5("{$this->from}.{$mail->recipient}"));

    // @todo Split DATA every 1000 characters!
    $this->clientSend(implode("\r\n", [
      "Content-Transfer-Encoding: 8bit",
      "Content-Type: multipart/alternative;",
      "\tboundary=\"{$messageId}\"",
      "Date: " . date(DateTime::RFC822),
      "From: {$this->encodeHeader($this->fromName)} <{$this->from}>",
      "Message-ID: <{$messageId}@{$_SERVER["SERVER_NAME"]}>",
      "MIME-Version: 1.0",
      "Return-Path: <{$this->from}>",
      "Subject: {$this->encodeHeader(String::removeNewlines($mail->subject))}",
      "To: {$mail->recipient}",
      "",
      "--{$messageId}",
      "Content-Type: text/plain; charset=utf-8",
      "Content-Transfer-Encoding: 8bit",
      "",
      String::wordwrap(str_replace(array("\r", "\r\n"), "\n", $mail->getPlain()), 80, "\n", true),
      "",
      "--{$messageId}",
      "Content-Type: text/html; charset=utf-8",
      "Content-Transfer-Encoding: 8bit",
      "",
      String::removeNewlines($mail->getHtml()),
      "",
      "--{$messageId}--",
      "",
      ".",
      ""
    ]));
    if ($this->responseCode !== 250) {
      throw new MailerException("SMTP server refused data payload. SMTP ERROR {$this->responseCode}: {$this->responseMessage}");
    }

    $this->keepAlive === true ? $this->reset() : $this->disconnect();
    return $this;
  }


  // ------------------------------------------------------------------------------------------------------------------- Public Static Methods


  /**
   * Sends all mails in the stack.
   */
  public static function run() {
    $mailer = (new DelayedMailer())->connect();
    $c = count(self::$mails);
    for ($i = 0; $i < $c; ++$i) {
      $mailer->send(self::$mails[$i]);
    }
    $mailer->disconnect();
  }

  /**
   * Add mail to the stack.
   *
   * @param string|array $recipient
   *   Recipient(s) of the mail, must comply with RFC 2822. If more then one recipient should receive this mail, all
   *   mails must be passed as numeric array.
   * @param string $subject
   *   The mail's subject, must comply with RFC 2047.
   * @param string $htmlBody
   *
   */

  /**
   * Add mail to stack.
   *
   * @param \MovLib\View\Mail\AbstractMail $mail
   *   The mail that should be sent.
   */
  public static function stack($mail) {
    delayed_register(__CLASS__, 0);
    self::$mails[] = $mail;
  }


  // ------------------------------------------------------------------------------------------------------------------- Private Methods


  /**
   * Send data to the server.
   *
   * @see \MovLib\View\Mail\AbstractMailer::serverResponse()
   * @param string $data
   *   The data to send.
   * @return $this
   * @throws \MovLib\Exception\MailerException
   */
  private function clientSend($data) {
    if (fwrite($this->connection, $data) === false) {
      throw new MailerException("Sending data to SMTP server failed. Data was:\n\n{$data}");
    }
    return $this->debug("Sent: {$data}")->serverResponse();
  }

  /**
   * Close the socket and clean up. You should always call <code>Mailer::quit()</code> before calling this method.
   *
   * @return $this
   */
  private function closeConnection() {
    if (is_resource($this->connection)) {
      fclose($this->connection);
      $this->connection = null;
    }
    return $this;
  }

  /**
   * Writes the given message to the debug log if the class constants <var>DelayedMailer::DEBUG</var> is set to
   * <code>TRUE</code>.
   *
   * <b>HINT:</b> Use the following command in your shell to check the debug log in real-time:
   * <code>tail -f /var/www/logs/debug-smtp.log</code>
   *
   * @param string $message
   *   The message to log.
   * @return $this
   */
  private function debug($message) {
    if (self::DEBUG === true) {
      // Passing a string as level will create a separate log file for us.
      DelayedLogger::logNow(rtrim($message, PHP_EOL) . PHP_EOL, "debug-smtp");
    }
    return $this;
  }

  /**
   * Enoce a header string to the best (shortest) of B, Q, quoted or none encoding.
   *
   * @param string $header
   *   The header to encode.
   * @param int $position
   *   The position of the header. Use the class constants <var>DelayedMailer::HEADER_ENCODE_*</var>.
   * @return string
   *   The encoded header.
   */
  private function encodeHeader($header, $position = self::HEADER_ENCODE_TEXT) {
    $x = null;
    $matches = null;

    switch ($position) {
      case self::HEADER_ENCODE_PHRASE:
        if (!preg_match('/[\200-\377]/', $header)) {
          $encoded = addcslashes($header, "\0..\37\177\\\"");
          if (($header === $encoded) && !preg_match('/[^A-Za-z0-9!#$%&\'*+\/=?^_`{|}~ -]/', $header)) {
            return $encoded;
          }
          else {
            return '"' . $encoded . '"';
          }
        }
        $x = preg_match_all('/[^\040\041\043-\133\135-\176]/', $header, $matches);
        break;

      case self::HEADER_ENCODE_COMMENT:
        $x = preg_match_all('/[()"]/', $header, $matches);
        // Fall through

      case self::HEADER_ENCODE_TEXT:
      default:
        $x += preg_match_all('/[\000-\010\013\014\016-\037\177-\377]/', $header, $matches);
    }

    // No matches and therefor nothing to encode.
    if (!$x) {
      return $header;
    }

    // Try to select the encoding which should produce the shortest output.
    // If more than a third of the content will need encoding, B encoding is most efficient.
    if ($x > mb_strlen($header) / 3) {
      $encoding = "B";
      $encoded = $this->encodeHeaderBencode($header);
    }
    else {
      $encoding = "Q";
      $encoded = $this->encodeHeaderQencode($header, $position);
    }
    return preg_replace("/^(.*)$/m", " =?utf-8?{$encoding}?\\1?=", $encoded);
  }

  /**
   * Bencode the given header.
   *
   * @link https://en.wikipedia.org/wiki/Bencode
   * @param string $header
   *   The header to encode.
   * @return string
   *   The encoded header.
   */
  private function encodeHeaderBencode($header) {
    $encoded = "";
    $mbLength = mb_strlen($header);
    if ($mbLength === strlen($header)) {
      $encoded = trim(chunk_split($header, self::HEADER_MAX_LENGTH % 4, "\n"));
    }
    else {
      // Base64 has a 4:3 ratio.
      $offset = $avgLength = floor(self::HEADER_MAX_LENGTH * ($mbLength / strlen($header)) * 0.75);
      for ($i = 0; $i < $mbLength; $i += $offset) {
        $lookBack = 0;
        do {
          $offset = $avgLength - $lookBack;
          $chunk = base64_encode(mb_substr($header, $i, $offset));
          ++$lookBack;
        }
        while (strlen($chunk) > self::HEADER_MAX_LENGTH);
        if ($i !== 0) {
          $encoded .= "\n";
        }
        $encoded .= $chunk;
      }
    }
    return $encoded;
  }

  /**
   * Qencode the given header.
   *
   * @param string $header
   *   The header to encode.
   * @param int $position
   *   The position of the header. Use the class constants <var>DelayedMailer::HEADER_ENCODE_*</var>.
   * @return string
   *   The encoded header.
   */
  private function encodeHeaderQencode($header, $position) {
    $pattern = "";
    $encoded = String::removeNewlines($header);
    switch ($position) {
      case self::HEADER_ENCODE_PHRASE:
        $pattern = '^A-Za-z0-9!*+\/ -';
        break;

      case self::HEADER_ENCODE_COMMENT:
        $pattern = '\(\)"';
        // Fall through

      case self::HEADER_ENCODE_TEXT:
        $pattern = '"\075\000-\011\013\014\016-\037\077\137\177-\377' . $pattern;
    }
    if (preg_match_all('/[' . $pattern . ']/', $encoded, $matches)) {
      foreach (array_unique($matches[0]) as $char) {
        $encoded = str_replace($char, "=" . sprintf("%02X", ord($char)), $encoded);
      }
    }
    return str_replace(" ", "_", $encoded);
  }

  /**
   * Sends <code>EHLO</code> (extended hello) or <code>HELO</code> command to the SMTP server. This makes sure that we
   * and the server are in the same known state.
   *
   * Implements RFC 821: <code>HELO <SP> <domain> <CRLF></code>
   *
   * @link http://www.ietf.org/rfc/rfc2821.txt
   * @see \MovLib\View\Mail\AbstractMailer::helloSend()
   * @return $this
   * @throws \MovLib\Exception\MailerException
   */
  private function hello() {
    if (!$this->helloSend("EHLO")) {
      $e = new MailerException("SMTP server did not accept EHLO command. SMTP ERROR {$this->responseCode}: {$this->responseMessage}");
      if (!$this->helloSend("HELO")) {
        throw new MailerException("SMTP server did not accept EHLO nor HELO command. SMTP ERROR {$this->responseCode}: {$this->responseMessage}", $e);
      }
    }
    return $this;
  }

  /**
   * Send <code>EHLO</code> or <code>HELO</code> command.
   *
   * @param string $command
   *   Either <code>EHLO</code> or <code>HELO</code>.
   * @return boolean
   *   <code>TRUE</code> if command succeeded, otherwise <code>FALSE</code>.
   */
  private function helloSend($command) {
    $this->clientSend("{$command} {$this->host}\r\n");
    return $this->responseCode === 250;
  }

  /**
   * Sendes the <code>RSET</code> command to abort a transaction that is currently in progress.
   *
   * @return $this
   * @throws \MovLib\Exception\MailerException
   */
  private function reset() {
    $this->clientSend("RSET\r\n");
    if ($this->responseCode !== 250) {
      throw new MailerException("SMTP server refused RSET command. SMTP ERROR {$this->responseCode}: {$this->responseMessage}");
    }
    return $this;
  }

  /**
   * Read as many lines as possible from the SMTP response, either before EOF or socket timeout.
   *
   * If the 4th character in the response is a dash (-) we have more lines to read. There are no more lines to read if
   * it is a space.
   *
   * Use the properties <code>Mailer::$responseMessage</code> and <code>Mailer::$responseCode</code> to access the
   * response after calling this method.
   *
   * @return $this
   * @throws \MovLib\Exception\MailerException
   */
  private function serverResponse() {
    $response = "";
    $endTime = time() + self::READ_TIMEOUT;
    stream_set_timeout($this->connection, self::RESPONSE_TIMEOUT);
    while (is_resource($this->connection) && !feof($this->connection)) {
      $response .= $tmp = fgets($this->connection, 515);
      // If the 4th character is a space, we are done reading.
      if (substr($tmp, 3, 1) === " ") {
        break;
      }
      $responseTimedout = stream_get_meta_data($this->connection)["timed_out"];
      $readTimedout = time() > $endTime;
      if ($responseTimedout || $readTimedout) {
        $this->disconnect();
        throw new MailerException($responseTimedout
          ? "SMTP timed out (response took more then " . self::RESPONSE_TIMEOUT . " seconds)."
          : "SMTP timelimit reached (read took more then " . self::READ_TIMEOUT . " seconds)."
        );
      }
    }
    $this->responseMessage = $response;
    $this->responseCode = (int) substr($response, 0, 3);
    return $this->debug($response);
  }

}
