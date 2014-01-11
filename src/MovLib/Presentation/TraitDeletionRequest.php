<?php

/* !
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
namespace MovLib\Presentation;

use \MovLib\Data\DeletionRequest;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\Form;
use \MovLib\Presentation\Partial\FormElement\InputHTML;
use \MovLib\Presentation\Partial\FormElement\InputSubmit;
use \MovLib\Presentation\Partial\FormElement\InputURL;
use \MovLib\Presentation\Partial\FormElement\Select;

/**
 * Implements the form elements that are necessary for any deletion request presentation.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
trait TraitDeletionRequest {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Input URL form element where the user has to paste the full URL of the content that's already available.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputURL
   */
  protected $inputDuplicateURL;

  /**
   * Input HTML form element where the user has to write an explanation if none of the predefined reasons is used.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputHTML
   */
  protected $inputOtherExplanation;

  /**
   * Select form element containing available reason's for a deletion request.
   *
   * @var \MovLib\Presentation\Partial\FormElement\Select
   */
  protected $selectReason;


  // ------------------------------------------------------------------------------------------------------------------- Abstract Methods


  /**
   * Stores the deletion request's identifier in the database record that should be deleted.
   *
   * @param integer $deletionRequestIdentifier
   *   The unique identifier of the deletion request.
   * @return this
   */
  protected abstract function storeDeletionRequestIdentifier($deletionRequestIdentifier);


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get the deletion request form.
   *
   * @return string
   *   The deletion request form.
   */
  protected function getDeletionRequestForm() {
    $duplicate = DeletionRequest::REASON_DUPLICATE;
    $other     = DeletionRequest::REASON_OTHER;
    return "{$this->form->open()}{$this->selectReason}<div id='info-{$duplicate}' class='hidden info'>{$this->inputDuplicateURL}</div><div id='info-{$other}' class='hidden info'>{$this->inputOtherExplanation}</div>{$this->form->close()}";
  }

  /**
   * Initialize the deletion request form.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @return this
   * @throws \LogicException
   */
  protected function initDeletionRequest() {
    global $i18n, $kernel;

    // @devStart
    // @codeCoverageIgnoreStart

    // We can't directly extend the form trait because of inheritance chains which might lead to double inclusion of the
    // same trait.
    if (!property_exists($this, "form")) {
      throw new \LogicException("You have to include the form trait in order to include the deletion request trait.");
    }

    // The initialization of the language links is essential to this trait because we have to store them along the
    // deletion request in the database record for this deletion request.
    if (empty($this->languageLinks)) {
      throw new \LogicException("You have to initialize the language links before you initialize the deletion request form.");
    }

    // The breadcrumb is also crucial for the cancel button within the form.
    if (empty($this->breadcrumb)) {
      throw new \LogicException("You have to initialize the breadcrumb before you initialize the deletion request form.");
    }

    // @codeCoverageIgnoreEnd
    // @devEnd

    // Conditionally show and require form elements, depending on selected reason.
    $kernel->javascripts[] = "Deletion";

    // Initialize the reason select form element with all available deletion request reasons, the user is required to
    // select an option before submitting the deletion request.
    $this->selectReason = new Select("reason", $i18n->t("Reason"), DeletionRequest::getTypes(), null, [ "required" ]);

    // The user has to give us the URL of the already existing content that is duplicated.
    $this->inputDuplicateURL = new InputURL("duplicate", $i18n->t("URL"));
    $this->inputDuplicateURL->setHelp($i18n->t("Enter the URL of the existing content."));

    // The user has to explain why this particular content should be deleted if none of the predefined reasons is
    // sufficient for the deletion request.
    $this->inputOtherExplanation = new InputHTML("other", $i18n->t("Explanation"), null, [
      "placeholder" => $i18n->t("Please explain why this content should be deleted…"),
    ]);

    // Initialize the actual form and include a link back
    $this->form                   = new Form($this, [ $this->selectReason, $this->inputDuplicateURL, $this->inputOtherExplanation ]);
    $this->form->actionElements[] = new InputSubmit($i18n->t("Delete"), [ "class" => "btn btn-danger btn-large" ]);
    $cancel                       = end($this->breadcrumb->menuitems);
    $this->form->actionElements[] = "<a class='btn btn-large' href='{$cancel[0]}'>{$i18n->t("Cancel")}</a>";

    return $this;
  }

  /**
   * Called if the form's auto-validation didn't came up with any errors.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return this
   */
  protected function valid() {
    global $i18n;

    // Add alert so the user knows that the deletion request was successful.
    $this->alerts .= new Alert(
      $i18n->t("You’ve successfully requested the deletion of this content for the reason: “{reason}”", [
        "reason" => $this->selectReason->options[$this->selectReason->value],
      ]),
      $i18n->t("Successfully Requested Deletion"),
      Alert::SEVERITY_SUCCESS
    );

    // Include additional information if the deletion request's reason requires it.
    $info = null;
    switch ($this->selectReason->value) {
      case DeletionRequest::REASON_DUPLICATE:
        $info = $this->inputDuplicateURL->value;
        break;

      case DeletionRequest::REASON_OTHER:
        $info = $this->inputOtherExplanation->value;
        break;
    }

    // The concrete class has to update it's database record to include the reference to the newly created deletion
    // request. The call to DeletionRequest::request will return the just inserted identifier.
    $this->storeDeletionRequestIdentifier(DeletionRequest::request($this->selectReason->value, $info, $this->languageLinks));

    return $this;
  }

}
