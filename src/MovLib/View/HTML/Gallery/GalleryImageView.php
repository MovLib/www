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
 * Generic template for single gallery images.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class GalleryImageView extends AbstractPageView {
  use \MovLib\View\HTML\TraitSecondaryNavigationView;

  /**
   * The position of this image within all images of this section (e.g. <code>"Photo X of Y"</code>).
   *
   * @var int
   */
  public $position;

  /**
   * Total image count of this section.
   *
   * @var int
   */
  public $totalCount;

  /**
   * Instantiate new single gallery image view.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @param \MovLib\Presenter\Gallery\AbstractGalleryPresenter $presenter
   *   The gallery image presenter controlling this view.
   */
  public function __construct($presenter) {
    global $i18n;
    list($this->position, $this->totalCount) = $presenter->imageModel->getPosterPositionAndTotalCount();
    $this->init($presenter, $i18n->t("{0} {1} of {2} from {3}", [ $presenter->imageTitle, $this->position, $this->totalCount, $presenter->title ]));
    $this->stylesheets[] = "modules/gallery-image.css";
  }

  /**
   * @inheritdoc
   */
  public function getSecondaryContent() {
    global $i18n;
    return
      "<div id='gallery-image--stream'>{$this->presenter->getImageStream()}</div>" .
      "<div id='gallery-image--image'>{$this->a(
        $this->presenter->imageModel->imageUri,
        $this->getImage($this->presenter->imageModel, AbstractImageModel::IMAGESTYLE_DETAILS, [
          "alt" => $i18n->t("{0} movie poster{1}.", [ $this->presenter->title, isset($this->presenter->imageModel->country)
            ? $i18n->t(" for {0}", [ $this->presenter->imageModel->country["name"] ])
            : ""
          ]),
        ])
      )}</div>" .
      $this->getDescriptionList($this->presenter->getImageDescription(), "", null, [
        "class" => "dl--horizontal",
        "id" => "gallery-image--description",
      ])
    ;
  }

}