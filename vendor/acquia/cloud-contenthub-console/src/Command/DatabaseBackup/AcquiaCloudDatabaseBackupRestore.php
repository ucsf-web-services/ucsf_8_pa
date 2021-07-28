<?php

namespace Acquia\Console\Cloud\Command\DatabaseBackup;

use Acquia\Console\Cloud\Command\AceNotificationHandlerTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * Class AcquiaCloudDatabaseBackupRestore.
 *
 * @package Acquia\Console\Cloud\Command\DatabaseBackup
 */
class AcquiaCloudDatabaseBackupRestore extends AcquiaCloudDatabaseBackupBase {

  use AceNotificationHandlerTrait;
  use AcquiaCloudDatabaseBackupHelperTrait;

  /**
   * {@inheritdoc}
   */
  protected static $defaultName = 'ace:database:backup:restore';

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setDescription('Restores database backups.');
    $this->addOption('wait', 'w', InputOption::VALUE_NONE, 'Wait for task until it is completed.');
    $this->setAliases(['ace-dbres']);
  }

  /**
   * {@inheritdoc}
   */
  protected function doRunCommand(string $env_id, string $db, InputInterface $input, OutputInterface $output): int {
    $list = $this->listBackups($env_id, $db, $this->acquiaCloudClient);
    if (!$list) {
      return 1;
    }

    $choice = new ChoiceQuestion('Choose a backup:', $list);
    $backup = $this->getHelper('question')->ask($input, $output, $choice);

    $resp = $this->restore($env_id, $db, $list[$backup]);
    $output->writeln("<info>{$resp->message}</info>");
    $this->waitInteractive($input, $output, $resp->links->notification->href, $this->acquiaCloudClient);

    return 0;
  }

}
