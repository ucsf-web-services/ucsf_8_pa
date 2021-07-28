<?php

namespace Acquia\Console\Cloud\Command\Helpers;

use Acquia\Console\Cloud\Command\AcquiaCloudCommandBase;
use Acquia\Console\Cloud\Command\DatabaseBackup\AcquiaCloudDatabaseBackupHelperTrait;
use Acquia\Console\Helpers\Command\PlatformCmdOutputFormatterTrait;
use AcquiaCloudApi\Endpoints\DatabaseBackups;
use AcquiaCloudApi\Response\OperationResponse;
use EclipseGc\CommonConsole\PlatformCommandInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AcquiaCloudDbBackupRestoreHelper.
 *
 * @package Acquia\Console\Cloud\Command\Helpers
 */
class AcquiaCloudDbBackupRestoreHelper extends AcquiaCloudCommandBase {

  use AcquiaCloudDatabaseBackupHelperTrait;
  use PlatformCmdOutputFormatterTrait;

  /**
   * {@inheritdoc}
   */
  protected static $defaultName = 'ace:database:backup:restore:helper';

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setDescription('Restore database backups.')
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
      $this->restore($info['environment_id'], $info['database_name'], $backup_id);
    }

    return 0;
  }

  /**
   * Restores a database from the given backup.
   *
   * @param string $env_id
   *   The environments uuid.
   * @param string $db_name
   *   The database name.
   * @param int $backup_id
   *   The backup id.
   *
   * @return \AcquiaCloudApi\Response\OperationResponse
   *   The response object.
   */
  protected function restore(string $env_id, string $db_name, int $backup_id): OperationResponse {
    $db_backups = new DatabaseBackups($this->acquiaCloudClient);
    return $db_backups->restore($env_id, $db_name, $backup_id);
  }

}
