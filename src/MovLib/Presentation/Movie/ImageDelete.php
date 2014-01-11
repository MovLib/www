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

use \MovLib\Data\DeletionRequest;
use \MovLib\Presentation\Partial\Alert;
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
  use \MovLib\Presentation\TraitDeletionRequest;

  /**
   * @inheritdoc
   */
  protected function getPageContent() {
    if ($this->deletionRequestedAlert) {
      return $this->deletionRequestedAlert;
    }
    return $this->getDeletionRequestForm();
  }

  /**
   * Initialize movie image edit page.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @global \MovLib\Data\User\Session $session
   * @return this
   * @throws \MovLib\Presentation\Error\NotFound
   */
  protected function initImagePage() {
    global $i18n, $kernel, $session;

    $session->checkAuthorization($i18n->t("Only authenticated users can request the deletion of an {image_type_name}.", [
      "image_type_name" => $this->imageTypeName
    ]));

    // Try to load the movie with the identifier from the request and set the breadcrumb title.
    $this->initMoviePage($i18n->t("Delete"));

    // Create absolute class name for the image and try to load it with the identifier from the request.
    $class       = "\\MovLib\\Data\\Image\\Movie{$this->imageClassName}";
    $this->image = new $class($this->movie->id, $this->movie->displayTitleWithYear, (integer) $_SERVER["IMAGE_ID"]);

    // Initialize the page without anchor in it and then set the page title with the anchors.
    $this->initPage($i18n->t("Delete {title} {image_type_name} {id}", [
      "title"           => $this->movie->displayTitleWithYear,
      "id"              => $i18n->format("{0,number}", [ $this->image->id ]),
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

    // @todo Display full deletion request information and form if the user is an admin or has the reputation to do so.
    if ($this->image->deletionId) {
      try {
        $deletionRequest              = new DeletionRequest($this->image->deletionId);
        $this->deletionRequestedAlert = new Alert(
          "<p>{$i18n->t("{user} has requested that this {image_type_name} should be deleted for the reason: “{reason}”", [
            "user"            => "<a href='{$deletionRequest->user->route}'>{$deletionRequest->user->name}</a>",
            "image_type_name" => $this->imageTypeName,
            "reason"          => $deletionRequest->reason,
          ])}</p>",
          $i18n->t("Deletion Requested"),
          Alert::SEVERITY_ERROR
        );
        switch ($deletionRequest->reasonId) {
          case DeletionRequest::REASON_OTHER:
            $this->deletionRequestedAlert->message .= "<p>{$i18n->t("The following additional information was supplied by {user_name}: {additional_info}", [
              "additional_info" => "</p><blockquote>{$kernel->htmlDecode($deletionRequest->info)}</blockquote>",
              "user_name"       => $deletionRequest->user->name,
            ])}";
            break;

          case DeletionRequest::REASON_DUPLICATE:
            $this->deletionRequestedAlert->message .= "<br>{$i18n->t("The {image_type_name} is a duplicate of {0}this image{1}.", [
              "<a href='{$deletionRequest->info}'>", "</a>"
            ])}";
            break;
        }
      }
      catch (\OutOfBoundsException $e) {
        $this->initDeletionRequest();
      }
    }
    // Initialize the deletion form.
    else {
      $this->initDeletionRequest();
    }

    // Lastly initialize the sidebar.
    return $this->initSidebar();
  }

  /**
   * Stores the deletion request's identifier in the movies images table.
   *
   * @global \MovLib\Data\Database $db
   * @param integer $deletionRequestIdentifier
   *   The unique identifier of the deletion request.
   * @throws \MovLib\Exception\DatabaseException
   * @throws \MovLib\Presentation\Redirect\SeeOther
   */
  protected function storeDeletionRequestIdentifier($deletionRequestIdentifier) {
    global $db;
    $db->query("UPDATE `movies_images` SET `deletion_id` = ?", "d", [ $deletionRequestIdentifier ]);
    throw new SeeOtherRedirect($this->image->route);
  }

}
