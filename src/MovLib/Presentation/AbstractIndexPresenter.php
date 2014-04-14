<?php

/* !
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
namespace MovLib\Presentation;

/**
 * Defines base class for index presenter.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractIndexPresenter extends \MovLib\Presentation\AbstractPresenter {
  use \MovLib\Partial\SidebarTrait;
  use \MovLib\Partial\PaginationTrait;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The set to present.
   *
   * @var \MovLib\Data\AbstractSet
   */
  protected $set;


  // ------------------------------------------------------------------------------------------------------------------- Abstract Methods


  /**
   * Format a single listing's item.
   *
   * @param \MovLib\Data\AbstractEntity $item
   *   The listing's item to format.
   * @param integer $delta
   *   The current loops delta.
   * @return string
   *   The formatted listing's item.
   */
  abstract protected function formatListingItem(\MovLib\Data\AbstractEntity $item, $delta);


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Initialize default index presentation.
   *
   * @param \MovLib\Data\AbstractSet $set
   *   The set to present.
   * @param string $title
   *   The title for page title and breadcrumb.
   * @param string $createText
   *   The translated text for the creation button (title case).
   * @return this
   */
  public function initIndex(\MovLib\Data\AbstractSet $set, $title, $createText) {
    $this->set           = $set;
    $this->headingBefore = "<a class='btn btn-large btn-success fr' href='{$this->intl->r("/{$set->singularKey}/create")}'>{$createText}</a>";
    $this->initPage($title);
    $this->initLanguageLinks("/{$set->pluralKey}", null, true);
    $this->sidebarInit([
      [ $set->route, $title, [ "class" => "ico ico-{$set->singularKey}" ] ],
      [ $this->intl->r("/{$set->singularKey}/random"), $this->intl->t("Random") ],
      [ $this->intl->rp("/{$set->singularKey}/charts"), $this->intl->t("Charts") ],
      [ $this->intl->r("/help/database/{$set->pluralKey}"), $this->intl->t("Help") ],
    ]);
    $this->paginationInit();
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    $items = null;
    foreach ($this->set->loadOrdered("`created` DESC", $this->paginationOffset, $this->paginationLimit) as $id => $entity) {
      $items .= $this->formatListingItem($entity, $id);
    }
    return $this->getListing($items);
  }

  /**
   * Get the listing.
   *
   * @param string $items
   *   The formatted listing's items.
   * @return string
   *   The listing.
   */
  protected function getListing($items) {
    return "<ol class='hover-list no-list'>{$items}</ol>";
  }

}
