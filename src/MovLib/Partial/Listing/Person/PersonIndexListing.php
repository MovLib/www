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
namespace MovLib\Partial\Listing\Person;

/**
 * Defines the person index listing.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class PersonIndexListing extends \MovLib\Partial\Listing\AbstractMySQLiResultListing {
  use \MovLib\Partial\Person\PersonTrait;

  /**
   * {@inheritdoc}
   * @param \MovLib\Data\Person\Person $item {@inheritdoc}
   */
  public function formatItem($item, $delta = null) {
    $bornName = null;
    if ($item->bornName) {
      $bornName = " <small>{$this->diContainerHTTP->intl->t("{0} ({1})", [
        "<span property='additionalName'>{$item->bornName}</span>",
        "<i>{$this->diContainerHTTP->intl->t("born name")}</i>",
      ])}</small>";
    }

    if (($bioDates = $this->getPersonBioDates($this->diContainerHTTP->presenter, $this->diContainerHTTP->intl, $item))) {
      $bioDates = "<small>{$bioDates}</small>";
    }

    return
      "<li class='hover-item r' typeof='Person'>" .
        "<a class='no-link s s1 tac'><img alt='' height='60' src='{$this->diContainerHTTP->presenter->getExternalURL("asset://img/logo/vector.svg")}' width='60'></a>" .
        "<div class='s s9'><p><a href='{$item->route}' property='url'><span property='name'>{$item->name}</span></a></p>{$bornName}{$bioDates}</div>" .
      "</li>"
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function getListing($items) {
    return "<ol class='hover-list no-list'>{$items}</ol>";
  }

}
