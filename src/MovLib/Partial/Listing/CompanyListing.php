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
namespace MovLib\Partial\Listing;

use \MovLib\Partial\Date;
use \MovLib\Partial\Alert;

/**
 * Images list for company instances.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class CompanyListing {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The Dependency Injection Container
   *
   * @var \MovLib\Data\diContainer
   */
  protected $diContainer;

  /**
   * The active intl instance.
   *
   * @var \MovLib\Data\Intl
   */
  protected $intl;

  /**
   * The list items to display.
   *
   * @var mixed
   */
  protected $listItems;

  /**
   * The text to display if there are no items.
   *
   * @var mixed
   */
  protected $noItemsText;

  /**
   * The presenting presenter.
   *
   * @var \MovLib\Presentation\AbstractPresenter
   */
  protected $presenter;

  /**
   * The active response instance.
   *
   * @var \MovLib\Data\Response
   */
  protected $response;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new company listing.
   *
   * @param \MovLib\Data\DIContiner $diContainer
   *   The Dependency Injection Container
   * @param mixed $listItems
   *   The items to build the company listing.
   * @param mixed $noItemsText [optional]
   *   The text to display if there are no items, defaults to a generic {@see \MovLib\Presentation\Partial\Alert}.
   */
  public function __construct(\MovLib\Core\diContainer $diContainer, $listItems, $noItemsText = null) {
    $this->diContainer = $diContainer;
    $this->intl        = $this->diContainer->intl;
    $this->response    = $this->diContainer->response;
    $this->presenter   = $this->diContainer->presenter;
    $this->listItems   = $listItems;
    $this->noItemsText = $noItemsText;
  }


  /**
   * Get the string representation of the listing.
   *
   * @return string
   *   The string representation of the listing.
   */
  public function __toString() {
    // @devStart
    // @codeCoverageIgnoreStart
    try {
    // @codeCoverageIgnoreEnd
    // @devEnd
      $list = null;
      /* @var $company \MovLib\Data\Company\Company */
      while ($company = $this->listItems->fetch_object("\\MovLib\\Data\\Company", [ $this->diContainer ])) {
        // @devStart
        // @codeCoverageIgnoreStart
        if (!($company instanceof \MovLib\Data\Company)) {
          throw new \LogicException($this->intl->t("\$company has to be a valid company object!"));
        }
        // @codeCoverageIgnoreEnd
        // @devEnd
        $company->initFetchObject();
        $list .= $this->formatListItem($company);
      }

      if ($list) {
        return "<ol class='hover-list no-list'>{$list}</ol>";
      }

      if (!$this->noItemsText) {
        $this->noItemsText = new Alert(
          $this->intl->t(
            "We couldn’t find any company matching your filter criteria, or there simply isn’t any company available." .
            "Would you like to {0}create a new entry{1}?",
            [ "<a href='{$this->intl->r("/company/create")}'>", "</a>" ]
          ),
          $this->intl->t("No Companies"),
          Alert::SEVERITY_INFO
        );
      }
      return (string) $this->noItemsText;

    // @devStart
    // @codeCoverageIgnoreStart
    } catch (\Exception $e) {
      return (string) new Alert("<pre>{$e}</pre>", "Error Rendering Company List", Alert::SEVERITY_ERROR);
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods

  /**
   * Format a company list item.
   *
   * @param \MovLib\Data\Company\FullCompany $company
   *   The company to format.
   * @param mixed $listItem [optional]
   *   The current list item if different from $company.
   * @return string
   *   The formatted company list item.
   */
  final protected function formatListItem($company, $listItem = null) {
    // Put company dates together.
    $companyDates = null;
    if ($company->foundingDate || $company->defunctDate) {
      $companyDates    = "<br><span class='small'>";
      if ($company->foundingDate) {
        $companyDates .= (new Date($this->presenter, $company->foundingDate))->format($this->intl, [
          "property" => "foundingDate",
          "title" => $this->intl->t("Founding Date")
        ]);
      }
      else {
        $companyDates .= $this->intl->t("{0}unknown{1}", [ "<em title='{$this->intl->t("Founding Date")}'>", "</em>" ]);
      }
      if ($company->defunctDate) {
        $companyDates .= " – " . (new Date($this->presenter, $company->defunctDate))->format($this->intl, [ "title" => $this->intl->t("Defunct Date") ]);
      }
      $companyDates   .= "</span>";
    }

    // @todo: display real company logo
    $companyLogo       =
      "<a class='fl no-link' href='{$company->imageRoute}' property='image'>" .
        "<img alt='' height='60' src='{$this->presenter->getExternalURL("asset://img/logo/vector.svg")}' width='60'>" .
      "</a>"
    ;

    // Put the company list entry together.
    return
      "<li class='hover-item r' typeof='Corporation'>" .
        "<div class='s s10'>" .
          $companyLogo .
          "<span class='s s9'>" .
            $this->getAdditionalContent($company, $listItem) .
            "<a href='{$company->route}' property='url'>" .
              "<span property='name'>{$company->name}</span>" .
            "</a>{$companyDates}" .
          "</span>" .
        "</div>" .
      "</li>"
    ;
  }

  /**
   * Get additional content to display on a company list item.
   *
   * @param \MovLib\Data\Company\FullCompany $company
   *   The company providing the information.
   * @return string
   *   The formatted additional content.
   */
  protected function getAdditionalContent($company, $listItem) {
    // The default implementation returns no additional content.
  }

}
