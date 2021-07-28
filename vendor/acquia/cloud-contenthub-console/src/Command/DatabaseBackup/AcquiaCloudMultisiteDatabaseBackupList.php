<?php

namespace Acquia\Console\Cloud\Command\DatabaseBackup;

use Acquia\Console\Helpers\Command\PlatformCmdOutputFormatterTrait;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AcquiaCloudMultisiteDatabaseBackupList.
 *
 * @package Acquia\Console\Cloud\Command\DatabaseBackup
 */
class AcquiaCloudMultisiteDatabaseBackupList extends AcquiaCloudMultisiteDatabaseBackupBase {

  use AcquiaCloudDatabaseBackupHelperTrait;
  use PlatformCmdOutputFormatterTrait;

  /**
   * {@inheritdoc}
   */
  protected static $defaultName = 'ace-multi:database:backup:list';

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setDescription('Lists database backups for ACE Multi-site environments.');
    $this->addOption('all', 'a', InputOption::VALUE_NONE, 'List backups for all databases in the multisite platform.');
    $this->addOption('silent', 's', InputOption::VALUE_NONE, 'Returns list, but does not send it to the output.');
    $this->setAliases(['ace-dblm']);
  }

  /**
   * {@inheritdoc}
   */
  protected function doRunCommand(string $env_id, array $databases, InputInterface $input, OutputInterface $output): int {
    $list = [];
    foreach ($databases as $db_name) {
      $list[$db_name] = $this->getBackupList($env_id, $db_name);
    }
    if (empty($list) && !isset($list)) {
      $output->writeln('No backups found for any database.');
      return 1;
    }
    $silent = [];
    $table = new Table($output);
    $table->setHeaders(['DB Name', 'Completed', 'ID', 'Type']);
    foreach ($list as $db_name => $backups) {
      foreach ($backups as $backup) {
        $silent[] = [
          'env_id' => $env_id,
          'database' => $db_name,
          'completed_at' => $backup->completedAt,
          'backup_id' => $backup->id
        ];
        $table->addRow([
          $db_name,
          $backup->completedAt,
          $backup->id, $backup->type
        ]);
      }
    }
    if ($input->getOption('silent') && !empty($silent)) {
      $output->writeln($this->toJsonSuccess($silent));
      return 0;
    }
    $table->render();

    return 0;
  }

}
