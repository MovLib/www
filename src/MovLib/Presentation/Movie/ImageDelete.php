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
namespace MovLib\Presentation\Movie;

use \MovLib\Data\Deletion;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\Form;
use \MovLib\Presentation\Partial\FormElement\InputHTML;
use \MovLib\Presentation\Partial\FormElement\InputSubmit;
use \MovLib\Presentation\Redirect\SeeOther as SeeOtherRedirect;

/**
 * Delete given image.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class ImageDelete extends \MovLib\Presentation\Movie\Image {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The reason for the deletion.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputHTML
   */
  protected $reason;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  protected function getPageContent() {
    return $this->form;
  }

  /**
   * Initialize movie image edit page.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\User\Session $session
   * @return this
   * @throws \MovLib\Presentation\Error\NotFound
   */
  protected function initImagePage() {
    global $i18n, $session;

    $session->checkAuthorization($i18n->t("Only authenticated users can request the deletion of an {image_type_name}.", [
      "image_type_name" => $this->imageTypeName
    ]));

    // Try to load the movie with the identifier from the request and set the breadcrumb title.
    $this->initMoviePage($i18n->t("Delete {image_type_name}", [ "image_type_name" => $this->imageTypeName ]));

    // Create absolute class name for the image and try to load it with the identifier from the request.
    $class       = "\\MovLib\\Data\\Image\\Movie{$this->imageClassName}";
    $this->image = new $class($this->movie->id, $this->movie->displayTitleWithYear, (integer) $_SERVER["IMAGE_ID"]);

    // Initialize the page without anchor in it and then set the page title with the anchors.
    $this->initPage($i18n->t("Delete {title} {image_type_name} {id}", [
      "title" => $this->movie->displayTitleWithYear,
      "id" => $i18n->format("{0,number}", [ $this->image->id ]),
      "image_type_name" => $this->imageTypeName,
    ]));
    $this->pageTitle = $i18n->t("Delete {title} {image_type_name} {id}", [
        "title"           => "<a href='{$this->movie->route}'>{$this->movie->displayTitleWithYear}</a>",
        "id"              => "<a href='{$i18n->r("/movie/{0}/{$this->routeKey}/{1}", [ $this->movie->id, $this->image->id])}'>{$this->image->id}</a>",
        "image_type_name" => $this->imageTypeName,
    ]);

    // Initialize the language links for this page.
    $this->initLanguageLinks("/movie/{0}/{$this->routeKey}/{1}/delete", [ $this->movie->id, $this->image->id ]);

    // Add the necessary trails to the breadcrumb.
    $this->breadcrumb->menuitems[] = [ $i18n->rp("/movie/{0}/{$this->routeKeyPlural}", [ $this->movie->id]), $this->imageTypeNamePlural];
    $this->breadcrumb->menuitems[] = [ $i18n->r("/movie/{0}/{$this->routeKey}/{1}", [ $this->movie->id, $this->image->id ]), "{$this->imageTypeName} {$this->image->id}" ];

    // Instantiate the textarea for stating the deletion reason.
    $this->reason = new InputHTML("reason", $i18n->t("Reason"), null, [
      "placeholder" => $i18n->t("Please state why this {image_type_name} should be deleted…", [
        "image_type_name" => $this->imageTypeName
      ]),
      "required",
    ]);

    // Instantiate the delete form.
    $this->form = new Form($this, [ $this->reason ]);
    $this->form->actionElements[] = new InputSubmit($i18n->t("Delete"), [
      "class" => "btn btn-danger btn-large"
    ]);
    $this->form->actionElements[] = "<a class='btn btn-large' href='{$i18n->r("/movie/{0}/{$this->routeKey}/{1}", [
      $this->movie->id, $this->image->id
    ])}'>{$i18n->t("Cancel")}</a>";

    // Lastly initialize the sidebar.
    return $this->initSidebar();
  }

  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   */
  protected function valid() {
    global $i18n, $kernel;

    // @todo Check if we already have a deletion request for this content.

    Deletion::request($this->title, $this->reason->value, $this->image->route);
    $kernel->alerts .= new Alert($i18n->t("You successfully requested the deletion of this {image_type_name} with the reason: {reason}", [
      "image_type_name" => $this->imageTypeName,
      "reason" => $kernel->htmlDecode($this->reason->value),
    ]), $i18n->t("Successfully Requested Deletion"), Alert::SEVERITY_SUCCESS);
    throw new SeeOtherRedirect($this->image->route);
  }

}
