<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link http://movlib.org/ MovLib}.
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
namespace MovLib\Data\History;

use \MovLib\Exception\FileSystemException;
use \MovLib\Exception\HistoryException;
use \ReflectionClass;

/**
 * Abstract base class for all history classes.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractHistory extends \MovLib\Data\Database {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Flag determining if we are on a branch other than "master".
   *
   * @var boolean
   */
  protected $customBranch = false;

  /**
   * Associative array containing this entity's database table (all or selected columns).
   *
   * @var array
   */
  public $entity;

  /**
   * Entity's unique ID (e.g. movie ID).
   *
   * @var int
   */
  protected $id;

  /**
   * The path to the repository.
   *
   * @var string
   */
  protected $path;

  /**
   * Entity's short name.
   *
   * @var string
   */
  protected $type;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new history model from given ID and gather basic information.
   *
   * @param int $id
   *   The id of the object to be versioned.
   * @param array $columns [optional]
   *   Numeric array containing the column names.
   * @param array $dynamicColumns [optional]
   *   Numeric array containing the dynamic column names.
   */
  public function __construct($id, array $columns = [], array $dynamicColumns = []) {
    $this->type = $this->getShortName();
    $result = $this->select(
      "SELECT {$this->getColumnsForSelectQuery($columns, $dynamicColumns)} FROM `{$this->type}s` WHERE `{$this->type}_id` = ? LIMIT 1",
      "d",
      [ $this->id ]
    );
    if (empty($result[0])) {
      throw new HistoryException("Could not find {$this->type} with ID '{$this->id}'!");
    }
    $this->id = $id;
    $this->entity = $result[0];
    $this->path = "{$_SERVER["DOCUMENT_ROOT"]}/history/{$this->type}/{$this->id}";

    // @todo Should be called when entity is first created!
    if (is_dir("{$this->path}/.git") === false) {
      $this->createRepository();
    }
  }

  /**
   * Destroy the history model and the custom branch if necessary.
   */
  public function __destruct() {
    parent::__destruct();
    if ($this->customBranch) {
      $this->destroyUserBranch();
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Abstract Methods


  /**
   * Write files to repository.
   *
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   * @throws \MovLib\Exception\FileSystemException
   */
  abstract protected function writeFiles();


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * C
   * Commit the current state of the object
   *
   * A new branch is created, files are written, commited and merged into master.
   * At the end the temporary branch is destroyed.
   *
   * @param string $message
   *   The commit message.
   * @return this
   */
  public function saveHistory($message) {
    return $this->getUserBranch()->writeFiles()->commit($message)->mergeIntoMaster()->destroyUserBranch();
  }

  /**
   * Checks in all changes and commits them.
   *
   * @global \Movlib\Data\Session $session
   * @param string $message
   *   The commit message.
   * @return this
   * @throws \MovLib\Exception\HistoryException
   */
  public function commit($message) {
    global $session;
    exec("cd {$this->path} && git add -A && git commit --author='{$session->id} <>' -m '{$message}'", $output, $returnVar);
    if ($returnVar !== 0) {
      if (empty($this->getChangedFiles())) {
        throw new HistoryException("No changed files to commit");
      }
      else {
        throw new HistoryException("Error commiting changes");
      }
    }
    return $this;
  }

  /**
   * Returns diff between two commits of one file as styled HTML.
   *
   * @todo Move to presentation.
   * @param string $head
   *   Hash of git commit (newer one).
   * @param sting $ref
   *   Hash of git commit (older one).
   * @param string $filename
   *   Name of file in repository.
   * @return string
   *   Returns diff of one file as styled HTML.
   * @throws \MovLib\Exception\HistoryException
   */
  public function getDiffasHTML($head, $ref, $filename) {
    $html = "";

    exec("cd {$this->path} && git diff {$ref} {$head} --word-diff='porcelain' {$filename}", $output, $returnVar);
    if ($returnVar !== 0) {
      throw new HistoryException("There was an error during 'git diff'");
    }

    $c = count($output);
    for ($i = 5; $i < $c; ++$i) {
      if ($output[$i][0] == " ") {
        $html .= substr($output[$i], 1);
      }
      elseif ($output[$i][0] == "+") {
        $tmp = substr($output[$i], 1);
        $html .= "<span class='green'>{$tmp}</span>";
      }
      elseif ($output[$i][0] == "-") {
        $tmp = substr($output[$i], 1);
        $html .= "<span class='red'>{$tmp}</span>";
      }
    }

    return $html;
  }

  /**
   * Get the file names of files that have changed.
   *
   * @param string $head [optional]
   *   Hash of git commit (newer one).
   * @param sting $ref [optional]
   *   Hash of git commit (older one).
   * @return array
   *   Numeric array of changed files.
   * @throws \MovLib\Exception\HistoryException
   */
  public function getChangedFiles($head = null, $ref = null) {
    exec("cd {$this->path} && git diff {$ref} {$head} --name-only", $output, $returnVar);
    if ($returnVar !== 0) {
      throw new HistoryException("There was an error locating changed files");
    }
    return $output;
  }

  /**
   * Returns an array of associative arrays with commits.
   *
   * @todo Is subject safe?
   * @return array
   *   Numeric array with associative array containing the commits.
   * @throws \MovLib\Exception\HistoryException
   */
  public function getLastCommits() {
    $format = '{"hash":"%H","author_id":%an,"timestamp":%at,"subject":"%s"}';
    exec("cd {$this->path} && git log --format='{$format}'", $output, $returnVar);
    if ($returnVar !== 0) {
      throw new HistoryException("There was an error getting last commits");
    }
    $c = count($output);
    for ($i = 0; $i < $c; ++$i) {
      $output[$i] = json_decode($output[$i], true);
    }
    return $output;
  }

  /**
   * Write file to repository path.
   *
   * @param string $filename
   *   The filename.
   * @param string $content
   *   The content.
   * @return this
   * @throws \MovLib\Exception}FileSystemException
   */
  protected function writeToFile($filename, $content) {
    if (file_put_contents("{$this->path}/{$filename}", $content) === false) {
      throw new FileSystemException("Error writing to file.");
    }
    return $this;
  }

  /**
   * Write specific columns of a related database row to a file.
   *
   * If no columns are given, all columns are selected.
   *
   * @param string $relation
   *   A database relation (e.g. <i>movies_titles</i>).
   * @param array $columns [optional]
   *   Array of columns to be written to the file.
   * @param array $dynColumns [optional]
   *   Array of dynamic colums to be written to the file.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  protected function writeRelatedRowsToFile($relation, array $columns = [], array $dynColumns = []) {
    $result = $this->select(
      "SELECT {$this->getColumnsForSelectQuery($columns, $dynColumns)} FROM `{$relation}` WHERE `{$this->type}_id` = ? LIMIT 1",
      "d",
      [ $this->id ]
    );
    if (empty($result[0])) {
      // @todo ????
    }
    foreach ($dynColumns as $value) {
      $result[0][$value] = json_decode($result[0][$value], true);
    }
    $this->writeToFile($relation, json_encode($result));
    return $this;
  }

  /**
   * Build query select part.
   *
   * Build string which can be used in a SELECT query (e.g. <code>["title"]["dyn_comment]</code> will become
   * <code>`title`, COLUMN_JSON(dyn_comment) AS `dyn_comment`</code>). If no columns or dynamic columns are given
   * all columns (*) are selected. If dynamic columns are given these are selectet as COLUMN_JSON.
   *
   * @param array $columns [optional]
   *   Numeric array containing the column names.
   * @param array $dynamicColumns [optional]
   *   Numeric array containing the dynamic column names.
   * @return string
   *   SELECT part of query.
   */
  private function getColumnsForSelectQuery(array $columns = null, array $dynamicColumns = null) {
    if (!$columns && !$dynamicColumns) {
      return "*";
    }
    $select = null;
    $c = count($columns);
    for ($i = 0; $i < $c; ++$i) {
      if ($i !== 0) {
        $select .= ", ";
      }
      $select .= "`{$columns[$i]}`";
    }
    $c = count($dynamicColumns);
    for ($i = 0; $i < $c; ++$i) {
      if ($select || $i !== 0) {
        $select .= ", ";
      }
      $select .= "COLUMN_JSON(`{$dynamicColumns[$i]}`) AS `{$dynamicColumns[$i]}`";
    }
    return $select;
  }

  /**
   * Get the model's short class name (e.g. <em>movie</em> for <em>MovLib\Data\History\Movie</em>).
   *
   * The short name is the name of the current instance of this class without the namespace lowercased.
   *
   * @return string
   *   The short name of the class (lowercase) without the namespace.
   */
  public function getShortName() {
    return strtolower((new ReflectionClass($this))->getShortName());
  }

  /**
   * Create GIT repository.
   *
   * @return this
   * @throws \MovLib\Exception\HistoryException
   */
  public function createRepository() {
    if (is_dir(($this->path)) === false && mkdir($this->path, 0777, true) === false) {
      throw new FileSystemException("Could not create directory for GIT repository.");
    }
    exec("cd {$this->path} && git init", $output, $returnVar);
    if ($returnVar !== 0) {
      throw new HistoryException("Could not initialize GIT repository.");
    }
    return $this;
  }

  /**
   * Create a new 'user branch' and check it out.
   *
   * @global \Movlib\Data\Session $session
   *
   * @throws HistoryException
   *  If something went wrong during creating  or changing into a new branch.
   */
  private function getUserBranch() {
    global $session;

    $output = array();
    $returnVar = null;

    exec("cd {$this->path} && git checkout -q -B {$session->id}", $output, $returnVar);
    if ($returnVar == 0) {
      $this->customBranch = true;
    } else {
      throw new HistoryException("Error while creating new branch");
    }
  }

  /**
   * Destroy the custom 'user branch'
   *
   * @global \Movlib\Data\Session $session
   *
   * @throws HistoryException
   *  If something went wrong during destroying this branch.
   */
  private function destroyUserBranch() {
    global $session;

    $output = array();
    $returnVar = null;

    $this->checkoutBranch("master");

    exec("cd {$this->path} && git branch -D {$session->id}", $output, $returnVar);
    if ($returnVar == 0) {
      $this->customBranch = false;
    } else {
      throw new HistoryException("Error while destroying branch!");
    }
  }

  /**
   * Change Branch
   *
   * @param string $name
   *  The name of the branch which should be checked out
   *
   * @throws HistoryException
   *  If something went wrong checking out the branch
   */
  private function checkoutBranch($name) {
    $output = array();
    $returnVar = null;

    exec("cd {$this->path} && git checkout -q {$name}", $output, $returnVar);
    if ($returnVar != 0) {
      throw new HistoryException("Error checking out branch '{$name}'");
    }
  }

  /**
   * Merging 'user branch' back into master
   *
   * @todo Handling of merge conflicts
   *
   * @global \Movlib\Data\Session $session
   *
   * @throws HistoryException
   *  If something went wrong during git merge.
   */
  private function mergeIntoMaster() {
    global $session;

    $output = array();
    $returnVar = null;

    $this->checkoutBranch("master");

    exec("cd {$this->path} && git merge {$session->id}", $output, $returnVar);
    if ($returnVar != 0) {
      throw new HistoryException("Error while merging into master!");
    }
  }
}
