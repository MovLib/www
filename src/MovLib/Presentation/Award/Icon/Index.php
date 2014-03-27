<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link http://movlib.org/ MovLib}.
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
namespace MovLib\Presentation\Award\Icon;

use \MovLib\Data\Award;
use \MovLib\Data\User\User;
use \MovLib\Presentation\Partial\DateTime;
use \MovLib\Presentation\Redirect\SeeOther as SeeOtherRedirect;

/**
 * Image details presentation for a award's logo.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Index extends \MovLib\Presentation\Award\AbstractBase {


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new award logo presentation.
   *
   * @throws \MovLib\Exception\DatabaseException
   * @throws \MovLib\Presentation\Redirect\SeeOther
   */
  public function __construct() {
    $this->award = new Award((integer) $_SERVER["AWARD_ID"]);

    // Redirect to award edit form, if there is no logo.
    if ($this->award->imageExists === false) {
      throw new SeeOtherRedirect($this->intl->r("/award/{0}/edit", [ $this->award->id ]));
    }

    $routeArgs = [ $this->award->id ];

    $this->initPage($this->intl->t("Logo"));
    $this->pageTitle = $this->intl->t("Logo of {0}", [
      "<span property='about' typeof='Corporation'><a href='{$this->award->route}' property='url'>" .
        "<span property='name'>{$this->award->name}</span>" .
      "</a></span>"
    ]);
    $this->initLanguageLinks("/award/{0}/logo", $routeArgs);
    $this->initAwardBreadcrumb();
    $this->sidebarInit();

    // Modify sidebar items.
    $this->sidebarNavigation->menuitems[0] = [ $this->award->route, $this->intl->t("Back to Award"), [ "class" => "ico ico-award" ] ];
    $this->sidebarNavigation->menuitems[count($this->sidebarNavigation->menuitems)] = [ $this->intl->r("/award/{0}/logo/delete", $routeArgs), $this->intl->t("Delete Icon"), [ "class" => "ico ico-delete" ] ];

    // Initialize CSS class, schema and stylesheet.
    $this->bodyClasses    .= " imagedetails";
    $this->schemaType      = "ImageObject";
    $kernel->stylesheets[] = "imagedetails";
  }

  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  protected function getPageContent() {
    $uploader    = new User(User::FROM_ID, $this->award->uploaderId);
    $dateTime    = new DateTime($this->award->changed, [ "property" => "uploadDate" ]);
    $description = "<dt>{$this->intl->t("Description")}</dt>";
    if ($this->award->imageDescription) {
      $description .= "<dd property='description'>{$this->htmlDecode($this->award->imageDescription)}</dd>";
    }
    else {
      $description .= "<dd>{$this->intl->t(
        "No description available, {0}add one{1}?",
        [ "<a href='{$this->intl->r("/award/{0}/edit", [ $this->award->id ])}'>", "</a>" ]
      )}</dd>";
    }
    return
    "<meta property='representativeOfPage' content='true'>" .
        // TraitDeletionRequest::getDeletionRequestedAlert($this->image->deletionId) .
        "<div class='r wrapper'>" .
          "<dl class='s s7 description'>" .
            $description .
            "<dt>{$this->intl->t("Provided by")}</dt><dd><a href='{$uploader->route}' property='accountablePerson'>{$uploader->name}</a></dd>" .
            "<dt>{$this->intl->t("Dimensions")}</dt><dd>{$this->intl->t("{width} × {height}", [
              "width"  => "<span property='width'>{$this->award->width}&nbsp;<abbr title='{$this->intl->t("Pixel")}'>px</abbr></span>",
              "height" => "<span property='height'>{$this->award->height}&nbsp;<abbr title='{$this->intl->t("Pixel")}'>px</abbr></span>",
            ])}</dd>" .
            "<dt>{$this->intl->t("File size")}</dt><dd property='contentSize'>{$this->intl->t("{0,number} {1}", $this->formatBytes($this->award->filesize))}</dd>" .
            "<dt>{$this->intl->t("Upload on")}</dt><dd>{$dateTime}</dd>" .
          "</dl>" .
          "<div class='s s3 tac image'>{$this->getImage(
            $this->award->getStyle(Award::STYLE_SPAN_03),
            false,
            [ "property" => "thumbnailUrl" ]
          )}</div>" .
        "</div>" .
      "</div>"
    ;
  }
}
