<?php

namespace Acquia\Console\Cloud\Command\DatabaseBackup;

use Acquia\Console\Cloud\Command\AceNotificationHandlerTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * Class AcquiaCloudDatabaseBackupDelete.
 *
 * @package Acquia\Console\Cloud\Command\DatabaseBackup
 */
class AcquiaCloudMultisiteDatabaseBackupDelete extends AcquiaCloudMultisiteDatabaseBackupBase {

  use AceNotificationHandlerTrait;
  use AcquiaCloudDatabaseBackupHelperTrait;

  /**
   * {@inheritdoc}
   */
  protected static $defaultName = 'ace-multi:database:backup:delete';

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setDescription('Deletes database backups for ACE Multi-site environments.');
    $this->addOption('wait', 'w', InputOption::VALUE_NONE, 'Wait for task until it is completed.');
    $this->setAliases(['ace-dbdelm']);
  }

  /**
   * {@inheritdoc}
   */
  protected function doRunCommand(string $env_id, array $databases, InputInterface $input, OutputInterface $output): int {
    $db = current($databases);
    $list = $this->listBackups($env_id, $db, $this->acquiaCloudClient);
    if (!$list) {
      return 1;
    }

    $choice = new ChoiceQuestion('Choose a backup to delete:', $list);
    $backup = $this->getHelper('question')->ask($input, $output, $choice);

    $resp = $this->delete($env_id, $db, $list[$backup]);
    $output->writeln("<info>{$resp->message}</info>");
    $this->waitInteractive($input, $output, $resp->links->notification->href, $this->acquiaCloudClient);

    return 0;
  }

}
