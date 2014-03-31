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
trait ContentSectionTrait {

  private $sections = [];

  final protected function addContentSection($title, $content, $decode = true) {
    $this->sections[$title] = $decode ? $this->htmlDecode($content) : $content;
    return $this;
  }

  final protected function getContentSections() {
    $formatted = null;
    foreach ($this->sections as $title => $content) {
      $id = $this->htmlString2ID($title);
      $this->sidebarNavigation->menuitems[] = [ "#{$id}", $title ];
      $formatted .= "<section id='{$id}'><h2 class='title'>{$title}</h2><div class='content'>{$content}</div></section>";
    }
    return $formatted;
  }

}
