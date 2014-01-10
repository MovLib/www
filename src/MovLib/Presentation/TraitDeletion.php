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
use \MovLib\Presentation\Redirect\SeeOther as SeeOtherRedirect;

/**
 * Implements the form elements that are necessary for any deletion presentation.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
trait TraitDeletion {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The deletion type select form element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\Select
   */
  protected $selectReason;

  /**
   * The reason why this content should be deleted.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputHTML
   */
  protected $inputAdditionalInfo;

  /**
   * The URL of the duplicated content.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputURL
   */
  protected $inputDuplicateURL;

  /**
   * The route key of the content that should be deleted.
   *
   * @var string
   */
  private $deletionRouteKey;

  /**
   * The route arguments of the content that should be deleted.
   *
   * @var array
   */
  private $deletionRouteArgs;

  /**
   * Whether the route is in plural form or not.
   *
   * @var boolean
   */
  private $deletionRoutePlural;


  // ------------------------------------------------------------------------------------------------------------------- Abstract Methods


  /**
   * Stores the deletion request's identifier in the database record that should be deleted.
   *
   * @param integer $deletionRequestIdentifier
   *   The unique identifier of the deletion request.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  protected abstract function storeDeletionRequestIdentifier($deletionRequestIdentifier);


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  protected function getTraitDeletionPageContent() {
    return
      $this->form->open() .
      $this->selectReason .
      "<div id='info-" . DeletionRequest::REASON_DUPLICATE . "' class='hidden info'>{$this->inputDuplicateURL}</div>" .
      "<div id='info-" . DeletionRequest::REASON_OTHER . "' class='hidden info'>{$this->inputAdditionalInfo}</div>" .
      $this->form->close()
    ;
  }


  /**
   * Initialize the deletion form.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @return this
   * @throws \LogicException
   */
  protected function initDeletion() {
    global $i18n, $kernel;
    // @devStart
    // @codeCoverageIgnoreStart
    if (!property_exists($this, "form")) {
      throw new \LogicException("You have to include the form trait in order to include the deletion form trait.");
    }
    if (empty($this->languageLinks)) {
      throw new \LogicException("You have to initialize the language links before you initialize the deletion form.");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    $kernel->javascripts[]        = "Deletion";
    $this->selectReason           = new Select("reason", $i18n->t("Reason"), DeletionRequest::getTypes(), null, [ "required" ]);
    $this->inputDuplicateURL      = new InputURL("duplicate", $i18n->t("URL"));
    $this->inputAdditionalInfo    = new InputHTML("other", $i18n->t("Explanation"), null, [
      "placeholder" => $i18n->t("Please explain why this content should be deleted…"),
    ]);
    $this->inputDuplicateURL->setHelp($i18n->t("Enter the URL of the existing content."));
    $this->form                   = new Form($this, [ $this->selectReason, $this->inputDuplicateURL, $this->inputAdditionalInfo ]);
    $this->form->actionElements[] = new InputSubmit($i18n->t("Delete"), [
      "class" => "btn btn-danger btn-large"
    ]);
    $this->form->actionElements[] = "<a class='btn btn-large' href='{$this->languageLinks[$i18n->languageCode]}'>{$i18n->t("Cancel")}</a>";

    return $this;
  }

  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   */
  protected function valid() {
    global $i18n, $kernel;

    $info = null;
    switch ($this->selectReason->value) {
      case DeletionRequest::REASON_DUPLICATE:
        $info = $this->inputDuplicateURL->value;
        break;

      case DeletionRequest::REASON_OTHER:
        $info = $this->inputAdditionalInfo->value;
        break;
    }

    $kernel->alerts .= new Alert($i18n->t("You’ve successfully requested the deletion of this content for the reason: “{reason}”", [
      "reason" => $this->selectReason->options[$this->selectReason->value],
    ]), $i18n->t("Successfully Requested Deletion"), Alert::SEVERITY_SUCCESS);

    if ($this->deletionRoutePlural === true) {
      $route = $i18n->rp($this->deletionRouteKey, $this->deletionRouteArgs);
    }
    else {
      $route = $i18n->r($this->deletionRouteKey, $this->deletionRouteArgs);
    }
    $this->storeDeletionRequestIdentifier(DeletionRequest::request($this->selectReason->value, $info, $this->languageLinks));
    throw new SeeOtherRedirect($route);
  }

}
