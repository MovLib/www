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

use \MovLib\Partial\Form;
use \MovLib\Partial\FormElement\InputEmail;
use \MovLib\Partial\FormElement\InputHTML;
use \MovLib\Partial\FormElement\InputText;
use \MovLib\Mail\Mailer;
use \MovLib\Mail\Webmaster;

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


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The presenation's form.
   *
   * @var \MovLib\Partial\Form
   */
  protected $form;

  /**
   * The email's body.
   *
   * @var string
   */
  protected $message;

  /**
   * The email's sender.
   *
   * @var string
   */
  protected $sender;

  /**
   * The email's subject.
   *
   * @var string
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
  public function init() {
    parent::init();
    $this->form = new Form($this->diContainerHTTP);
    // Add email input for non-authenticated users.
    if ($this->session->isAuthenticated === false) {
      $this->form->addElement(new InputEmail($this->diContainerHTTP, "email", $this->intl->t("Email address"), $this->sender));
    }
    else {
      $this->sender = "{$this->session->userName}@movlib.org";
    }
    $this->form->addElement(new InputText($this->diContainerHTTP, "subject", $this->intl->t("Subject"), $this->subject), [
      "placeholder" => $this->intl->t("This will appear as subject of your message"),
      "required" => "required",
    ]);
    $this->form->addElement(new InputHTML($this->diContainerHTTP, "message", $this->intl->t("Message"), $this->message, [ "required" => "required"]));
    $this->form->addAction($this->intl->t("Send"), [ "class" => "btn btn-large btn-success" ]);
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  public function getContent(){
    $append = $this->success ?: $this->form;
    return "{$this->htmlDecode($this->systemPage->text)}{$append}";
  }

  /**
   * @inheritdoc
   */
  protected function valid() {
    // Send the contact email to the webmaster.
    (new Mailer())->send(
      $this->diContainerHTTP,
      new Webmaster(
        $this->diContainerHTTP,
        $this->subject,
        "<a href='mailto:{$this->sender}'>{$this->sender}</a>wrote:<br>{$this->htmlDecode($this->message)}"
      )
    );

    // Submission was successful but further action is required, let the client know.
    http_response_code(202);

    // Display success alert so the user knows that the submission was successful.
    $this->alerts .= new Alert(
      $this->intl->t("Contact Successful"),
      $this->intl->t("Contact Successful"),
      Alert::SEVERITY_SUCCESS
    );

    return $this;
  }

}
