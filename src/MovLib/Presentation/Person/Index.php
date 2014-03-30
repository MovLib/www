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
namespace MovLib\Presentation\Person;

use \MovLib\Data\Person\PersonSet;
use \MovLib\Partial\Alert;

/**
 * Defines the person index listing.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Index extends \MovLib\Presentation\AbstractIndexPresenter {
  use \MovLib\Partial\PersonTrait;

  /**
   * {@inheritdoc}
   */
  public function init() {
    $this->set = new PersonSet($this->diContainerHTTP);
    $this->headingBefore =
      "<a class='btn btn-large btn-success fr' href='{$this->intl->r("/person/create")}'>" .
        $this->intl->t("Create New Person") .
      "</a>"
    ;
    $this
      ->initPage($this->intl->t("Persons"))
      ->initBreadcrumb()
      ->initLanguageLinks("/persons", null, true)
      ->sidebarInit([
        [ $this->request->path, $this->title, [ "class" => "ico ico-person" ] ],
        [ $this->intl->r("/person/random"), $this->intl->t("Random") ],
      ])
      ->paginationInit($this->set)
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function getNoItemsContent() {
    return new Alert(
      "<p>{$this->intl->t(
        "We couldn’t find any persons matching your filter criteria, or there simply aren’t any persons available."
      )}</p><p>{$this->intl->t(
        "Would you like to {0}create a person{1}?",
        [ "<a href='{$this->intl->r("/person/create")}'>", "</a>" ]
      )}</p>",
      $this->intl->t("No Persons")
    );
  }

  /**
   * {@inheritdoc}
   * @param \MovLib\Data\Person\Person $person {@inheritdoc}
   */
  protected function formatListingItem($person) {
    if (($bornName = $this->getPersonBornName($person))) {
      $bornName = "<br><span class='small'>{$bornName}</span>";
    }
    if (($bioDates = $this->getPersonBioDates($person))) {
      $bioDates = "<br><span class='small'>{$bioDates}</span>";
    }
    return
      "<li class='hover-item r' typeof='Person'>" .
        "<a class='no-link s s1 tac'><img alt='' height='60' src='{$this->getExternalURL("asset://img/logo/vector.svg")}' width='60'></a>" .
        "<div class='s s9'><a href='{$person->route}' property='url'><span property='name'>{$person->name}</span></a>{$bornName}{$bioDates}</div>" .
      "</li>"
    ;
  }

  /**
   * {@inheritdoc}
   */
  protected function getListing($items) {
    return "<ol class='hover-list no-list'>{$items}</ol>";
  }

}
