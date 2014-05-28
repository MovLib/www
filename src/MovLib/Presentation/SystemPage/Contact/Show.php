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
namespace MovLib\Presentation\SystemPage\Contact;

use \MovLib\Partial\Form;
use \MovLib\Partial\FormElement\InputEmail;
use \MovLib\Partial\FormElement\TextareaHTML;
use \MovLib\Partial\FormElement\InputText;
use \MovLib\Mail\Mailer;
use \MovLib\Mail\Webmaster;

/**
 * Defines the contact system page's presenter.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Show extends \MovLib\Presentation\SystemPage\AbstractShow {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Show";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


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


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  public function init() {
    return $this->initSystemPage(1, $this->intl->t("Contact"));
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    $this->schemaType = "ContactPage";
    $this->headingSchemaProperty = "name";

    $form              = new Form($this->container);
    $subjectAttributes = [
      "placeholder" => $this->intl->t("This will appear as subject of your message"),
      "required"    => true,
    ];

    if ($this->session->isAuthenticated) {
      $this->sender                   = "{$this->session->userName}@movlib.org";
      $subjectAttributes["autofocus"] = true;
    }
    else {
      $form->addElement(new InputEmail($this->container, "email", $this->intl->t("Email address"), $this->sender, [
        "autofocus"   => true,
        "placeholder" => $this->intl->t("Your email address"),
        "required"    => true,
      ]));
    }

    $form
      ->addElement(new InputText($this->container, "subject", $this->intl->t("Subject"), $this->subject, $subjectAttributes))
      ->addElement(new TextareaHTML($this->container, "message", $this->intl->t("Message"), $this->message, [
        "placeholder" => $this->intl->t("Type your message here…"),
        "required"    => true,
      ]))
      ->addAction($this->intl->t("Send"), [ "class" => "btn btn-large btn-success" ])
      ->init([ $this, "valid" ])
    ;

    return parent::getContent() . $form;
  }

  /**
   * {@inheritdoc}
   */
  public function valid() {
    // Send an email to the webmaster.
    (new Mailer())->send(new Webmaster($this->subject, $this->intl->t(
      "{email} wrote: {message}",
      [ "email" => "<a href='mailto:{$this->sender}'>{$this->sender}</a>", "message" => "<p>{$this->htmlDecode($this->message)}</p>" ]
    )));

    // Submission was successful but further action is required, let the client know.
    http_response_code(202);

    // Display success alert so the user knows that the submission was successful.
    return $this->alertSuccess(
      $this->intl->t("Contact successful"),
      $this->intl->t("Thank you for your email, we’ll get back to you asap.")
    );
  }

}
