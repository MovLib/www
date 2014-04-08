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
namespace MovLib\Partial;

/**
 * Defines the quick info partial.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
trait InfoboxTrait {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The infobox's formatted infos.
   *
   * @var string
   */
  protected $infoboxInfos;

  /**
   * The infobox's image.
   *
   * @var \MovLib\Data\Image\AbstractReadOnlyImageEntity
   */
  protected $infoboxImage;

  /**
   * The infobox's image route.
   *
   * @var mixed
   */
  protected $infoboxImageRoute;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get the infobox main heading.
   *
   * @return string
   *   The infobox main heading.
   */
  final protected function getMainHeading() {
    if (!empty($this->infoboxImage->wikipedia)) {
      $this->infoboxInfos .= "<small><span class='ico ico-wikipedia'></span> <a href='{$this->infoboxImage->wikipedia}' property='sameAs' target='_blank'>{$this->intl->t("Wikipedia Article")}</a></small>";
    }

    if ($this->infoboxInfos) {
      $this->infoboxInfos = "<section id='infobox'><h2 class='vh'>{$this->intl->t("Infobox")}</h2>{$this->infoboxInfos}</section>";
    }

    $property = $this->headingSchemaProperty ? "property='{$this->headingSchemaProperty}'" : null;
    return
      "<div class='r'>" .
        "<div class='s s10'>" .
          "<h1{$property}>{$this->pageTitle}</h1>" .
          "{$this->headingBefore}{$this->infoboxInfos}{$this->headingAfter}" .
        "</div>" .
        "<div class='s s2'>{$this->img($this->infoboxImage->imageGetStyle("s2"), [], $this->infoboxImageRoute)}</div>" .
      "</div>"
    ;
  }

  /**
   * Add new info to the infobox.
   *
   * @param string $label
   *   The label of the info.
   * @param mixed $info
   *   The info for the label, you can pass any scaler value, objects that implement the <code>__toString()</code>
   *   method, or arrays which will be imploded with a comma.
   * @param string $tag [optional]
   *   The tag to wrap the infobox entry, defaults to <code>"p"</code>.
   * @return this
   */
  final protected function infoboxAdd($label, $info, $tag = "p") {
    if (!empty($info)) {
      if ($info === (array) $info) {
        $info = implode($this->intl->t(", "), $info);
      }
      $this->infoboxInfos .= "<{$tag} class='dtr'><span class='dtc'>{$this->intl->t("{0}:", $label)}</span> <span class='dtc'>{$info}</span></{$tag}>";
    }
    return $this;
  }

  /**
   * Initialize the infobox trait.
   *
   * @param \MovLib\Data\Image\AbstractReadOnlyImageEntity $image
   *   The infobox's image entity.
   * @param mixed $imageRoute [optional]
   *   The infobox's image route, defaults to <code>FALSE</code> ({@see \MovLib\Core\Presentation\Base::img()}).
   * @param mixed $before [optional]
   *   Arbitrary data that should be included before the infobox.
   * @param mixed $after [optional]
   *   Arbitrary data that should be included after the infobox.
   * @return this
   */
  final protected function infoboxInit(\MovLib\Data\Image\AbstractReadOnlyImageEntity $image, $imageRoute = false, $before = null, $after = null) {
    $this->headingAfter      = $after;
    $this->headingBefore     = $before;
    $this->infoboxImage      = $image;
    $this->infoboxImageRoute = $imageRoute;
    return $this;
  }

}
