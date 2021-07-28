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
class AcquiaCloudMultisiteDatabaseBackupCreate extends AcquiaCloudMultisiteDatabaseBackupBase {

  use AceNotificationHandlerTrait;
  use AcquiaCloudDatabaseBackupHelperTrait;

  /**
   * {@inheritdoc}
   */
  protected static $defaultName = 'ace-multi:database:backup:create';

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setDescription('Creates database backups for ACE Multi-site environments.');
    $this->addOption('wait', 'w', InputOption::VALUE_NONE, 'Wait for task until it is completed.');
    $this->addOption('all', 'a', InputOption::VALUE_NONE, 'Perform backups for all sites in the multisite platform.');
    $this->setAliases(['ace-dbcrm']);
  }

  /**
   * {@inheritdoc}
   */
  protected function doRunCommand(string $env_id, array $databases, InputInterface $input, OutputInterface $output): int {
    if ($input->hasOption('all') && $input->getOption('all')) {
      $output->writeln(sprintf('Initiating backup for all the databases in this platform...'));
    }
    else {
      $helper = $this->getHelper('question');
      $confirm = new ConfirmationQuestion(sprintf('Database backup initiation for database = "%s". Would you like to proceed? (Y/N): ', current($databases)));
      $yes = $helper->ask($input, $output, $confirm);
      if (!$yes) {
        $output->writeln('Backup process terminated by user.');
        return 1;
      }
    }
    foreach ($databases as $db) {
      $resp = $this->initiateBackup($env_id, $db);
      $output->writeln("<info>{$resp->message}</info>");
      $this->waitInteractive($input, $output, $resp->links->notification->href, $this->acquiaCloudClient);
    }
    return 0;
  }

}
