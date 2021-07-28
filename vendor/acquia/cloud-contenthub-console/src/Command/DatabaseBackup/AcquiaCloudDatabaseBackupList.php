<?php

namespace Acquia\Console\Cloud\Command\DatabaseBackup;

use Acquia\Console\Helpers\Command\PlatformCmdOutputFormatterTrait;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AcquiaCloudDatabaseBackupList.
 *
 * @package Acquia\Console\Cloud\Command\DatabaseBackup
 */
class AcquiaCloudDatabaseBackupList extends AcquiaCloudDatabaseBackupBase {

  use PlatformCmdOutputFormatterTrait;
  use AcquiaCloudDatabaseBackupHelperTrait;

  /**
   * {@inheritdoc}
   */
  protected static $defaultName = 'ace:database:backup:list';

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setDescription('Lists database backups.');
    $this->addOption('all', 'a', InputOption::VALUE_NONE, 'Perform backup operation for all sites in the platform.');
    $this->addOption('silent', 's', InputOption::VALUE_NONE, 'Returns list, but does not send it to the output.');
    $this->setAliases(['ace-dbl']);
  }

  /**
   * {@inheritdoc}
   */
  protected function doRunCommand(string $env_id, string $db, InputInterface $input, OutputInterface $output): int {
    $list = $this->getBackupList($env_id, $db);
    if (!$list) {
      $output->writeln('No backups found.');
      return 1;
    }

    $table = new Table($output);
    $table->setHeaders(['Completed', 'ID', 'Type']);
    /** @var \AcquiaCloudApi\Response\BackupResponse $backup */
    foreach ($list as $backup) {
      $silent[] = [
        'env_id' => $env_id,
        'database' => $db,
        'completed_at' => $backup->completedAt,
        'backup_id' => $backup->id
      ];
      $table->addRow([$backup->completedAt, $backup->id, $backup->type]);
    }

    if ($input->getOption('silent') && !empty($silent)) {
      $output->writeln($this->toJsonSuccess($silent));
      return 0;
    }

    $table->render();

    return 0;
  }

}
