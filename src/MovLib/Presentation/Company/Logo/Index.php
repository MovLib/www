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

use \MovLib\Data\Company;
use \MovLib\Data\User\User;
use \MovLib\Partial\DateTime;
use \MovLib\Exception\SeeOtherException;

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


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  protected function getPageContent() {
    $uploader    = new User($this->diContainerHTTP, User::FROM_ID, $this->company->uploaderId);
    $dateTime    = new DateTime($this->diContainerHTTP, $this->company->changed, [ "property" => "uploadDate" ]);
    $description = "<dt>{$this->intl->t("Description")}</dt>";
    if ($this->company->imageDescription) {
      $description .= "<dd property='description'>{$this->htmlDecode($this->company->imageDescription)}</dd>";
    }
    else {
      $description .= "<dd>{$this->intl->t(
        "No description available, {0}add one{1}?",
        [ "<a href='{$this->intl->r("/company/{0}/edit", [ $this->company->id ])}'>", "</a>" ]
      )}</dd>";
    }

    // @todo: display real company logo
    $companyLogo =
      "<img alt='' height='220' src='{$this->getExternalURL("asset://img/logo/vector.svg")}' width='220'>"
    ;

    return
      "<meta property='representativeOfPage' content='true'>" .
        // TraitDeletionRequest::getDeletionRequestedAlert($this->image->deletionId) .
        "<div class='r wrapper'>" .
          "<dl class='s s7 description'>" .
            $description .
            "<dt>{$this->intl->t("Provided by")}</dt><dd><a href='{$uploader->route}' property='accountablePerson'>{$uploader->name}</a></dd>" .
            "<dt>{$this->intl->t("Dimensions")}</dt><dd>{$this->intl->t("{width} × {height}", [
              "width"  => "<span property='width'>{$this->company->width}&nbsp;<abbr title='{$this->intl->t("Pixel")}'>px</abbr></span>",
              "height" => "<span property='height'>{$this->company->height}&nbsp;<abbr title='{$this->intl->t("Pixel")}'>px</abbr></span>",
            ])}</dd>" .
            "<dt>{$this->intl->t("File size")}</dt><dd property='contentSize'>{$this->intl->t("{0,number} Bytes", [ $this->company->filesize ])}</dd>" .
            "<dt>{$this->intl->t("Upload on")}</dt><dd>{$dateTime}</dd>" .
          "</dl>" .
          "<div class='s s3 tac image'>{$companyLogo}</div>" .
        "</div>" .
      "</div>"
    ;
  }

  /**
   * Instantiate new company logo presentation.
   *
   * @throws \MovLib\Exception\DatabaseException
   * @throws \MovLib\Presentation\Redirect\SeeOther
   */
  public function init() {
    $this->company = (new Company($this->diContainerHTTP))->init((integer) $_SERVER["COMPANY_ID"]);

    // Redirect to company edit form, if there is no logo.
    if ($this->company->imageExists === false) {
      throw new SeeOtherException($this->intl->r("/company/{0}/edit", [ $this->company->id ]));
    }

    $routeArgs = [ $this->company->id ];

    $this->initPage($this->intl->t("Logo"));
    $this->pageTitle = $this->intl->t("Logo of {0}", [
      "<span property='about' typeof='Corporation'><a href='{$this->company->route}' property='url'>" .
        "<span property='name'>{$this->company->name}</span>" .
      "</a></span>"
    ]);
    $this->initLanguageLinks("/company/{0}/logo", $routeArgs);
    $this->initCompanyBreadcrumb();
    $this->sidebarInit();

    // Modify sidebar items.
    $this->sidebarNavigation->menuitems[0] = [ $this->company->route, $this->intl->t("Back to Company"), [ "class" => "ico ico-company" ] ];
    $this->sidebarNavigation->menuitems[count($this->sidebarNavigation->menuitems)] = [ $this->intl->r("/company/{0}/logo/delete", $routeArgs), $this->intl->t("Delete Logo"), [ "class" => "ico ico-delete" ] ];

    // Initialize CSS class, schema and stylesheet.
    $this->bodyClasses    .= " imagedetails";
    $this->schemaType      = "ImageObject";
  }

}
