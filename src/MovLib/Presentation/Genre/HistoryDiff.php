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
 * @copyright © 2014 MovLib
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
    $this->sectionAdd($this->intl->t("Name"), $this->formatDiffLanguageProperty($history->new->names, $history->old->names));
    return $this->sections;
  }

  protected function formatDiffSimpleProperty($newValue, $oldValue) {
    $newValue = (string) $newValue;
    $oldValue = (string) $oldValue;

    if ($newValue === $oldValue) {
      return "<p>{$this->intl->t("No changes.")}</p>";
    }

    $newContent = $oldContent = null;
    $oldPointer = $newPointer = 0;
    $transformations          = $this->diff->getDiff($oldValue, $newValue);

    foreach ($transformations as $trans) {
      switch ($trans->code) {
        case Diff::COPY:
          $newContent .= mb_substr($newValue, $newPointer, $trans->length);
          $newPointer += $trans->length;
          $oldContent .= mb_substr($oldValue, $oldPointer, $trans->length);
          $oldPointer += $trans->length;
          break;
        case Diff::INSERT:
          $newContent .= "<ins>{$trans->text}</ins>";
          $newPointer += $trans->length;
          break;
        case Diff::DELETE:
          $oldContent .= "<del>" . mb_substr($oldValue, $oldPointer, $trans->length) . "</del>";
          $oldPointer += $trans->length;
          break;
      }
    }

    return "<div class='r'><div class='s s5'>{$newContent}</div><div class='s s5'>{$oldContent}</div></div>";
  }

  protected function formatDiffLanguageProperty($newValue, $oldValue) {
    $formatted = null;

    $newValue = (array) $newValue;
    $oldValue = (array) $oldValue;
    $languageKeys = array_keys($newValue + $oldValue);

    // Display changes for every language.
    foreach ($languageKeys as $languageCode) {
      $newValueLanguage = array_key_exists($languageCode, (array) $newValue) ? $newValue[$languageCode] : null;
      $oldValueLanguage = array_key_exists($languageCode, (array) $oldValue) ? $oldValue[$languageCode] : null;
      // We have changes, append them.
      if ($newValueLanguage !== $oldValueLanguage) {
        $displayLanguage = \Locale::getDisplayLanguage($languageCode, $this->intl->locale);
        $formatted .= "<h3 class='tac'>{$displayLanguage}</h3>{$this->formatDiffSimpleProperty($newValueLanguage, $oldValueLanguage)}";
      }
    }

    if ($formatted) {
      return $formatted;
    }

    return "<p>{$this->intl->t("No changes.")}</p>";
  }

}
