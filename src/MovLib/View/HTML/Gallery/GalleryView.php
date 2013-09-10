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
namespace MovLib\View\HTML\Gallery;

use \MovLib\Model\AbstractImageModel;
use \MovLib\View\HTML\AbstractPageView;

/**
 * Generic gallery view.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class GalleryView extends AbstractPageView {
  use \MovLib\View\HTML\TraitSecondaryNavigationView;

  /**
   * The title of this view's breadcrumb entry.
   *
   * @var string
   */
  public $breadcrumbTitle;

  /**
   * Initialize new gallery view.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @param \MovLib\Presenter\GalleryPresenter $presenter
   *   The presenter controlling this view.
   */
  public function __construct($presenter) {
    global $i18n;
    $this->init($presenter, $i18n->t("{$presenter->galleryTitle} of {0}", [ $presenter->title ]));
    $this->breadcrumbTitle = $presenter->galleryTitle;
    $this->stylesheets[] = "modules/gallery.css";
  }

  /**
   * @inheritdoc
   */
  public function getSecondaryContent() {
    global $i18n;
    return
      $this->getOrderedList(
        $this->presenter->images,
        "<p>{$i18n->t("No {$this->presenter->galleryTitle} for {0}.", [ $this->presenter->title ])}</p>" .
        "<p>{$i18n->t("Want to upload your {0}? {1}", [
          $this->presenter->galleryTitle,
          $this->a($i18n->r("/{$_SERVER["ACTION"]}/{0}/{$_SERVER["TAB"]}s/upload", [ $_SERVER["ID"] ]), $i18n->t("Click here to do so."))
        ])}</p>",
        function ($imageModel) use ($i18n) {
          return "<li class='span span--2'>{$this->a(
            $i18n->r("/{$_SERVER["ACTION"]}/{0}/{$_SERVER["TAB"]}/{1}", [ $imageModel->id, $imageModel->sectionId ]),
            $this->getImage($imageModel, AbstractImageModel::IMAGESTYLE_GALLERY, [
              "alt" => $i18n->t("{0} movie poster{1}.", [ $this->presenter->title, isset($imageModel->country)
                ? $i18n->t(" for {0}", [ $imageModel->country["name"] ])
                : ""
              ]),
            ])
          )}</li>";
        },
        [ "id" => "gallery-list" ]
      )
    ;
  }

}
