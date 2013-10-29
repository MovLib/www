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
 * Email template that is used upon fatal errors to notify the developers.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class FatalErrorEmail extends \MovLib\Presentation\Email\AbstractEmail {
  
  
  // ------------------------------------------------------------------------------------------------------------------- Properties
  
  
  /**
   * The (recreated) exception that was thrown.
   * 
   * @var \Exception
   */
  protected $exception;
  
  
  // ------------------------------------------------------------------------------------------------------------------- Magic Methods
  
  
  /**
   * Instantiate new fatal error email.
   *
   * @global \MovLib\Kernel $kernel
   * @param \Exception $exception
   *   The (recreated) exception that was thrown.
   */
  public function __construct(\Exception $exception) {
    global $kernel;
    $this->exception = $exception;
    $this->priority  = self::PRIORITY_HIGH;
    $this->recipient = $kernel->emailDevelopers;
    $this->subject   = "IMPORTANT! {$kernel->siteName} Fatal Error!";
  }

  
  // ------------------------------------------------------------------------------------------------------------------- Methods
  
  
  /**
   * @inheritdoc
   */
  public function getHTML() {
    $stacktrace = htmlspecialchars($this->exception->getTraceAsString(), ENT_QUOTES);
    return
      "<p>Hi developers!</p>" .
      "<p>The system might be unusable, action must be taken immediately!</p>" .
      "<pre style='background:#eaeaea;padding:5px'>{$stacktrace}</pre>"
    ;
  }

  /**
   * @inheritdoc
   */
  public function getPlainText() {
    return <<<EOT
Hi developers!

The system might be unusable, action must be taken immediately!

{$this->exception->getTraceAsString()}
EOT;
  }

}
