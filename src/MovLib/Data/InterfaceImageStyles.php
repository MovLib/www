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
namespace MovLib\Data;

/**
 * All available image widths as namespace constants.
 *
 * All are direct matches to the CSS grid classes, you should only use these widths for all of your styles, to ensure
 * that they always match the grid system. There are special occasions where images will not match the grid system,
 * they need special attention. For an example of this have a look at the stream images of the various image details
 * presentations.
 */
const IMAGE_STYLE_SPAN1  = 70;
const IMAGE_STYLE_SPAN2  = 140;
const IMAGE_STYLE_SPAN3  = 220;
const IMAGE_STYLE_SPAN4  = 300;
const IMAGE_STYLE_SPAN5  = 380;
const IMAGE_STYLE_SPAN6  = 460;
const IMAGE_STYLE_SPAN7  = 540;
const IMAGE_STYLE_SPAN8  = 620;
const IMAGE_STYLE_SPAN9  = 700;
const IMAGE_STYLE_SPAN10 = 780;
const IMAGE_STYLE_SPAN11 = 860;
const IMAGE_STYLE_SPAN12 = 940;

/**
 * Default image styles.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
interface InterfaceImageStyles {

  /**
   * Default image widths as class constants.
   *
   * The image styles <code>\MovLib\Data\Image\IMAGE_STYLE_SPAN1</code> and <code>\MovLib\Data\Image\IMAGE_STYLE_SPAN2</code>
   * are always available for every image(s). This is because <code>IMAGE_STYLE_SPAN1</code> is used in
   * <code>\MovLib\Presentation\Partial\FormElement\InputImage</code> if the image exists to display a very small
   * preview of the existing image and <code>IMAGE_STYLE_SPAN2</code> is used within any presentation class that
   * displays the images within a list grid. Each implementing class has decide how it generates these styles (cropped
   * rectangle, etc.), but they have to be implemented!
   */
  const IMAGE_STYLE_SPAN1 = \MovLib\Data\IMAGE_STYLE_SPAN1;
  const IMAGE_STYLE_SPAN2 = \MovLib\Data\IMAGE_STYLE_SPAN2;

}
