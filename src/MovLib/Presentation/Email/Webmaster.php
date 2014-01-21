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
namespace MovLib\Presentation\Email;

/**
 * Send email to the webmaster.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Webmaster extends \MovLib\Presentation\Email\AbstractEmail {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The message that should be sent to the webmaster.
   *
   * @var string
   */
  protected $message;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new fatal error email.
   *
   * @global \MovLib\Kernel $kernel
   * @param string $subject
   *   The email's subject.
   * @param string $$message
   *   The message that should be sent to the webmaster.
   */
  public function __construct($subject, $message) {
    global $kernel;
    $this->subject   = $subject;
    $this->message   = $message;
    $this->recipient = $kernel->emailWebmaster;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  public function getHTML() {
    global $i18n;
    return "<p>{$i18n->t("Hi webmaster!")}</p><p>{$this->message}</p>";
  }

  /**
   * @inheritdoc
   */
  public function getPlainText() {
    global $i18n;
    return <<<EOT
{$i18n->t("Hi webmaster!")}

{$this->message}
EOT;
  }

}
