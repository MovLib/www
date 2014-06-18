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
namespace MovLib\Partial\Table;

use \MovLib\Core\Diff\Diff;

/**
 * Defines the table partial for diff presentations.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class DiffTable extends \MovLib\Partial\Table\Table {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "DiffTable";

  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The diff instance for computing differences.
   *
   * @var \MovLib\Core\Diff\Diff
   */
  public $diff;

  /**
   * The Intl instance for translations.
   *
   * @var \MovLib\Core\Intl
   */
  public $intl;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   *
   */
  public function __construct(\MovLib\Core\Intl $intl, \MovLib\Core\Revision\AbstractRevision $oldRevision, \MovLib\Core\Revision\AbstractRevision $newRevision) {
    parent::__construct([ "class" => "diff-table" ], null);
    $this->diff = new Diff();
    $this->intl = $intl;
  }


  // ------------------------------------------------------------------------------------------------------------------- Public Methods


  /**
   * Add a simple (scalar) property to the diff table.
   *
   * @param string $name
   *   The property's translated name.
   * @param mixed $oldValue
   *   The property's old value.
   * @param mixed $newValue
   *   The property's new value.
   * @return $this
   */
  public function addDiffSimpleProperty($name, $oldValue, $newValue) {
    if ($oldValue === $newValue) {
      return $this;
    }

    list($oldValue, $newValue) = $this->formatStringDiff($oldValue, $newValue);

    return $this
      ->addRow([ ["#content" => $name, "colspan" => 2], [] ], [ "class" => "some-separator" ])
      ->addRow([ [ "#content" => $oldValue ], [ "#content" => $newValue ], [] ])
    ;
  }

  /**
   * Add a language dependent property (array keyed by language codes) to the diff table.
   *
   * @param string $name
   *   The property's translated name.
   * @param array $oldValue
   *   The property's old values.
   * @param array $newValue
   *   The property's new values.
   * @return $this
   */
  public function addDiffLanguageProperty($name, $oldValue, $newValue) {
    // Prevent foreach and array_keys from null values.
    $oldValue = (array) $oldValue;
    $newValue = (array) $newValue;

    // Look for changes in every language.
    $languageKeys = array_keys($oldValue + $newValue);
    $changes      = false;
    foreach ($languageKeys as $languageCode) {
      $newValueLanguage = array_key_exists($languageCode, $newValue) ? $newValue[$languageCode] : null;
      $oldValueLanguage = array_key_exists($languageCode, $oldValue) ? $oldValue[$languageCode] : null;

      // We have changes, append them.
      if ($newValueLanguage !== $oldValueLanguage) {
        // Add the property heading if it's the first change we encounter.
        if ($changes === false) {
          $changes = true;
          $this->addRow([ ["#content" => $name, "colspan" => 2], [] ], [ "class" => "some-separator" ]);
        }
        list($oldValueLanguage, $newValueLanguage) = $this->formatStringDiff($oldValueLanguage, $newValueLanguage);
        $displayLanguage = \Locale::getDisplayLanguage($languageCode, $this->intl->locale);
        $this->addRow([
          [ "#content" => $oldValue ],
          [ "#content" => $newValue ],
          [ "#content" => $this->intl->t("Language: {language}", [ "language" => $displayLanguage ]) ]
        ]);
      }
    }

    return $this;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Format the textual difference between two values as HTML.
   *
   * @param type $oldValue
   *   The old value for the diff.
   * @param mixed $newValue
   *   The new value for the diff.
   * @return array
   *   Contains 0 => formatted old difference, 1 => formatted new difference for use with list().
   */
  protected function formatStringDiff($oldValue, $newValue) {
    $newValue = (string) $newValue;
    $oldValue = (string) $oldValue;

    if ($newValue === $oldValue) {
      throw new \LogicException;
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

    return [ $oldContent, $newContent ];
  }

}
