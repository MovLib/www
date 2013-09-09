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
namespace MovLib\Presenter\Gallery;

use \MovLib\Presenter\AbstractPresenter;
use \MovLib\View\HTML\Gallery\GalleryView;

/**
 * Base class for all gallery presenter.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractGalleryPresenter extends AbstractPresenter {

  /**
   * The gallery's model.
   *
   * @var \MovLib\Model\BaseModel
   */
  public $model;

  /**
   * The entity's title.
   *
   * @var string
   */
  public $title;

  /**
   * The gallery's title.
   *
   * @var string
   */
  public $galleryTitle;

  /**
   * Numeric array containing all image models of this gallery.
   *
   * @var array
   */
  public $images;

  /**
   * Get the secondary navigation for this gallery presenter.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @return array
   *   The secondary navigation points.
   */
  abstract public function getSecondaryNavigationPoints();

  /**
   * Set the view for this presenter.
   *
   * @return this
   */
  protected function setView() {
    new GalleryView($this);
    return $this;
  }

}
