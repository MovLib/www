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
namespace MovLib\Core\Presentation;

use \MovLib\Core\Diff\Diff;
use \MovLib\Data\History\History;
use \MovLib\Data\User\User;
use \MovLib\Exception\RedirectException\TemporaryRedirectException;
use \MovLib\Partial\DateTime;
use \MovLib\Partial\Table\Table;

/**
 * Defines base class for history diff presenter.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractHistoryDiff extends \MovLib\Presentation\AbstractPresenter {
  use \MovLib\Partial\SidebarTrait;


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "AbstractHistoryDiffPresenter";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The diff instance for computing differences.
   *
   * @var \MovLib\Core\Diff\Diff
   */
  protected $diff;

  /**
   * The entity to present.
   *
   * @var \MovLib\Core\Entity\AbstractEntity
   */
  protected $entity;

  /**
   * The history object used for patching.
   *
   * @var \MovLib\Data\History\History
   */
  protected $history;

  /**
   * The table for displaying differences.
   *
   * @var \MovLib\Partial\Table\Table
   */
  protected $table;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Initialize the history diff presentation.
   *
   * @param array $breadcrumbs [optional]
   *   Additional breadcrumbs to prepend to the trail (in the syntax of {@see \MovLib\Partial\Navigation\Breadcrumb::addCrumbs}).
   *   The entity's and the set's breadcrumb will be added automatically.
   * @throws TemporaryRedirectException
   */
  protected function initHistoryDiff(array $breadcrumbs = null) {
    // @devStart
    // @codeCoverageIgnoreStart
    assert(!empty($this->entity), "You have to initialize the entity property");
    assert($this->entity instanceof \MovLib\Core\Entity\AbstractEntity, "Entity must be a child of \\MovLib\\Core\\Entity\\AbstractEntity");
    // @codeCoverageIgnoreEnd
    // @devEnd

    // We can assume that the entity exists at this point.
    $historyRoute = $this->entity->r("/history");

    // We have to make sure that the request actually makes sense, if not redirect. We use a temporary redirect, may
    // be that the route we redirect now has some purpose in the future.
    if (isset($_SERVER["REVISION_NEW"])) {
      // We can't display a diff between two revisions that are actually the same, doesn't make sense.
      if ($_SERVER["REVISION_OLD"] == $_SERVER["REVISION_NEW"]) {
        throw new TemporaryRedirectException("{$historyRoute}/{$_SERVER["REVISION_OLD"]}");
      }
      // We only support diff view between old and new.
      elseif ($_SERVER["REVISION_OLD"] > $_SERVER["REVISION_NEW"]) {
        throw new TemporaryRedirectException("{$historyRoute}/{$_SERVER["REVISION_NEW"]}/{$_SERVER["REVISION_OLD"]}");
      }
    }
    else {
      $_SERVER["REVISION_NEW"] = null;
    }

    // Configure the presentation.
    $this->initPage(
      $this->intl->t("{0}: {1}", [ $this->entity->lemma, $this->intl->t("Difference between revisions") ]),
      null,
      $this->intl->t("Diff")
    );
    $this->sidebarInitToolbox($this->entity);
    // @todo Replace with the new universal implementation to enable nested entities.
    isset($breadcrumbs) && ($this->breadcrumb->addCrumbs($breadcrumbs));
    $this->breadcrumb->addCrumb($this->entity->set->route, $this->entity->set->bundleTitle);
    $this->breadcrumb->addCrumb($this->entity->route, $this->entity->lemma);
    $this->breadcrumb->addCrumb($historyRoute, $this->intl->t("History"));

    // Construct the diff table with the correct header.
    $this->history = new History((string) $this->entity, $this->entity->id, $_SERVER["REVISION_OLD"], $_SERVER["REVISION_NEW"]);
    $oldUser = new User($this->container, $this->history->old->userId, User::FROM_ID);
    $newUser = new User($this->container, $this->history->new->userId, User::FROM_ID);
    $dateTime = new DateTime($this->intl, $this, $this->session->userTimezone);

    if ($this->history->new->id === $this->entity->changed->formatInteger()) {
      $newRevisionName = $this->intl->t("Current Revision");
    }
    else {
      $newRevisionName = $this->intl->t("Revision {0}", $this->history->new->id);
    }

    $this->table = (new Table([ "class" => "table-diff" ], [
      [ "#content" => "{$this->img($oldUser->imageGetStyle("s1"), [], true, [ "class" => "fl" ])}<span class='big'>{$this->intl->t("Revision {0}", $this->history->old->id)}</span><br>{$this->intl->t("by {username} {time}", [ "username" => "<a href='{$oldUser->route}'>{$oldUser->name}</a>", "time" => $dateTime->formatRelative($this->history->old->created, $this->request->dateTime) ])}" ],
      [ "#content" => "{$this->img($newUser->imageGetStyle("s1"), [], true, [ "class" => "fl" ])}<span class='big'>{$newRevisionName}</span><br>{$this->intl->t("by {username} {time}", [ "username" => "<a href='{$newUser->route}'>{$newUser->name}</a>", "time" => $dateTime->formatRelative($this->history->new->created, $this->request->dateTime) ])}" ],
      [ "class" => "aside" ],
    ]))->addColGroup([ [], [], [ "class" => "aside" ] ]);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    // @todo Should we try to recover from a backup?
    if (empty($this->table->rows)) {
      return $this->table->addRow([ [
        "#content" => "<div class='callout callout-info '>{$this->intl->t("No changes to show.")}</div>",
        "class"    => "tac",
        "colspan"  => 2
      ], [ "class" => "aside" ] ]);
    }
    return $this->table;
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
      $oldValueLanguage = array_key_exists($languageCode, $oldValue) ? $oldValue[$languageCode] : null;
      $newValueLanguage = array_key_exists($languageCode, $newValue) ? $newValue[$languageCode] : null;

      // We have changes, append them.
      if ($newValueLanguage !== $oldValueLanguage) {
        // Add the property heading if it's the first change we encounter.
        if ($changes === false) {
          $changes = true;
          $this->addPropertyHeading($name);
        }
        $this->formatStringDiff($oldValueLanguage, $newValueLanguage);
        $displayLanguage = \Locale::getDisplayLanguage($languageCode, $this->intl->locale);
        $this->table->addRow([
          [ "#content" => $oldValueLanguage ],
          [ "#content" => $newValueLanguage ],
          [ "#content" => "<div class='ico ico-help popup'><small class='content'>{$this->intl->t("Language: {language}", [ "language" => $displayLanguage ])}</small></div>", "class" => "aside" ]
        ]);
      }
    }

    return $this;
  }

  /**
   * Add a property heading to the diff table.
   *
   * @param string $propertyName
   *   The property's translated name.
   * @return $this
   */
  protected function addPropertyHeading($propertyName) {
    $this->table->addRow([ [ "#content" => $propertyName, "class" => "diff-label", "colspan" => 2 ], [ "class" => "aside" ] ]);
    return $this;
  }

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

    $this->formatStringDiff($oldValue, $newValue);

    $this->addPropertyHeading($name);
    $this->table->addRow([ [ "#content" => $oldValue ], [ "#content" => $newValue ], [ "class" => "aside" ] ]);

    return $this;
  }

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
  protected function formatStringDiff(&$oldValue, &$newValue) {
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

    return $this;
  }

}
