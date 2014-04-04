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
namespace MovLib\Presentation\Release;

use \MovLib\Data\Release\ReleaseSet;
use \MovLib\Partial\Alert;

/**
 * Defines the release index presentation.
 *
 * @link http://www.google.com/webmasters/tools/richsnippets?q=https://en.movlib.org/releases
 * @link http://www.w3.org/2012/pyRdfa/extract?validate=yes&uri=https://en.movlib.org/releases
 * @link http://validator.w3.org/check?uri=https://en.movlib.org/releases
 * @link http://gsnedders.html5.org/outliner/process.py?url=https://en.movlib.org/releases
 *
 * @property \MovLib\Data\Release\ReleaseSet $set
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Index extends \MovLib\Presentation\AbstractIndexPresenter {

  /**
   * {@inheritdoc}
   */
  public function init() {
    return $this->initIndex(new ReleaseSet($this->diContainerHTTP), $this->intl->t("Create New Release"));
  }

  /**
   * {@inheritdoc}
   * @param \MovLib\Data\Release\Release $release {@inheritdoc}
   */
  public function formatListingItem(\MovLib\Data\AbstractEntity $release, $delta) {
    return
      "<li class='hover-item r'>" .
        "<article>" .
          "<a class='no-link s s1' href='{$release->route}'>" .
            "<img alt='{$release->name}' src='{$this->getExternalURL("asset://img/logo/vector.svg")}' width='60' height='60'>" .
          "</a>" .
          "<div class='s s9'>" .
            "<h2 class='para'><a href='{$release->route}' property='url'><span property='name'>{$release->name}</span></a></h2>" .
          "</div>" .
        "</article>" .
      "</li>"
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function getNoItemsContent() {
    return new Alert(
      "<p>{$this->intl->t("We couldn’t find any releases matching your filter criteria, or there simply aren’t any releases available.")}</p>" .
      "<p>{$this->intl->t("Would you like to {0}create an release{1}?", [ "<a href='{$this->intl->r("/release/create")}'>", "</a>" ])}</p>",
      $this->intl->t("No Releases")
    );
  }

}
