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
namespace MovLib\Console\Command\Admin;

use \MovLib\Core\Intl;
use \MovLib\Component\String;
use \MovLib\Data\Forum\Forum;
use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * Defines the create forum command.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class CreateForum extends \MovLib\Console\Command\AbstractCommand {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "CreateForum";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName("create-forum");
    $this->setDescription("Create a new forum.");
    $this->addArgument("name", InputArgument::REQUIRED, "The (English) name of the new forum.");
    $this->addArgument("category", InputArgument::OPTIONAL, "The (English) name of the category the forum belongs to.");
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $forumName  = $this->getForumName();
    $categoryId = $this->getCategoryId();
    $forumId    = $this->getForumId();
    $class      = "\\MovLib\\Data\\Forum\\Forum{$forumId}";

    $this->writeVeryVerbose("New forum class will be <comment>{$class}</comment>...");
    file_put_contents("dr://src" . strtr($class, "\\", "/") . ".php", str_replace(
      [ "forum_id", "forum_name", "forum_route", "category_id" ],
      [ $forumId, $forumName, String::sanitizeFilename($forumName), $categoryId ],
      file_get_contents(__DIR__ . "/CreateForumScaffold.inc")
    ));

    $this->write("The new forum <comment>{$forumName}</comment> has been created, please perform the following actions:");
    $this->write([
      "Download the file from the server",
      "Update the @author annotation if necessary",
      "Enter the forum's description directly in the class",
      "Extract translations",
      "Translate the new strings",
      "Compile new nginx routes",
      "Commit and push the new forum to GitHub"
    ]);

    return 0;
  }

  /**
   * Get the forum's unique category identifier.
   *
   * @return integer
   *   The forum's unique category identifier.
   * @throws \InvalidArgumentException
   */
  protected function getCategoryId() {
    $categories = Forum::getCategories(new Intl(Intl::DEFAULT_CODE));
    $categoryId = null;
    $category   = $this->input->getArgument("category");

    if (empty($category)) {
      $category = $this->askWithChoices("Please select the category the new forum belongs to.", null, $categories);
      if ($category === null) {
        throw new \InvalidArgumentException("A forum has to belong to a category!");
      }
    }

    if (($categoryId = array_search($category, $categories))) {
      throw new \InvalidArgumentException("Couldn't find category: {$category}");
    }
    return (integer) $categoryId;
  }

  /**
   * Get the next unique forum identifier.
   *
   * @return integer
   *   The next unique forum identifier.
   */
  protected function getForumId() {
    $forumId = 0;
    foreach (new \RegexIterator(new \DirectoryIterator("dr://src/MovLib/Data/Forum"), "/Forum([0-9]+)\.php/", \RegexIterator::GET_MATCH) as $matches) {
      if ($matches[1] > $forumId) {
        $forumId = (integer) $matches[1];
      }
    }
    return $forumId;
  }

  /**
   * Get the forum's name.
   *
   * @return string
   *   The forum's name.
   * @throws \InvalidArgumentException
   */
  protected function getForumName() {
    $name = $this->input->getArgument("name");
    $names = Forum::getAll(new Intl(Intl::DEFAULT_CODE));
    if (in_array($name, $names)) {
      throw new \InvalidArgumentException("Forum '{$name}' already exists.");
    }
    return $name;
  }

}
