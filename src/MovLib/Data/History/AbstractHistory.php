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
   * The commit hash.
   *
   * @var string
   */
  protected $commitHash = null;

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
   * Flag which indicates if repository is hidden to prevent access.
   *
   * @var boolean
   */
  private $repositoryHidden = false;

  /**
   * Entity's short name.
   *
   * @var string
   */
  protected $type;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods

  /**
   * Instantiate new history model from given ID.
   *
   * @param int $id
   *   The id of the object to be versioned.
   */
  public function __construct($id) {
    $this->type = $this->getShortName();
    $this->id = $id;

    // verify if id is valid.
    $result = $this->select("SELECT `{$this->type}_id` FROM `{$this->type}s` WHERE `{$this->type}_id` = ? LIMIT 1", "d", [ $this->id ]);
    if (empty($result[0])) {
      throw new HistoryException("Could not find {$this->type} with ID '{$this->id}'!");
    }

    $this->path = "{$_SERVER["DOCUMENT_ROOT"]}/history/{$this->type}/{$this->id}";
  }

  /**
   * Destroy the history model and rename repository if necessary.
   */
  public function __destruct() {
    parent::__destruct();
    if ($this->repositoryHidden) {
      $this->unhideRepository();
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Abstract Methods


  /**
   * Write files to repository.
   *
   * @param array $data
   *   Associative array with data to store (use file name as key)
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   * @throws \MovLib\Exception\FileSystemException
   */
  abstract protected function writeFiles(array $data);

  // ------------------------------------------------------------------------------------------------------------------- Methods

  /**
   * Stage all changed files.
   *
   * @return this
   * @throws \MovLib\Exception\HistoryException
   */
  private function stageAllFiles() {
    exec("cd {$this->path} && git add -A", $output, $returnVar);
    if ($returnVar !== 0) {
      throw new HistoryException("Error adding files to stage!");
    }
    return $this;
  }

  /**
   * Unstage files
   *
   * @param array $files
   *   Numeric array containing the file names to unstage.
   * @return this
   * @throws \MovLib\Exception\HistoryException
   */
  private function unstageFiles(array $files) {
    $filesToUnstage = implode(" ", $files);
    exec("cd {$this->path} && git reset HEAD {$filesToUnstage}", $output, $returnVar);
    if ($returnVar !== 1) { // not 0!
      throw new HistoryException("Error unstaging files!");
    }
    return $this;
  }

  /**
   * Reset unstaged files
   *
   * @param array $files
   *   Numeric array containing the file names to be reseted. Only unstaged files can be reseted!
   * @return this
   * @throws \MovLib\Exception\HistoryException
   */
  private function resetFiles(array $files) {
    $filesToReset = implode(" ", $files);
    exec("cd {$this->path} && git checkout -- {$filesToReset}", $output, $returnVar);
    if ($returnVar !== 0) {
      throw new HistoryException("Error resetting files!");
    }
    return $this;
  }

  /**
   * Commit staged files
   *
   * @global \Movlib\Data\Session $session
   * @param string $message
   *   The commit message.
   * @return this
   * @throws \MovLib\Exception\HistoryException
   */
  private function commitFiles($message) {
    global $session;
    exec("cd {$this->path} && git commit --author='{$session->userId} <>' -m '{$message}'", $output, $returnVar);
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
  private function getChangedFiles($head = null, $ref = null) {
    exec("cd {$this->path} && git diff {$ref} {$head} --name-only", $output, $returnVar);
    if ($returnVar !== 0) {
      throw new HistoryException("There was an error locating changed files");
    }
    return $output;
  }

  /**
   * Get the last commit hash from the repository.
   *
   * @return string
   *   Last commit hash from the repository.
   * @throws HistoryException
   */
  private function getLastCommitHash() {
    exec("cd {$this->path} && git log --format='%H' --max-count=1", $output, $returnVar);
    if ($returnVar !== 0 || !isset($output[0])) {
      throw new HistoryException("There was an error getting last commit hash from repository");
    }
    return $output[0];
  }

  /**
   * Returns an array of associative arrays with commits.
   *
   * @todo Is subject safe?
   * @param int $limit [optional]
   *   The number of commits which should be retrieved.
   * @return array
   *   Numeric array with associative array containing the commits.
   * @throws \MovLib\Exception\HistoryException
   */
  public function getLastCommits($limit = null) {
    $limit = isset($limit)? " --max-count={$limit}" : "";
    $format = '{"hash":"%H","author_id":%an,"timestamp":%at,"subject":"%s"}';
    exec("cd {$this->path} && git log --format='{$format}'{$limit}", $output, $returnVar);
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
   * Remembers the state of the repository when editing starts.
   *
   * This methode should be called before editing actually starts. The hash is used to verify if between
   * <code>startEditing</code> and <code>saveHistory</code> someone else edited the history.
   *
   * @return this
   */
  public function startEditing() {
    $this->commitHash = $this->getCommitHash();
    return $this;
  }

  public function saveHistory(array $entitiy, $message) {
    if (!isset($this->commitHash)) {
      throw new HistoryException("startEditing() have to be called bevore saveHistory()!");
    }

    $this->hideRepository();
    $this->writeFiles($entitiy);
    $this->stageAllFiles();

    try {
      if ($this->commitHash != $this->getLastCommitHash()) {
        // If someone else commited in the meantime find intersecting files.
        $changedSinceStartEditing = $this->getChangedFiles("HEAD", $this->commitHash);
        $changedFiles = $this->getChangedFiles();
        $intersection = array_intersect($changedFiles, $changedSinceStartEditing);
        if (empty($intersection)) {
          // If there are no intersecting files we can commit normaly.
          $this->commitFiles($message);
        } else {
          // Else we reset the intersecting files.
          $this->unstageFiles($intersection);
          $this->resetFiles($intersection);
          // If there are files left which can be commited do it.
          if (!empty($this->getChangedFiles())) {
            $this->commitFiles($message);
          }
          // @todo: show intersection to user instead of this exception.
          throw new HistoryException("Someone else edited the same information about the {$this->type}!");
        }
      } else {
        $this->commitFiles($message);
      }
    }
    finally {
      $this->unhideRepository();
    }

    return $this->getLastCommitHash();
  }

  /**
   * Hide a repository
   *
   * @return this
   * @throws \MovLib\Exception\HistoryException
   * @throws \MovLib\Exception}FileSystemException
   */
  private function hideRepository() {
    $newPath = "{$_SERVER["DOCUMENT_ROOT"]}/history/{$this->type}/.{$this->id}";
    if ($this->repositoryHidden || is_dir($newPath)) {
      throw new HistoryException("Repository already hidden");
    }
    exec("mv {$this->path} {$newPath}", $output, $returnVar);
    if ($returnVar !== 0) {
      throw new FileSystemException("Error while renaming repository");
    }
    else {
      $this->repositoryHidden = true;
      $this->path = $newPath;
    }
    return $this;
  }

  /**
   * Unhide a repository
   *
   * @return this
   * @throws \MovLib\Exception\HistoryException
   * @throws \MovLib\Exception}FileSystemException
   */
  private function unhideRepository() {
    $newPath = "{$_SERVER["DOCUMENT_ROOT"]}/history/{$this->type}/{$this->id}";
    if ($this->repositoryHidden === false || is_dir($newPath)) {
      throw new HistoryException("Repository not hidden");
    }
    exec("mv {$this->path} {$newPath}", $output, $returnVar);
    if ($returnVar !== 0) {
      throw new FileSystemException("Error while renaming repository");
    }
    else {
      $this->repositoryHidden = false;
      $this->path = $newPath;
    }
    return $this;
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
   * Get the current commit hash of the entity from DB
   *
   * @return string
   *   Commit hash as string.
   * @throws \MovLib\Exception\DatabaseException
   * @throws \MovLib\Exception\HistoryException
   */
  private function getCommitHash() {
    $result = $this->select("SELECT `commit` FROM `{$this->type}s` WHERE `{$this->type}_id` = ? LIMIT 1", "d", [$this->id]);
    if (!isset($result[0]["commit"])) {
      throw new HistoryException("Could not find commit hash of {$this->type} with ID '{$this->id}'!");
    }
    return $result[0]["commit"];;
  }

  /**
   * Get the model's short class name (e.g. <em>movie</em> for <em>MovLib\Data\History\Movie</em>).
   *
   * The short name is the name of the current instance of this class without the namespace lowercased.
   *
   * @return string
   *   The short name of the class (lowercase) without the namespace.
   */
  private function getShortName() {
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

}
