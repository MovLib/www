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
namespace MovLib\Presentation;

use \MovLib\Data\DeletionRequest;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\Form;
use \MovLib\Presentation\Partial\FormElement\InputHTML;
use \MovLib\Presentation\Partial\FormElement\InputSubmit;
use \MovLib\Presentation\Partial\FormElement\InputURL;
use \MovLib\Presentation\Partial\FormElement\Select;

/**
 * Add deletion request form to presentation.
 *
 * @see \MovLib\Presentation\AbstractBase
 *
 * @method string a($route, $text, array $attributes = null, $ignoreQuery = true)
 * @method this addClass($class, array &$attributes = null)
 * @method string collapseWhitespace($string)
 * @method string expandTagAttributes(array $attributes)
 * @method string getImage($style, $route = true, array $attributes = null, array $anchorAttributes = null)
 * @method string htmlDecode($text)
 * @method string htmlDecodeEntities($text)
 * @method string htmlEncode($text)
 * @method string lang($lang)
 * @method string normalizeLineFeeds($text)
 * @method string placeholder($text)
 *
 * @see \MovLib\Presentation\AbstractPresenter
 *
 * @property string $alerts
 * @property string $bodyClasses
 * @property \MovLib\Presentation\Partial\Navigation $breadcrumb
 * @property string $breadcrumbTitle
 * @property string $contentAfter
 * @property string $contentBefore
 * @property string $headingBefore
 * @property string $headingAfter
 * @property string $headingSchemaProperty
 * @property-read string $id
 * @property-read array $languageLinks
 * @property-read array $namespace
 * @property-read string $pageTitle
 * @property-read string $schemaType
 * @property-read string $title
 * @method string getContent()
 * @method string getFooter()
 * @method string getHeader()
 * @method string getHeadTitle()
 * @method string getPresentation()
 * @method string getMainContent()
 * @method this initBreadcrumb()
 * @method this initLanguageLinks($route, array $args = null, $plural = false, $query = null)
 * @method this initPage($title)
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
trait TraitDeletionRequest {
  use \MovLib\Presentation\TraitForm;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Alert message explaining that the deletion of this image was requested.
   *
   * @var null|\MovLib\Presentation\Partial\Alert
   */
  protected $deletionRequestedAlert;

  /**
   * The deletion request's unique identifier.
   *
   * @var null|integer
   */
  protected $deletionRequestId;

  /**
   * Submit input to delete the content.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputSubmit
   */
  protected $inputDelete;

  /**
   * Submit input to discard the deletion request.
   *
   * @var null|\MovLib\Presentation\Partial\FormElement\InputSubmit
   */
  protected $inputDiscard;

  /**
   * Input URL form element where the user has to paste the full URL of the content that's already available.
   *
   * @var null|\MovLib\Presentation\Partial\FormElement\InputURL
   */
  protected $inputDuplicateURL;

  /**
   * Input HTML form element where the user has to write an explanation if none of the predefined reasons is used.
   *
   * @var null|\MovLib\Presentation\Partial\FormElement\InputHTML
   */
  protected $inputOtherExplanation;

  /**
   * Select form element containing available reason's for a deletion request.
   *
   * @var null|\MovLib\Presentation\Partial\FormElement\Select
   */
  protected $selectReason;


  // ------------------------------------------------------------------------------------------------------------------- Abstract Methods


  /**
   * Delete the content.
   *
   * @return this
   */
  abstract protected function delete();

  /**
   * Remove the deletion request's identifier from the database record.
   *
   * @param integer $id
   *   The unique identifier of the deletion request.
   * @return this
   */
  abstract protected function removeDeletionRequestIdentifier($id);

  /**
   * Stores the deletion request's identifier in the database record that should be deleted.
   *
   * @param integer $id
   *   The unique identifier of the deletion request.
   * @return this
   */
  abstract protected function storeDeletionRequestIdentifier($id);


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get unified deletion requested alert.
   *
   * @param null|integer $id
   *   The deletion request's unique identifier.
   * @param boolean $extended [optional]
   *   Whether to display extended deletion request information or not, defaults to no extended information.
   * @return \MovLib\Presentation\Partial\Alert|null
   *   Unified deletion requested alert or <code>NULL</code> if the deletion request identifier couldn't be found or
   *   is <code>NULL</code> itself.
   * @throws \OutOfBoundsException
   */
  public static function getDeletionRequestedAlert($id, $extended = false) {
    // If the given identifier is NULL simply return and do nothing. This is mainly for calling presentations which
    // don't care if there is an alert or not and only want to display it if there is one.
    if (!$id) {
      return;
    }

    // Try to load the deletion request.
    try {
      $deletionRequest = new DeletionRequest($id);

      // Create the default deletion request alert message.
      $alert = new Alert(
        "<p>{$this->intl->t("{user} has requested that this content should be deleted for the reason: “{reason}”", [
          "user"   => "<a href='{$deletionRequest->user->route}'>{$deletionRequest->user->name}</a>",
          "reason" => $deletionRequest->reason,
        ])}</p>",
        $this->intl->t("Deletion Requested"),
        Alert::SEVERITY_ERROR
      );

      // Some predefined reasons need additional information.
      if ($extended === true) {
        $lang = $this->lang($deletionRequest->languageCode);
        switch ($deletionRequest->reasonId) {
          case DeletionRequest::REASON_OTHER:
            $alert->message .= "<blockquote><div{$lang}>{$this->htmlDecode($deletionRequest->info)}</div><cite>{$deletionRequest->user->name}</cite></blockquote>";
            break;

          case DeletionRequest::REASON_DUPLICATE:
            $alert->message .= "<p>{$this->intl->t("The content is a duplicate of {0}this content{1}.", [
              "<a href='{$deletionRequest->info}'{$lang}>", "</a>"
            ])}</p>";
            break;
        }
      }

      return $alert;
    }
    catch (\OutOfBoundsException $e) {
      // Do nothing! The deletion request might have been discarded right now.
    }
  }

  /**
   * Get the presentation's page content.
   *
   * @return string
   *   The presentation's page content.
   */
  protected function getPageContent() {
    // We already have a deletion request if our init method has built the alert message.
    if ($this->deletionRequestedAlert) {
      $content = "{$this->deletionRequestedAlert}{$this->form}";
    }
    // Display the deletion request form if we have no request for this content yet.
    else {
      $duplicate = DeletionRequest::REASON_DUPLICATE;
      $other     = DeletionRequest::REASON_OTHER;
      $content   = "{$this->form->open()}{$this->selectReason}<div id='info-{$duplicate}' class='hidden info'>{$this->inputDuplicateURL}</div><div id='info-{$other}' class='hidden info'>{$this->inputOtherExplanation}</div>{$this->form->close()}";
    }
    return $content;
  }

  /**
   * Initialize the deletion request form.
   *
   * @param null|integer $id
   *   The possible deletion request identifier of the content. This will automatically construct the deletion request
   *   alert for your presentation if the identifier matches an existing deletion request.
   * @return this
   * @throws \LogicException
   */
  protected function initDeletionRequest($id) {
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

    // Try to load the deletion request if an identifier was passed to the init method. If we were able to load an alert
    // message for this content abort initialization and we're done. Please also note that this alert message always
    // contains extended information because this is the actual deletion page.
    if ($id && ($this->deletionRequestedAlert = self::getDeletionRequestedAlert($id, true))) {
      // @todo Check user reputation and decide based on that if we should display the real deletion form. As of now
      //       we limit it to administrators only.
      if ($session->isAdmin() === true) {
        $this->deletionRequestId      = $id;
        $this->inputDelete            = new InputSubmit($this->intl->t("Delete"), [
          "class" => "btn btn-large btn-danger",
          "id"    => "delete",
          "name"  => "delete",
          "title" => $this->intl->t("Delete the content and resolve the deletion request."),
        ]);
        $this->inputDiscard           = new InputSubmit($this->intl->t("Discard"), [
          "class" => "btn btn-large",
          "id"    => "discard",
          "name"  => "discard",
          "title" => $this->intl->t("Discard this deletion request."),
        ]);
        $this->form                   = new Form($this, null, "handle-deletion", "validateDeletion");
        $this->form->actionElements[] = $this->inputDelete;
        $this->form->actionElements[] = $this->inputDiscard;
      }
    }
    else {
      // Conditionally show and require form elements, depending on selected reason.
      $kernel->javascripts[] = "DeletionRequest";

      // Initialize the reason select form element with all available deletion request reasons, the user is required to
      // select an option before submitting the deletion request.
      $this->selectReason = new Select("reason", $this->intl->t("Reason"), DeletionRequest::getTypes(), null, [ "required" ]);

      // The user has to give us the URL of the already existing content that is duplicated.
      $this->inputDuplicateURL = new InputURL("duplicate", $this->intl->t("URL"));
      $this->inputDuplicateURL->setHelp($this->intl->t("Enter the URL of the existing content."));

      // The user has to explain why this particular content should be deleted if none of the predefined reasons is
      // sufficient for the deletion request.
      $this->inputOtherExplanation = new InputHTML("other", $this->intl->t("Explanation"), null, [
        "placeholder" => $this->intl->t("Please explain why this content should be deleted…"),
      ]);

      // Initialize the actual form and include a link back
      $this->form                   = new Form($this, [ $this->selectReason, $this->inputDuplicateURL, $this->inputOtherExplanation ]);
      $this->form->actionElements[] = new InputSubmit($this->intl->t("Delete"), [ "class" => "btn btn-danger btn-large" ]);
      $cancel                       = end($this->breadcrumb->menuitems);
      $this->form->actionElements[] = "<a class='btn btn-large' href='{$cancel[0]}'>{$this->intl->t("Cancel")}</a>";
    }

    return $this;
  }

  /**
   * Called if the form's auto-validation didn't came up with any errors.
   *
   * @return this
   */
  protected function valid() {
    // Add alert so the user knows that the deletion request was successful.
    $this->alerts .= new Alert(
      $this->intl->t("You’ve successfully requested the deletion of this content for the reason: “{reason}”", [
        "reason" => $this->selectReason->options[$this->selectReason->value],
      ]),
      $this->intl->t("Successfully Requested Deletion"),
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

  /**
   * Validate the deletion request.
   *
   * @return this
   */
  public function validateDeletion() {
    // @todo Check user reputation instead of limiting action to administrators.
    if ($session->isAdmin() === false) {
      $this->alerts .= new Alert(
        $this->intl->t("Only administrators can delete content."),
        $this->intl->t("Not Allowed!"),
        Alert::SEVERITY_ERROR
      );
      return $this;
    }

    // Delete the deletion request from the database.
    if (isset($_POST[$this->inputDiscard->attributes["name"]])) {
      DeletionRequest::discard($this->deletionRequestId);
    }
    // Delete the content but keep the deletion request for reference (so we know why this content was deleted and
    // who deleted it).
    elseif (isset($_POST[$this->inputDelete->attributes["name"]])) {
      $this->delete();
    }

    return $this;
  }

}
