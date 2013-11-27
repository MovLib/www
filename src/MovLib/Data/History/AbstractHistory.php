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
namespace MovLib\Data\History;

use \MovLib\Exception\FileSystemException;
use \ReflectionClass;
use \RuntimeException;

/**
 * Abstract base class for all history classes.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractHistory extends \MovLib\Data\Database {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Exception code if nothing is to be commited.
   *
   * @var int
   */
  const E_NOTHING_TO_COMMIT = 100;

  /**
   * Exception code if a repository is in use.
   *
   * @var int
   */
  const E_REPOSITORY_IN_USE = 101;

  /**
   * Exception code for editing conflicts.
   *
   * @var int
   */
  const E_EDITING_CONFLICT = 102;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The commit hash.
   *
   * @var string
   */
  protected $commitHash;

  /**
   * The directory in which repositories are created.
   *
   * @var string
   */
  protected $context;

  /**
   * Names of files with plain text content.
   *
   * @var array
   */
  public $files;

  /**
   * Entity's unique ID (e.g. movie ID).
   *
   * @var int
   */
  public $id;

  /*
   * Files with conflicts
   *
   * ævar array
   */
  public $intersectingFiles;

  /**
   * The path to the repository.
   *
   * @var string
   */
  protected $path;

  /**
   * Names of files with serialized arrays as content.
   *
   * @var array
   */
  public $serializedArrays;
  
  /**
   * Names of files with a serialized arrays of ids as content.
   *
   * @var array
   */
  public $serializedIds;

  /**
   * Entity's short name.
   *
   * @var string
   */
  public $type;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new history model from given ID.
   *
   * @global \MovLib\Kernel $kernel
   * @param int $id
   *   The id of the object to be versioned.
   * @param string $context [optional]
   *   The directory in which the repository is found.
   */
  public function __construct($id, $context = "history") {
    global $kernel;
    $this->context = $context;
    $this->type = $this->getShortName();
    $this->id = $id;
    $this->path = "{$kernel->documentRoot}/private/{$this->context}/{$this->type}/{$this->id}";
  }
  

  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Commit staged files.
   *
   * @global \Movlib\Data\Session $session
   * @param string $message
   *   The commit message.
   * @return this
   * @throws \RuntimeException
   */
  private function commitFiles($message) {
    global $session;

    if (empty($this->getDirtyFiles())) {
      throw new RuntimeException("No changed files to commit!", self::E_NOTHING_TO_COMMIT);
    }

    exec("cd {$this->path} && git commit --author='{$session->userId} <>' -m '{$message}'", $output, $returnVar);
    if ($returnVar !== 0) {
      throw new RuntimeException("Error commiting changes");
    }
    return $this;
  }

  /**
   * Create GIT repository.
   *
   * Creates a directory in which a empty git repository is created. Then an empty initial commit is made and the commit
   * hash is stored in the database.
   *
   * @return string
   *   The commit hash.
   * @throws \RuntimeException
   * @throws \MovLib\Exception\DatabaseException
   */
  public function createRepository() {
    if (is_dir(($this->path)) === false && mkdir($this->path, 0777, true) === false) {
      throw new FileSystemException("Could not create directory for GIT repository.");
    }
    exec("cd {$this->path} && git init", $output, $returnVar);
    if ($returnVar !== 0) {
      throw new RuntimeException("Could not initialize GIT repository.");
    }
    exec("cd {$this->path} && git commit --allow-empty --author='init <>' -m 'initial commit'", $output, $returnVar);
    if ($returnVar !== 0) {
      throw new RuntimeException("Error while initial commit!");
    }
    return $this->getLastCommitHashFromGit();
  }

  /**
   * Get the file names of files that have changed between two commits.
   *
   * @param string $head
   *   Hash of git commit (newer one).
   * @param sting $ref
   *   Hash of git commit (older one).
   * @return array
   *   Numeric array of changed files.
   * @throws \RuntimeException
   */
  public function getChangedFiles($head, $ref) {
    exec("cd {$this->path} && git diff {$ref} {$head} --name-only", $output, $returnVar);
    if ($returnVar !== 0) {
      throw new RuntimeException("There was an error locating changed files");
    }
    return $output;
  }

  /**
   * Get the current commit hash of the entity from database.
   *
   * @return string
   *   Commit hash as string.
   * @throws \MovLib\Exception\DatabaseException
   * @throws \RuntimeException
   */
  private function getCommitHashFromDb() {
    $stmt = $this->query("SELECT `commit` FROM `{$this->type}s` WHERE `{$this->type}_id` = ? LIMIT 1", "d", [ $this->id ]);
    $stmt->bind_result($commitHash);
    if (!$stmt->fetch()) {
      throw new RuntimeException("Could not find commit hash of {$this->type} with ID '{$this->id}'!");
    }
    return $commitHash;
  }

  /**
   * Returns diff between two commits of an serialized array stored in a file.
   *
   * @param string $head
   *   Hash of git commit (newer one).
   * @param sting $ref
   *   Hash of git commit (older one).
   * @param string $filename
   *   Name of file in repository.
   * @return array
   *   Associative array with added, removed and edited items.
   */
  public function getArrayDiff($head, $ref, $filename) {
    $new = unserialize($this->getFileAtRevision($filename, $head));
    $old = unserialize($this->getFileAtRevision($filename, $ref));
        
    $added = ($old == false) ? $new : array_udiff($new, $old, [ $this, 'getArrayDiffDeepCompare' ]);
    $removed = ($old == false) ? [] : array_udiff($old, $new, [ $this, 'getArrayDiffDeepCompare' ]);
    
    $edited = array_uintersect($added, $removed, [ $this, 'getArrayDiffIdCompare' ]);
    $c = count($edited);
    for ($i = 0; $i < $c; ++$i) {
      $old = [];
      $e = count($removed);
      for ($j = 0; $j < $e; ++$j) {
        if ($removed[$j]['id'] == $edited[$i]['id']) {
          $old = $removed[$j];
          break;
        }
      }
      $edited[$i]['old'] = $old;
    }

    $added = array_udiff($added, $edited, [ $this, 'getArrayDiffIdCompare' ]);
    $removed = array_udiff($removed, $edited, [ $this, 'getArrayDiffIdCompare' ]);

    return [
     "added" => array_values($added),
     "removed" => array_values($removed),
     "edited" => array_values($edited)
    ];
  }

  /**
   * Helper method to deep compare two arrays.
   */
  private function getArrayDiffIdCompare($a, $b) {
    if (is_array($a) && is_array($b)) {
      if ($a['id'] == $b['id']) {
        return 0;
      }
      elseif ($a['id'] > $b['id']) {
        return 1;
      }
      else {
        return -1;
      }
    }
    else {
      return -1;
    }
  }

  /**
   * Helper method to deep compare two arrays.
   */
  private function getArrayDiffDeepCompare($a, $b) {
    if (is_array($a) && is_array($b)) {
      if (serialize($a) == serialize($b)) {
        return 0;
      }
      elseif (serialize($a) > serialize($b)) {
        return 1;
      }
      else {
        return -1;
      }
    }
    else {
      return -1;
    }
  }

  /**
   * Get the file names of files that are dirty in current working tree.
   *
   * @return array
   *   Numeric array of changed files.
   * @throws \RuntimeException
   */
  private function getDirtyFiles() {
    exec("cd {$this->path} && git diff --name-only HEAD && git ls-files --others", $output, $returnVar);
    if ($returnVar !== 0) {
      throw new RuntimeException("There was an error locating dirty files");
    }
    return $output;
  }

  /**
   * Get the last commit hash from the repository.
   *
   * @return string
   *   Last commit hash from the repository.
   * @throws \RuntimeException
   */
  private function getLastCommitHashFromGit() {
    exec("cd {$this->path} && git log --format='%H' --max-count=1", $output, $returnVar);
    if ($returnVar !== 0 || !isset($output[0])) {
      throw new RuntimeException("There was an error getting last commit hash from repository");
    }
    return $output[0];
  }

  /**
   * Get the content of a file at a certain revision.
   *
   * @param string $filename
   *   The file we want the content from.
   * @param string $ref
   *   Hash of git commit.
   * @return string
   *   The content of a file at a certain revision.
   * @throws \RuntimeException
   */
  public function getFileAtRevision($filename, $ref) {
    exec("cd {$this->path} && git show {$ref}:{$filename} 2<&-", $output, $returnVar);
    if ($returnVar === 128) {
      // filename exists on disk, but not in ref return an empty string.
      return "";
    }
    elseif ($returnVar !== 0 || !isset($output[0])) {
      throw new RuntimeException("There was an error getting '{$filename}' at revision '{$ref}'");
    }
    return $output[0];
  }
  
  /**
   * Returns diff between two commits of an serialized array of ids stored in a file.
   *
   * @param string $head
   *   Hash of git commit (newer one).
   * @param sting $ref
   *   Hash of git commit (older one).
   * @param string $filename
   *   Name of file in repository.
   * @return array
   *   Associative array with added and removed items.
   */
  public function getIdDiff($head, $ref, $filename) {
    $new = unserialize($this->getFileAtRevision($filename, $head));
    $old = unserialize($this->getFileAtRevision($filename, $ref));
    
    $added = ($old == false) ? $new : array_diff($new, $old);
    $removed = ($old == false) ? [] : array_diff($old, $new);
    
    return [
     "added" => array_values($added),
     "removed" => array_values($removed)
    ];
  }

  /**
   * Returns an array of associative arrays with commits.
   *
   * @param int $limit [optional]
   *   The number of commits which should be retrieved.
   * @return array
   *   Numeric array with associative array containing the commits.
   * @throws \RuntimeException
   */
  public function getLastCommits($limit = null) {
    $limit = isset($limit) ? " --max-count={$limit}" : "";
    $format = '{"hash":"%H","author_id":%an,"timestamp":%at,"subject":"%s"}';
    exec("cd {$this->path} && git log --format='{$format}'{$limit} --min-parents=1", $output, $returnVar);
    if ($returnVar !== 0) {
      throw new RuntimeException("There was an error getting last commits");
    }
    $c = count($output);
    for ($i = 0; $i < $c; ++$i) {
      $output[$i] = json_decode($output[$i], true);
    }
    return $output;
  }

  /**
   * Get the model's short class name (e.g. <em>movie</em> for <em>\MovLib\Data\History\Movie</em>).
   *
   * The short name is the lowercased name of the current instance of this class without the namespace.
   *
   * @return string
   *   The short name of the class (lowercase) without the namespace.
   */
  private function getShortName() {
    return strtolower((new ReflectionClass($this))->getShortName());
  }

  /**
   * Hide a repository.
   *
   * @global \MovLib\Kernel $kernel
   * @return this
   * @throws \RuntimeException
   */
  private function hideRepository() {
    global $kernel;
    $newPath = "{$kernel->documentRoot}/private/{$this->context}/{$this->type}/.{$this->id}";
    if (is_dir($newPath)) {
      throw new RuntimeException("Repository already hidden", self::E_REPOSITORY_IN_USE);
    }
    exec("mv {$this->path} {$newPath}", $output, $returnVar);
    if ($returnVar !== 0) {
      throw new RuntimeException("Error while renaming repository");
    }
    $this->path = $newPath;
    return $this;
  }

  /**
   * Write files to repository.
   *
   * @param array $data
   *   Associative array with data to store (use file names as keys)
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   * @throws \MovLib\Exception\FileSystemException
   */
  private function writeFiles(array $data) {
    foreach ($this->files as $key) {
      if (isset($data[$key])) {
        $this->writeToFile($key, $data[$key]);
      }
    }

    $c = count($this->serializedArrays);
    for ($i = 0; $i < $c; ++$i) {
      if (isset($data[$this->serializedArrays[$i]])) {
        $this->writeToFile($this->serializedArrays[$i], serialize($data[$this->serializedArrays[$i]]));
      }
    }

    return $this;
  }

  /**
   * Reset files.
   *
   * @param array $files
   *   Numeric array containing the file names to be reseted.
   * @return this
   * @throws \RuntimeException
   */
  private function resetFiles(array $files) {
    $this->unstageFiles($files);
    $filesToReset = implode(" ", $files);
    exec("cd {$this->path} && git checkout -- {$filesToReset}", $output, $returnVar);
    if ($returnVar !== 0) {
      throw new RuntimeException("Error resetting files!");
    }
    return $this;
  }

  /**
   * Commits all changes if possible.
   *
   * First the repository is hidden to prevent race conditions. Then the files which have changed are written. If there
   * was no other commit since <code>startEditing()</code> everything is commited and the repository is made visible
   * again. If there was another commit in the meantime, intersecting files are identified. If there are no intersecting
   * files everything is commited. Otherwise intersecting files are resetet to HEAD and an HistoryException is thrown.
   *
   * @param array $entity
   *   Associative array with data to store (use file names as keys).
   * @param type $message
   *   The commit Message.
   * @return string
   *   The hash of the current HEAD of the repository.
   * @throws \RuntimeException
   */
  public function saveHistory(array $entity, $message) {
    if (!isset($this->commitHash)) {
      throw new RuntimeException("startEditing() has to be called before saveHistory()!");
    }

    $this->hideRepository();
    $this->writeFiles($entity);
    $this->stageAllFiles();

    try {
      if ($this->commitHash != $this->getLastCommitHashFromGit()) {
        // If someone else commited in the meantime find intersecting files.
        $changedSinceStartEditing = $this->getChangedFiles("HEAD", $this->commitHash);
        $changedFiles = $this->getDirtyFiles();
        $this->intersectingFiles = array_intersect($changedFiles, $changedSinceStartEditing);
        // we reset the all dirty files.
        $this->resetFiles($changedFiles);
        throw new RuntimeException("Someone else edited the same information about the {$this->type}!", self::E_EDITING_CONFLICT);
      } else {
        $this->intersectingFiles = null;
        $this->commitFiles($message);
      }
    }
    finally {
      $this->unhideRepository();
    }
    return $this->getLastCommitHashFromGit();
  }

  /**
   * Stage all changed files.
   *
   * @return this
   * @throws \RuntimeException
   */
  private function stageAllFiles() {
    exec("cd {$this->path} && git add -A", $output, $returnVar);
    if ($returnVar !== 0) {
      throw new RuntimeException("Error adding files to stage!");
    }
    return $this;
  }

  /**
   * Stores the hash of current HEAD of the repository when editing starts.
   *
   * This methode should be called before editing actually starts. The hash is used to verify if between
   * <code>startEditing()</code> and <code>saveHistory()</code> someone else edited the history.
   *
   * @return this
   */
  public function startEditing() {
    $this->commitHash = $this->getCommitHashFromDb();
    return $this;
  }

  /**
   * Unhide a repository.
   *
   * @global \MovLib\Kernel $kernel
   * @return this
   * @throws \RuntimeException
   */
  private function unhideRepository() {
    global $kernel;
    $newPath = "{$kernel->documentRoot}/private/{$this->context}/{$this->type}/{$this->id}";
    if (is_dir($newPath)) {
      throw new RuntimeException("Repository not hidden");
    }
    exec("mv {$this->path} {$newPath}", $output, $returnVar);
    if ($returnVar !== 0) {
      throw new RuntimeException("Error while renaming repository");
    }
    else {
      $this->path = $newPath;
    }
    return $this;
  }

  /**
   * Unstage files.
   *
   * @param array $files
   *   Numeric array containing the file names to unstage.
   * @return this
   * @throws \RuntimeException
   */
  private function unstageFiles(array $files) {
    $filesToUnstage = implode(" ", $files);
    exec("cd {$this->path} && git reset --quiet HEAD {$filesToUnstage}", $output, $returnVar);
    if ($returnVar !== 0) {
      throw new RuntimeException("Error unstaging files!");
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

}
