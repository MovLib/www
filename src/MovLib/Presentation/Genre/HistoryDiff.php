<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright Â© 2013-present {@link https://movlib.org/ MovLib}.
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
namespace MovLib\Presentation\Genre;

use \MovLib\Core\Diff\Diff;
use \MovLib\Data\Genre\Genre;

/**
 * Defines the genre history diff presentation.
 *
 * @route /genre/{id}/history/{ro}/{rn}
 * @property \MovLib\Data\Genre\Genre $entity
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright Â© 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class HistoryDiff extends \MovLib\Core\Presentation\AbstractHistoryDiff {
  use \MovLib\Partial\SectionTrait;


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "HistoryDiff";
  // @codingStandardsIgnoreEnd

  /**
   * {@inheritdoc}
   */
  public function init() {
    $this->entity = new Genre($this->container, $_SERVER["GENRE_ID"]);
    $this->diff = new Diff();
    return $this->initHistoryDiff();
  }

  public function getContent() {
    $history = new \MovLib\Data\History\History((string) $this->entity, $this->entity->id, $_SERVER["REVISION_OLD"], $_SERVER["REVISION_NEW"]);
    return (new \MovLib\Partial\Table\DiffTable($history->old, $history->new))
      ->addDiffLanguageProperty($this->intl->t("Name"), $history->old->names, $history->new->names)
    ;
    $this->sectionAdd($this->intl->t("Name"), $this->formatDiffLanguageProperty($history->new->names, $history->old->names));
    return $this->sections;
  }

}
