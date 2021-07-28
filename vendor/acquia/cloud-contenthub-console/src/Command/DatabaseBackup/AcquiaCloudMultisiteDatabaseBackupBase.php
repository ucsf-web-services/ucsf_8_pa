<?php

namespace Acquia\Console\Cloud\Command\DatabaseBackup;

use Acquia\Console\Cloud\Command\AcquiaCloudCommandBase;
use Acquia\Console\Cloud\Platform\AcquiaCloudMultiSitePlatform;
use Acquia\Console\Cloud\Platform\AcquiaCloudPlatform;
use EclipseGc\CommonConsole\PlatformCommandInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * Class AcquiaCloudMultisiteDatabaseBackupBase.
 *
 * @package Acquia\Console\Cloud\Command\DatabaseBackup
 */
abstract class AcquiaCloudMultisiteDatabaseBackupBase extends AcquiaCloudCommandBase implements PlatformCommandInterface {

  use AcquiaCloudDatabaseBackupHelperTrait;

  /**
   * {@inheritdoc}
   */
  public static function getExpectedPlatformOptions(): array {
    return ['source' => AcquiaCloudMultiSitePlatform::getPlatformId()];
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $helper = $this->getHelper('question');
    /** @var \Acquia\Console\Cloud\Platform\AcquiaCloudMultiSitePlatform $platform */
    $platform = $this->getPlatform('source');
    $env_uuid = current($platform->get(AcquiaCloudPlatform::ACE_ENVIRONMENT_DETAILS));

    if (!$env_uuid) {
      $output->writeln('No site configured. Exiting...');
      return 1;
    }
    $databases = $this->getDatabasesByEnvironment($env_uuid);
    $dbs = [];
    foreach ($databases as $db_info) {
      $dbs[] = $db_info->name;
    }
    if ($input->hasOption('all') && $input->getOption('all')) {
      $this->doRunCommand($env_uuid, $dbs, $input, $output);
      return 0;
    }

    $choice = new ChoiceQuestion('Please choose the database you would like to manage a backup for:', $dbs);
    $db = $helper->ask($input, $output, $choice);

    return $this->doRunCommand($env_uuid, [$db], $input, $output);
  }

  /**
   * Runs the command implementation.
   *
   * @param string $env_id
   *   The environment id.
   * @param array $databases
   *   The array of databases.
   * @param \Symfony\Component\Console\Input\InputInterface $input
   *   The input object.
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *   The output stream.
   *
   * @return int
   *   Exit code.
   */
  abstract protected function doRunCommand(string $env_id, array $databases, InputInterface $input, OutputInterface $output): int;

}
