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

use \MovLib\Data\Image\PersonImage;
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
    parent::__construct();

    $routeArgs = [ $this->person->id ];

    // Redirect to person edit form, if there is no photo.
    if ($this->person->displayPhoto->imageExists === false) {
      throw new SeeOther($this->routeEdit);
    }

    // Modify sidebar items.
    $this->sidebarNavigation->menuitems[0] = [ $this->person->route, $i18n->t("Back to Person"), [ "class" => "ico ico-person" ] ];
    $this->sidebarNavigation->menuitems[count($this->sidebarNavigation->menuitems) - 1] = [ $i18n->r("/person/{0}/photo/delete", $routeArgs), $i18n->t("Delete"), [ "class" => "ico ico-delete" ] ];

    // Set correct breadcrumb title.
    $this->breadcrumbTitle = $i18n->t("Photo");

    // Initialize language links.
    $this->initLanguageLinks("/person/{0}/photo", $routeArgs);

    // Initialize page titles.
    $title = $i18n->t("Photo of {person_name}");
    $search = "{person_name}";
    $this->initPage(str_replace($search, $this->person->name, $title));
    $this->pageTitle = str_replace(
      $search,
      "<span itemscope itemtype='http://schema.org/Person'><a href='{$this->person->route}' itemprop='url'><span itemprop='name'>{$this->person->name}</span></a></span>",
      $title
    );

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
    $uploader    = new User(User::FROM_ID, $this->person->displayPhoto->uploaderId);
    $dateTime    = new DateTime($this->person->displayPhoto->changed, [ "itemprop" => "uploadDate" ]);
    $description = "<dt>{$i18n->t("Description")}</dt>";
    if ($this->person->displayPhoto->description) {
      $description .= "<dd itemprop='description'>{$this->htmlDecode($this->person->displayPhoto->description)}</dd>";
    }
    else {
      $description .= "<dd>{$i18n->t("No description available, {0}add one{1}?", [ "<a href='{$this->routeEdit}'>", "</a>" ])}</dd>";
    }
    return
    "<meta itemprop='representativeOfPage' content='true'>" .
        // TraitDeletionRequest::getDeletionRequestedAlert($this->image->deletionId) .
        "<div class='r wrapper'>" .
          "<dl class='s s7 description'>" .
            $description .
            "<dt>{$i18n->t("Provided by")}</dt><dd><a href='{$uploader->route}' itemprop='accountablePerson'>{$uploader->name}</a></dd>" .
            "<dt>{$i18n->t("Dimensions")}</dt><dd>{$i18n->t("{width} × {height}", [
              "width"  => "<span itemprop='width'>{$this->person->displayPhoto->width}&nbsp;<abbr title='{$i18n->t("Pixel")}'>px</abbr></span>",
              "height" => "<span itemprop='height'>{$this->person->displayPhoto->height}&nbsp;<abbr title='{$i18n->t("Pixel")}'>px</abbr></span>",
            ])}</dd>" .
            "<dt>{$i18n->t("File size")}</dt><dd itemprop='contentSize'>{$i18n->t("{0,number} {1}", $this->formatBytes($this->person->displayPhoto->filesize))}</dd>" .
            "<dt>{$i18n->t("Upload on")}</dt><dd>{$dateTime}</dd>" .
          "</dl>" .
          "<div class='s s3 tac image'>{$this->getImage(
            $this->person->displayPhoto->getStyle(PersonImage::STYLE_SPAN_03),
            false,
            [ "itemprop" => "thumbnailUrl" ]
          )}</div>" .
        "</div>" .
      "</div>"
    ;
  }
}
