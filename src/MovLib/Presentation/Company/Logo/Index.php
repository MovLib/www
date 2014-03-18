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
namespace MovLib\Presentation\Company\Logo;

use \MovLib\Data\Company\FullCompany;
use \MovLib\Data\User\User;
use \MovLib\Presentation\Partial\DateTime;
use \MovLib\Presentation\Redirect\SeeOther as SeeOtherRedirect;

/**
 * Image details presentation for a company's logo.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Index extends \MovLib\Presentation\Company\AbstractBase {


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new company logo presentation.
   *
   * @global \MovLib\Kernel $kernel
   * @global \MovLib\Data\I18n $i18n
   * @throws \MovLib\Exception\DatabaseException
   * @throws \MovLib\Presentation\Redirect\SeeOther
   */
  public function __construct() {
    global $i18n, $kernel;

    $this->company = new FullCompany((integer) $_SERVER["COMPANY_ID"]);

    // Redirect to company edit form, if there is no logo.
    if ($this->company->imageExists === false) {
      throw new SeeOtherRedirect($i18n->r("/company/{0}/edit", [ $this->company->id ]));
    }

    $routeArgs = [ $this->company->id ];

    $this->initPage($i18n->t("Logo"));
    $this->pageTitle = $i18n->t("Logo of {0}", [
      "<span property='about' typeof='Corporation'><a href='{$this->company->route}' property='url'>" .
        "<span property='name'>{$this->company->name}</span>" .
      "</a></span>"
    ]);
    $this->initLanguageLinks("/company/{0}/logo", $routeArgs);
    $this->initCompanyBreadcrumb();
    $this->sidebarInit();

    // Modify sidebar items.
    $this->sidebarNavigation->menuitems[0] = [ $this->company->route, $i18n->t("Back to Company"), [ "class" => "ico ico-company" ] ];
    $this->sidebarNavigation->menuitems[count($this->sidebarNavigation->menuitems)] = [ $i18n->r("/company/{0}/logo/delete", $routeArgs), $i18n->t("Delete Logo"), [ "class" => "ico ico-delete" ] ];

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
    global $i18n;
    $uploader    = new User(User::FROM_ID, $this->company->uploaderId);
    $dateTime    = new DateTime($this->company->changed, [ "property" => "uploadDate" ]);
    $description = "<dt>{$i18n->t("Description")}</dt>";
    if ($this->company->imageDescription) {
      $description .= "<dd property='description'>{$this->htmlDecode($this->company->imageDescription)}</dd>";
    }
    else {
      $description .= "<dd>{$i18n->t(
        "No description available, {0}add one{1}?",
        [ "<a href='{$i18n->r("/company/{0}/edit", [ $this->company->id ])}'>", "</a>" ]
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
              "width"  => "<span property='width'>{$this->company->width}&nbsp;<abbr title='{$i18n->t("Pixel")}'>px</abbr></span>",
              "height" => "<span property='height'>{$this->company->height}&nbsp;<abbr title='{$i18n->t("Pixel")}'>px</abbr></span>",
            ])}</dd>" .
            "<dt>{$i18n->t("File size")}</dt><dd property='contentSize'>{$i18n->t("{0,number} {1}", $this->formatBytes($this->company->filesize))}</dd>" .
            "<dt>{$i18n->t("Upload on")}</dt><dd>{$dateTime}</dd>" .
          "</dl>" .
          "<div class='s s3 tac image'>{$this->getImage(
            $this->company->getStyle(FullCompany::STYLE_SPAN_03),
            false,
            [ "property" => "thumbnailUrl" ]
          )}</div>" .
        "</div>" .
      "</div>"
    ;
  }
}
