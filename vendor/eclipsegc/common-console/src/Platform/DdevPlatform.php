<?php

namespace EclipseGc\CommonConsole\Platform;

use Consolidation\Config\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Process\Process;

/**
 * Class DdevPlatform
 *
 * @package EclipseGc\CommonConsole\Platform
 */
class DdevPlatform extends PlatformBase {

  /**
   * {@inheritdoc}
   */
  public static function getPlatformId(): string {
    return 'DDEV';
  }

  /**
   * {@inheritdoc}
   */
  public static function getQuestions() {
    return [
      'ddev.local.wrapper_dir' => new Question("Directory containing the .ddev directory: "),
      'ddev.remote.vendor_dir' => new Question("The location of the vendor dir within the container: "),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function execute(Command $command, InputInterface $input, OutputInterface $output) : int {
    $process = Process::fromShellCommandline("cd {$this->get('ddev.local.wrapper_dir')}; ddev exec {$this->get('ddev.remote.vendor_dir')}/bin/commoncli {$input->__toString()}");
    return $this->runner->run($process, $this, $output);
  }

}
