<?php

/*!
 *  This file is part of {@link https://github.com/MovLib MovLib}.
 *
 *  Copyright © 2013-present {@link http://movlib.org/ MovLib}.
 *
 *  MovLib is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public
 *  License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later
 *  version.
 *
 *  MovLib is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 *  of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License along with MovLib.
 *  If not, see {@link http://www.gnu.org/licenses/ gnu.org/licenses}.
 */
namespace MovLib\Presentation\Person;

use \MovLib\Data\Person\Person;
use \MovLib\Data\User\User;
use \MovLib\Presentation\Partial\DateTime;

/**
 * Image details presentation for a person's photo.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Photo extends \MovLib\Presentation\Person\AbstractBase {

  /**
   * Instantiate new Person Photo presentation.
   *
   * @global \MovLib\Kernel $kernel
   * @global \MovLib\Data\I18n $i18n
   * @throws \MovLib\Exception\DatabaseException
   * @throws \MovLib\Presentation\Redirect\SeeOther
   */
  public function __construct() {
    global $i18n, $kernel;


    $this->person = new Person((integer) $_SERVER["PERSON_ID"]);

    // Redirect to person edit form, if there is no photo.
    if ($this->person->imageExists === false) {
      throw new SeeOther($i18n->r("/person/{0}/edit", [ $this->person->id ]));
    }

    $routeArgs = [ $this->person->id ];

    $this->initPage($i18n->t("Photo"));
    $this->pageTitle        = $i18n->t("Photo of {0}", [
      "<span property='about' typeof='Person'><a href='{$this->person->route}' property='url'>" .
        "<span property='name'>{$this->person->name}</span>" .
      "</a></span>"
    ]);
    $this->initLanguageLinks("/person/{0}/photo", $routeArgs);
    $this->initPersonBreadcrumb();
    $this->sidebarInit();

    // Modify sidebar items.
    $this->sidebarNavigation->menuitems[0] = [ $this->person->route, $i18n->t("Back to Person"), [ "class" => "ico ico-person" ] ];
    $this->sidebarNavigation->menuitems[count($this->sidebarNavigation->menuitems) - 1] = [ $i18n->r("/person/{0}/photo/delete", $routeArgs), $i18n->t("Delete"), [ "class" => "ico ico-delete" ] ];

    // Initialize CSS class, schema and stylesheet.
    $this->bodyClasses    .= " imagedetails";
    $this->schemaType      = "ImageObject";
    $kernel->stylesheets[] = "imagedetails";
  }

  /**
   * @inheritdoc
   */
  protected function getPageContent() {
    global $i18n, $kernel;
    $uploader    = new User(User::FROM_ID, $this->person->uploaderId);
    $dateTime    = new DateTime($this->person->changed, [ "property" => "uploadDate" ]);
    $description = "<dt>{$i18n->t("Description")}</dt>";
    if ($this->person->description) {
      $description .= "<dd property='description'>{$this->htmlDecode($this->person->description)}</dd>";
    }
    else {
      $description .= "<dd>{$i18n->t(
        "No description available, {0}add one{1}?",
        [ "<a href='{$i18n->r("/person/{0}/edit", [ $this->person->id ])}'>", "</a>" ]
      )}</dd>";
    }
    return
    "<meta property='representativeOfPage' content='true'>" .
        // TraitDeletionRequest::getDeletionRequestedAlert($this->image->deletionId) .
        "<div class='r wrapper'>" .
          "<dl class='s s7 description'>" .
            $description .
            "<dt>{$i18n->t("Provided by")}</dt><dd><a href='{$uploader->route}' property='accountablePerson'>{$uploader->name}</a></dd>" .
            "<dt>{$i18n->t("Dimensions")}</dt><dd>{$i18n->t("{width} × {height}", [
              "width"  => "<span property='width'>{$this->person->width}&nbsp;<abbr title='{$i18n->t("Pixel")}'>px</abbr></span>",
              "height" => "<span property='height'>{$this->person->height}&nbsp;<abbr title='{$i18n->t("Pixel")}'>px</abbr></span>",
            ])}</dd>" .
            "<dt>{$i18n->t("File size")}</dt><dd property='contentSize'>{$i18n->t("{0,number} {1}", $this->formatBytes($this->person->filesize))}</dd>" .
            "<dt>{$i18n->t("Upload on")}</dt><dd>{$dateTime}</dd>" .
          "</dl>" .
          "<div class='s s3 tac image'>{$this->getImage(
            $this->person->getStyle(Person::STYLE_SPAN_02),
            false,
            [ "property" => "thumbnailUrl" ]
          )}</div>" .
        "</div>" .
      "</div>"
    ;
  }
}
