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
 * Defines methods to add sections to the presenter's content.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
trait SectionTrait {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The sections.
   *
   * @var string
   */
  protected $sections;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Format additional names.
   *
   * @return string
   *   The formatted additional names.
   */
  final protected function formatAliases($aliases) {
    $formatted = null;
    $c = count($aliases);
    for ($i = 0; $i < $c; ++$i) {
      $formatted .= "<li class='mb10 s s3' property='additionalName'>{$aliases[$i]}</li>";
    }
    if ($formatted) {
      return "<ul class='grid-list r'>{$formatted}</ul>";
    }
  }

  /**
   * Add section to content.
   *
   * @param string $title
   *   The section's title.
   * @param string $content
   *   The section's content.
   * @param boolean $decode [optional]
   *   Whether the content should be HTML decoded or not, defaults to <code>TRUE</code> (content will be decoded).
   * @param array|string $attributes [optional]
   *   Additional attributes that should be applied to the <code><section></code>, note that the <code>"id"</code> is
   *   always overwritten. If you pass a string it's assumed that you want to add CSS classes.
   * @param string $editRoute [optional]
   *   The route to the edit page of this section, defaults to <code>NULL</code>
   * @return this
   */
  final protected function sectionAdd($title, $content, $decode = true, $attributes = null, $editRoute = null) {
    $decode && ($content = $this->htmlDecode($content));

    if ($attributes !== (array) $attributes) {
      $attributes = [ "class" => $attributes ];
    }
    $attributes["id"] = mb_strtolower(preg_replace("/[^\d\w-_]+/", "-", $title));
    if (is_numeric($title{0})) {
      $attributes["id"] = "s{$attributes["id"]}"; // Numeric CSS ids arent' allowed!
    }

    $this->sidebarNavigation->menuitems[] = [ "#{$attributes["id"]}", $title ];
    if ($editRoute) {
      $title = "<div class='cf'><a class='edit btn btn-info fr' href='{$editRoute}'>{$this->intl->t("Edit")}</a><h2 class='title'>{$title}</h2></div>";
    }
    else {
      $title = "<h2 class='title'>{$title}</h2>";
    }
    $this->sections .= "<section{$this->expandTagAttributes($attributes)}>{$title}{$content}</section>";

    return $this;
  }

}
