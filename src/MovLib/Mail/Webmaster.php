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
 * Send email to the webmaster.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Webmaster extends \MovLib\Mail\AbstractEmail {


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
   * @param string $subject
   *   The email's subject.
   * @param string $$message
   *   The message that should be sent to the webmaster.
   */
  public function __construct(\MovLib\Core\DIContainer $diContainer, $subject, $message) {
    parent::__construct($diContainer, $diContainer->config->emailWebmaster, $subject);
    $this->message = $message;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  public function getHTML() {
    return "<p>{$this->intl->t("Hi webmaster!")}</p><p>{$this->message}</p>";
  }

  /**
   * @inheritdoc
   */
  public function getPlainText() {
    return <<<EOT
{$this->intl->t("Hi webmaster!")}

{$this->message}
EOT;
  }

}
