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

use \MovLib\Data\Image\AbstractReadOnlyImageEntity;

/**
 * Defines the quick info partial.
 *
 * @property-read \MovLib\Data\AbstractEntity $entity
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
   * The infobox's arbitrary content that should be displayed before itself but after the heading.
   *
   * @var string
   */
  protected $infoboxBefore;

  /**
   * The infobox's formatted infos.
   *
   * @var string
   */
  private $infoboxInfos;

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
    // @devStart
    // @codeCoverageIgnoreStart
    assert($this->entity instanceof \MovLib\Core\Entity\AbstractEntity, "You need to have an entity property in order to use the InfoboxTrait");
    // @codeCoverageIgnoreEnd
    // @devEnd

    // Build Wikipedia link if current entity has one.
    if (!empty($this->entity->wikipedia)) {
      $this->infoboxInfos .= "<small><span class='ico ico-wikipedia'></span> <a href='{$this->entity->wikipedia}' property='sameAs' target='_blank'>{$this->intl->t("Wikipedia Article")}</a></small>";
    }

    // Build the infobox section, note that we're concatenating at this point because the concrete class might have
    // inserted some content before the infobox.
    if ($this->infoboxInfos) {
      $this->infoboxInfos = "<section id='infobox'><h2 class='vh'>{$this->intl->t("Infobox")}</h2>{$this->infoboxInfos}</section>";
    }

    // Build the default main heading.
    $property    = $this->headingSchemaProperty ? " property='{$this->headingSchemaProperty}'" : null;
    $mainHeading = "{$this->headingBefore}<h1{$property}>{$this->pageTitle}</h1>{$this->infoboxBefore}{$this->infoboxInfos}{$this->headingAfter}";

    // Add grid with image if we have an image to present.
    if ($this->entity instanceof AbstractReadOnlyImageEntity) {
      return
        "<div class='r'>" .
          "<div class='s s10'>{$mainHeading}</div>" .
          "<div class='s s2'>{$this->img($this->entity->imageGetStyle("s2"), [], $this->infoboxImageRoute)}</div>" .
        "</div>"
      ;
    }

    return $mainHeading;
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

}
