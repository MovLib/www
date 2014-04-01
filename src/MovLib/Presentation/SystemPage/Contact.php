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
namespace MovLib\Presentation\SystemPage;

use \MovLib\Presentation\Email\Webmaster;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\Form;
use \MovLib\Presentation\Partial\FormElement\InputHTML;
use \MovLib\Presentation\Partial\FormElement\InputSubmit;
use \MovLib\Presentation\Partial\FormElement\InputText;

/**
 * Contact page presentation.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Contact extends \MovLib\Presentation\SystemPage\Show {
  use \MovLib\Presentation\TraitForm;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The email's body.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputHTML
   */
  protected $message;

  /**
   * The email's subject.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputText
   */
  protected $subject;

  /**
   * The success alert message.
   *
   * @var \MovLib\Presentation\Partial\Alert
   */
  protected $success;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new contact system page.
   *
   */
  public function __construct() {
    parent::__construct();
    $this->subject                = new InputText("subject", $this->intl->t("Subject"), [
      "placeholder" => $this->intl->t("This will appear as subject of your message"),
      "required",
    ]);
    $this->message                = new InputHTML("message", $this->intl->t("Message"));
    $this->message->attributes[]  = "required";
    $this->form                   = new Form($this, [ $this->subject, $this->message ]);
    $this->form->actionElements[] = new InputSubmit($this->intl->t("Send"), [ "class" => "btn btn-success btn-large" ]);
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  protected function getPageContent(){
    $append = $this->success ?: $this->form;
    return "<div class='c'><div class='r'><div class='s s10'>{$this->htmlDecode($this->systemPage->text)}{$append}</div></div></div>";
  }

  /**
   * @inheritdoc
   */
  protected function valid() {
    // Send the contact email to the webmaster.
    $kernel->sendEmail(new Webmaster($this->subject->value, $this->htmlDecode($this->message->value)));

    // Submission was successful but further action is required, let the client know.
    http_response_code(202);

    // Display success alert so the user knows that the submission was successful.
    $this->success = new Alert(
      $this->intl->t("Contact Successful"),
      $this->intl->t("Contact Successful"),
      Alert::SEVERITY_SUCCESS
    );

    return $this;
  }

}
