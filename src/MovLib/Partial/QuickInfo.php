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
final class QuickInfo {

  /**
   * The infos of the quick info.
   *
   * @var array
   */
  protected $infos;

  /**
   * The active intl instance.
   *
   * @var \MovLib\Core\Intl
   */
  protected $intl;

  /**
   * The Wikipedia link.
   *
   * @var string
   */
  protected $wikipedia;

  /**
   * Instantiate new quick info partial.
   *
   * @param \MovLib\Core\Intl $intl
   *   The active intl instance.
   */
  public function __construct(\MovLib\Core\Intl $intl) {
    $this->intl = $intl;
  }

  /**
   * Get the quick info's string representation.
   *
   * @return string
   *   The quick info's string representation.
   */
  public function __toString() {
    // @devStart
    // @codeCoverageIgnoreStart
    try {
    // @codeCoverageIgnoreEnd
    // @devEnd
      $formatted = "";

      if ($this->infos) {
        $formatted .= "<div class='dt'>";
        foreach ($this->infos as $label => $info) {
          if ($info === (array) $info) {
            $info = implode(trim($this->intl->t("{0}, {1}"), "{}01"), $info);
          }
          $formatted .= "<p class='dtr'><span class='dtc'>{$this->intl->t("{0}:", $label)}</span><span class='dtc'>{$info}</span></p>";
        }
        $formatted .= "</div>";
      }

      if ($this->wikipedia) {
        $formatted .= "<p><span class='ico ico-wikipedia'></span> <a href='{$this->wikipedia}' property='sameAs' target='_blank'>{$this->intl->t("Wikipedia Article")}</a></p>";
      }

      if ($formatted) {
        $formatted = "<section class='quickinfo'><h2 class='vh'>{$this->intl->t("Infobox")}</h2>{$formatted}</section>";
      }

      return $formatted;
    // @devStart
    // @codeCoverageIgnoreStart
    }
    catch (\Exception $e) {
      return (string) new \MovLib\Partial\Alert("<pre>{$e}</pre>", "Error Rendering Quick Info", \MovLib\Partial\Alert::SEVERITY_ERROR);
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
  }

  /**
   * Add new info to the quick infos.
   *
   * @param string $label
   *   The label of the info.
   * @param mixed $info
   *   The info for the label, you can pass any scaler value, objects that implement the <code>__toString()</code>
   *   method, or arrays which will be imploded with a comma.
   * @return this
   */
  public function add($label, $info) {
    if (!empty($info)) {
      $this->infos[$label] = $info;
    }
    return $this;
  }

  /**
   * Add a Wikipedia link to the quick infos.
   *
   * @param string $link
   *   The Wikipedia link to add.
   * @return this
   */
  public function addWikipedia($link) {
    if (!empty($link)) {
      $this->wikipedia = $link;
    }
    return $this;
  }

}
