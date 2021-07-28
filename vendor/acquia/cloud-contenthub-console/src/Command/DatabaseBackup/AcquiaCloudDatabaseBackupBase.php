<?php

namespace Acquia\Console\Cloud\Command\DatabaseBackup;

use Acquia\Console\Cloud\Command\AcquiaCloudCommandBase;
use EclipseGc\CommonConsole\PlatformCommandInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * Class AcquiaCloudDatabaseBackupBase.
 *
 * @package Acquia\Console\Cloud\Command\DatabaseBackup
 */
abstract class AcquiaCloudDatabaseBackupBase extends AcquiaCloudCommandBase implements PlatformCommandInterface {

  use AcquiaCloudDatabaseBackupHelperTrait;

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $helper = $this->getHelper('question');
    /** @var \Acquia\Console\Cloud\Platform\AcquiaCloudPlatform $platform */
    $platform = $this->getPlatform('source');
    $options = $platform->getPlatformSites();
    if (empty($options)) {
      $output->writeln('No sites have been registered in the current platform.');
      return 0;
    }

    $sites = [];
    foreach ($options as $uuid => $site_data) {
      $sites[$uuid] = $site_data['uri'];
    }

    if ($input->hasOption('all') && $input->getOption('all')) {
      foreach ($sites as $uuid => $site) {
        $databases = $this->getDatabasesByEnvironment($uuid);
        $db_info = reset($databases);
        $this->doRunCommand($uuid, $db_info->name, $input, $output);
      }
      return 0;
    }

    $choice = new ChoiceQuestion('Please choose the site you would like to manage a database backup for:', $sites);
    $site = $helper->ask($input, $output, $choice);

    $databases = [];
    foreach ($this->getDatabasesByEnvironment($site) as $db_info) {
      $databases[$db_info->id] = $db_info->name;
    }
    $choice = new ChoiceQuestion('Choose a database:', array_values($databases));
    $db = $helper->ask($input, $output, $choice);

    return $this->doRunCommand($site, $db, $input, $output);
  }

  /**
   * Runs the command implementation.
   *
   * @param string $env_id
   *   The environment id.
   * @param string $db
   *   The database name.
   * @param \Symfony\Component\Console\Input\InputInterface $input
   *   The input object.
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *   The output stream.
   *
   * @return int
   *   Exit code.
   */
  abstract protected function doRunCommand(string $env_id, string $db, InputInterface $input, OutputInterface $output): int;

}
