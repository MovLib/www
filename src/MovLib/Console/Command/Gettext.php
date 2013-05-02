<?php

/* !
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
namespace MovLib\Console\Command;

use \MovLib\Console\Command\AbstractCommand;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * Description of Gettext
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Gettext extends AbstractCommand {

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    parent::__construct('gettext');
    $this->setDescription('Generate gettext translation files.');
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $output->writeln('<info>Extracting strings from source files.</info>');
    chdir('/var/www/src');
    system('find . -iname "*.php" | xargs xgettext --' . implode(' --', [
      'language=PHP',
      'keyword=__:1',
      'keyword=__:1,2c',
      'keyword=n__:1,2',
      'keyword=n__:1,2,4c',
      'default-domain=movlib',
      'output=movlib.pot',
      'output-dir=/var/www/translations/en_US/LC_MESSAGES',
      'from-code=utf-8',
      'join-existing',
      'add-comments=#',
      'add-location',
      'width=120',
      'copyright-holder="MovLib, the free movie library."',
      'package-name=MovLib',
      'package-version=0.0.1-dev',
      'msgid-bugs-address=webmaster@movlib.org',
    ]));
    $output->writeln('<info>Finished!</info>');
  }

}
