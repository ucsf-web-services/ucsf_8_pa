<?php

namespace Acquia\Console\Cloud\Tests\Command\DatabaseBackup;

use Acquia\Console\Cloud\Command\DatabaseBackup\AcquiaCloudDatabaseBackupDelete;

/**
 * Class AcquiaCloudDatabaseBackupDeleteTest.
 *
 * @coversDefaultClass \Acquia\Console\Cloud\Command\DatabaseBackup\AcquiaCloudDatabaseBackupDelete
 *
 * @group acquia-console-cloud
 *
 * @package Acquia\Console\Cloud\Tests\Command\DatabaseBackup
 */
class AcquiaCloudDatabaseBackupDeleteTest extends AcquiaCloudDatabaseBackupTestBase {

  /**
   * @covers ::doRunCommand
   */
  public function testDatabaseBackupDelete() {
    $operation_response = (object) [
      'message' => 'Database backup deleted.',
      '_links' => (object) [
        'notification' => (object) [
          'href' => 'https://test2.devcloud.acquia-sites.com/api/v2/notifications/2343b683-b194-4217-982a-6a95c72ad9a8',
        ],
      ],
    ];
    $environment_response = $this->getFixture('ace_environment_response.php')['111111-11111111-c36a-401a-9724-fd8072a607d7'];
    $db_response = $this->getFixture('ace_database_response.php');
    $backups_response = $this->getFixture('ace_backups_resp.php');
    $notification = $this->getFixture('ace_notification.php')['backup_delete'];

    $arguments = [
      'create backup' => [
        'returns' => [
          // First case.
          $environment_response,
          $environment_response,
          $db_response,
          $backups_response,
          $operation_response,
          // Second case.
          $environment_response,
          $environment_response,
          $db_response,
          $backups_response,
          $operation_response,
          $notification
        ],
      ],
    ];

    $tester = $this->getCmdTesterInstanceOf(AcquiaCloudDatabaseBackupDelete::class, $arguments);
    $tester->setInputs([
      '111111-11111111-c36a-401a-9724-fd8072a607d7',
      0,
      '2017-01-08T05:00:02Z'
    ])->execute([]);
    $output = $tester->getDisplay();

    $this->assertStringContainsString('Database backup deleted.', $output);
    $this->assertStringContainsString('Process has been queued. Check the task logs for more information.', $output);

    $tester->setInputs([1, 0, '2017-01-08T05:00:02Z'])->execute(['--wait' => TRUE]);
    $output = $tester->getDisplay();

    $this->assertStringContainsString('Database backup deleted', $output, 'Wait function works.');
  }

}
