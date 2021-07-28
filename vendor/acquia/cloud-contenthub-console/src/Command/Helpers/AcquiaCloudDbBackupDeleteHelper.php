<?php

namespace Acquia\Console\Cloud\Command\Helpers;

use Acquia\Console\Cloud\Command\AcquiaCloudCommandBase;
use Acquia\Console\Cloud\Command\DatabaseBackup\AcquiaCloudDatabaseBackupHelperTrait;
use Acquia\Console\Helpers\Command\PlatformCmdOutputFormatterTrait;
use EclipseGc\CommonConsole\PlatformCommandInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AcquiaCloudDbBackupRestoreHelper.
 *
 * @package Acquia\Console\Cloud\Command\Helpers
 */
class AcquiaCloudDbBackupDeleteHelper extends AcquiaCloudCommandBase {

  use AcquiaCloudDatabaseBackupHelperTrait;
  use PlatformCmdOutputFormatterTrait;

  /**
   * {@inheritdoc}
   */
  protected static $defaultName = 'ace:database:backup:delete:helper';

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setDescription('Delete database backups.')
      ->setHidden(TRUE)
      ->addOption('backups', 'bid', InputOption::VALUE_REQUIRED, 'Database backups array of backup_id\'s keyed by site_id\'s.');
  }

  /**
   * {@inheritdoc}
   */
  public static function getExpectedPlatformOptions(): array {
    return ['source' => PlatformCommandInterface::ANY_PLATFORM];
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output): int {
    $backups = $input->getOption('backups');
    /** @var array $info */
    foreach ($backups as $backup_id => $info) {
      $this->delete($info['environment_id'], $info['database_name'], $backup_id);
    }
    return 0;
  }

}
