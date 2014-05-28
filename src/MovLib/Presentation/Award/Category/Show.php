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
namespace MovLib\Presentation\Award\Category;

use \MovLib\Data\Award\Category;
use \MovLib\Partial\Date;

/**
 * Defines the award category show presentation.
 *
 * @property \MovLib\Data\Award\Award $entity
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Show extends \MovLib\Presentation\AbstractShowPresenter {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Show";
  // @codingStandardsIgnoreEnd
  use \MovLib\Presentation\Award\Category\CategoryTrait;

  /**
   * {@inheritdoc}
   */
  public function init() {
    $this->entity = new Category($this->container, $_SERVER["CATEGORY_ID"]);
    $this->initPage($this->entity->name);
    $this->breadcrumb->addCrumbs([
      [ $this->intl->r("/awards"), $this->intl->t("Awards") ],
      [ $this->intl->r("/award/{0}/", [ $this->entity->award->id ]), $this->entity->award->name ],
    ]);
    $this->initShow($this->entity, $this->intl->t("Categories"), "Category", null, $this->getSidebarItems())
    ;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    $this->entity->firstYear && $this->infoboxAdd($this->intl->t("From"), (new Date($this->intl, $this))->format($this->entity->firstYear));
    $this->entity->lastYear  && $this->infoboxAdd($this->intl->t("To"), (new Date($this->intl, $this))->format($this->entity->lastYear));

    $this->entity->description && $this->sectionAdd($this->intl->t("Description"), $this->entity->description);
    if ($this->sections) {
      return $this->sections;
    }

    return $this->callout(
      $this->intl->t("Would you like to {0}add additional information{1}?", [ "<a href='{$this->intl->r("/award/{0}/category/{1}/edit", [ $this->entity->award->id, $this->entity->id ])}'>", "</a>" ]),
      $this->intl->t("{sitename} doesn’t have further details about this award category.", [ "sitename" => $this->config->sitename ])
    );
  }

}
