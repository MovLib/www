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
namespace MovLib\View\HTML;

use \MovLib\Model\AbstractImageModel;

/**
 * Generic gallery view.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class GalleryView extends AbstractView {


  /**
   * Initialize new gallery view.
   *
   * @param \MovLib\Presenter\GalleryPresenter $presenter
   *   The presenter controlling this view.
   */
  public function __construct($presenter) {
    global $i18n;
    parent::__construct($presenter, $i18n->t("{0} {$presenter->galleryTitle}", [ $presenter->title ]));
    $this->stylesheets[] = "modules/gallery.css";
  }

  /**
   * {@inheritdoc}
   * @global \MovLib\Model\I18nModel $i18n
   *   The global I18n Model instance for translations.
   */
  public function getContent() {
    global $i18n;
    $galleryList = "<ol id='gallery-list' class='span span--9'>";
    $c = count($this->presenter->images);
    for ($i = 0; $i < $c; ++$i) {
      $galleryList .=
        "<li class='span span--2'>" .
        $this->a(
          $i18n->r(
            "/{$_SERVER["ACTION"]}/{0}/{$_SERVER["TAB"]}/{1}",
            [ $this->presenter->model->id, $this->presenter->images[$i]->sectionId ]
          ),
          $this->getImage($this->presenter->images[$i], AbstractImageModel::IMAGESTYLE_GALLERY)
        ) .
        "</li>";
    }
    $galleryList .= "</ol>";
    return
      "<div class='container'>" .
        "<div class='row'>" .
          "<aside class='span span--3'>" .
            $this->getSecondaryNavigation(
              $i18n->t("{0} gallery navigation", [ mb_convert_case($this->presenter->getAction(), MB_CASE_TITLE) ]),
              $this->presenter->getSecondaryNavigation()
            ) .
          "</aside>" .
          "<div class='span span--9'>{$galleryList}</div>" .
        "</div>" .
      "</div>"
    ;
  }

}
