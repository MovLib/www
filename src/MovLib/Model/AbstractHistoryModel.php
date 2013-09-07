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
namespace MovLib\Model;

use \MovLib\Exception\ErrorException;
use \MovLib\Exception\FileSystemException;
use \MovLib\Exception\HistoryException;
use \MovLib\Model\BaseModel;
use \ReflectionClass;

/**
 * Contains methods for models that manage histories.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractHistoryModel extends BaseModel {

  /**
   * The instance's id.
   * @var int
   */
  private $id;

  /**
   * The instance's model short name.
   * @var string
   */
  private $type;

  /**
   * The instance to be versioned
   * @var BaseModel
   */
  public $instance;

  /**
   * The Path to the repository
   * @var string
   */
  public $path;

  /**
   *
   * @param int $id
   *  The id of the instance to be versioned
   * @throws HistoryException
   *  If no instance with given id is found
   */
  public function __construct($id = null) {
    $this->type = $this->getShortName();
    $this->id = $id;
    $this->path = "{$_SERVER["DOCUMENT_ROOT"]}/history/{$this->type}/{$this->id}";

    $this->instance = $this->select(
      "SELECT *
        FROM `{$this->type}s`
        WHERE `{$this->type}_id` = ?",
      "d",
      [$this->id]
    );
    if (isset($this->instance[0]) === false) {
      throw new HistoryException("Could not find {$this->type} with ID '{$this->id}'!");
    }
  }

  /**
   * Abstract method which should write files
   */
  abstract protected function writeFiles();

  /**
   * Abstract method which should read files
   */
  abstract protected function readFiles();

  /**
   * Get the model's short class name (e.g. <em>movie</em> for <em>MovieHistoryModel</em>).
   *
   * The short name is the name of the current instance of this class without the namespace and the model suffix.
   *
   * @return string
   *   The short name of the class (lowercase) without the "HistoryModel" suffix.
   */
  public function getShortName() {
    return strtolower(substr((new ReflectionClass($this))->getShortName(), 0, -12));
  }

  /**
   * Creates a new folder, makes a git repository out of it, writes files and commits them.
   */
  public static function init() {
    createRepositoryFolder();
    initRepository();
    writeFiles();
    commit("initial commit");
  }

  /**
   * Create a new folder for the repository
   */
  public function createRepositoryFolder() {
    if (!is_dir(($this->path))) {
      mkdir($this->path, 0777, true);
    }
  }

  /**
   * Make a git repository out of the specified folder.
   *
   * @throws HistoryException
   *  If something went wrong with "git init"
   */
  public function initRepository() {
    try {
      exec("cd {$this->path} && git init");
    } catch (ErrorException $e) {
      throw new HistoryException("Error executing 'git init'", $e);
    }
  }

  /**
   * Checks in all changes and commits them
   *
   * @param int $author_id
   *  The user id of the author
   * @param string $message
   *  The commit message
   * @throws HistoryException
   *  If something went wrong during commit
   */
  public function commit($author_id, $message) {
    try {
      exec("cd {$this->path} && git add -A && git commit --author='{$author_id} <>' -m '{$message}'");
    } catch (ErrorException $e) {
      throw new HistoryException("Error commiting changes", $e);
    }
  }

  /**
   * Returns diff between two commits of one file as styled HTML
   *
   * @param string $head
   *  HEAD or hash of git commit
   * @param sting $ref
   *  Hash of git commit
   * @param string $filename
   *  Name of file in repository
   *
   * @throws HistoryException
   *  If something went wrong with "git diff"
   *
   * @return string
   *  Returns diff of one file as styled HTML
   */
  public function getDiffasHTML($head, $ref, $filename) {
    $output = array();
    $html = "";

    try {
      exec("cd {$this->path} && git diff {$ref} {$head} --word-diff='porcelain' {$filename}", $output);
    } catch (ErrorException $e) {
      throw new HistoryException("There was an error during 'git diff'", $e);
    }

    for ($i = 5; $i < count($output); $i++) {
      if ($output[$i][0] == " ") {
        $html .= substr($output[$i], 1);
      } else if ($output[$i][0] == "+") {
        $html .= "<span class='green'>" . substr($output[$i], 1) . "</span>";
      } else if ($output[$i][0] == "-") {
        $html .= "<span class='red'>" . substr($output[$i], 1) . "</span>";
      } else if ($output[$i][0] == "~") {
        $html .= "\n";
      }
    }

    return $html;
  }

  /**
   * Returns an array of associative arrays with commits
   *
   * @todo is subject safe?
   * @return array
   */
  public function getLastCommits() {
    $output = array();
    $format = '{"hash":"%H","author_id":%an, "timestamp":%at, "subject":"%s"}';

    exec("cd {$this->path} && git log --format='{$format}'", $output);

    $mapfunction = function($value) {
        return json_decode($value, true);
    };

    return array_map($mapfunction, $output);
  }

}
