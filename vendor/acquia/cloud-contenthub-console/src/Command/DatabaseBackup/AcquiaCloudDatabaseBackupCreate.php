<?php

namespace Acquia\Console\Cloud\Command\DatabaseBackup;

use Acquia\Console\Cloud\Command\AceNotificationHandlerTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Class AcquiaCloudDatabaseBackupCreate.
 *
 * @package Acquia\Console\Cloud\Command\DatabaseBackup
 */
class AcquiaCloudDatabaseBackupCreate extends AcquiaCloudDatabaseBackupBase {

  use AceNotificationHandlerTrait;
  use AcquiaCloudDatabaseBackupHelperTrait;

  /**
   * {@inheritdoc}
   */
  protected static $defaultName = 'ace:database:backup:create';

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setDescription('Creates database backups.');
    $this->addOption('wait', 'w', InputOption::VALUE_NONE, 'Wait for task until it is completed.');
    $this->addOption('all', 'a', InputOption::VALUE_NONE, 'Perform backups for all sites in the platform.');
    $this->setAliases(['ace-dbcr']);
  }

  /**
   * {@inheritdoc}
   */
  protected function doRunCommand(string $env_id, string $db, InputInterface $input, OutputInterface $output): int {
    if ($input->hasOption('all') && $input->getOption('all')) {
      $output->writeln(sprintf('Initiating backup for database = "%s"...', $db));
    }
    else {
      $helper = $this->getHelper('question');
      $confirm = new ConfirmationQuestion(sprintf('Database backup initiation for db = "%s". Would you like to proceed? (Y/N): ', $db));
      $yes = $helper->ask($input, $output, $confirm);
      if (!$yes) {
        $output->writeln('<warning>Backup process terminated by user.</warning>');
        return 1;
      }
    }

    $resp = $this->initiateBackup($env_id, $db);
    $output->writeln("<info>{$resp->message}</info>");
    $this->waitInteractive($input, $output, $resp->links->notification->href, $this->acquiaCloudClient);

    return 0;
  }

}
