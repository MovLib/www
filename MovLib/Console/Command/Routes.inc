<?php

/**
 * Command for console application.
 *
 * The command namespace is part of the console namespace within the MovLib software. The console application is used to
 * run several administrative tasks. The MovLib software does not have any administrative backend, instead all such
 * tasks are handled with console applications.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright (c) 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/ movlib.org
 * @since 0.0.1
 */
namespace MovLib\Console\Command;

use MovLib\Console\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Compile all defined routes and reload nginx.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright (c) 2013-present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/ movlib.org
 */
class Routes extends AbstractCommand {

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    parent::__construct('routes');
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setDescription('Compile all routes and reload nginx.')
    ;
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->input = $input;
    $this->output = $output;
  }
}