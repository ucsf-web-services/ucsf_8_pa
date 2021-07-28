<?php

namespace Acquia\Console\Cloud\Tests\Command\DatabaseBackup;

use Acquia\Console\Cloud\Command\DatabaseBackup\AcquiaCloudDatabaseBackupCreate;
use Acquia\Console\Cloud\Platform\AcquiaCloudPlatform;

/**
 * Class AcquiaCloudDatabaseBackupCreateTest.
 *
 * @coversDefaultClass \Acquia\Console\Cloud\Command\DatabaseBackup\AcquiaCloudDatabaseBackupCreate
 *
 * @group acquia-console-cloud
 *
 * @package Acquia\Console\Cloud\Tests\Command\DatabaseBackup
 */
class AcquiaCloudDatabaseBackupCreateTest extends AcquiaCloudDatabaseBackupTestBase {

  /**
   * @covers ::doRunCommand
   */
  public function testNoSitesAvailable() {
    $tester = $this->getCmdTesterInstanceOf(AcquiaCloudDatabaseBackupCreate::class, [], [AcquiaCloudPlatform::ACE_ENVIRONMENT_DETAILS => []]);
    $tester->execute([]);
    $output = $tester->getDisplay();

    $this->assertStringContainsString('No sites have been registered in the current platform.', $output, 'Profile is empty, no sites available.');
  }

  /**
   * @covers ::doRunCommand
   */
  public function testDatabaseCreate() {
    $operation_response = (object) [
      'message' => 'Database backup created.',
      '_links' => (object) [
        'notification' => (object) [
          'href' => 'https://test2.devcloud.acquia-sites.com/api/v2/notifications/2343b683-b194-4217-982a-6a95c72ad9a8',
        ],
      ],
    ];
    $environment_response = $this->getFixture('ace_environment_response.php')['111111-11111111-c36a-401a-9724-fd8072a607d7'];
    $db_response = $this->getFixture('ace_database_response.php');

    $notification = $this->getFixture('ace_notification.php')['backup_create'];
    $arguments = [
      'create backup' => [
        'arguments' => [
          // Second case.
          ['get', '/environments/111111-11111111-c36a-401a-9724-fd8072a607d7'],
          ['get', '/environments/111111-11111111-c36a-401a-9724-fd8072a607d7'],
          [
            'get',
            '/environments/111111-11111111-c36a-401a-9724-fd8072a607d7/databases'
          ],
          ['get', '/environments/111111-11111111-c36a-401a-9724-fd8072a607d7'],
          ['get', '/environments/111111-11111111-c36a-401a-9724-fd8072a607d7'],
          [
            'get',
            '/environments/111111-11111111-c36a-401a-9724-fd8072a607d7/databases'
          ],
          [
            'post',
            '/environments/111111-11111111-c36a-401a-9724-fd8072a607d7/databases/example/backups'
          ],
          // Third case.
          ['get', '/environments/111111-11111111-c36a-401a-9724-fd8072a607d7'],
          ['get', '/environments/111111-11111111-c36a-401a-9724-fd8072a607d7'],
          [
            'get',
            '/environments/111111-11111111-c36a-401a-9724-fd8072a607d7/databases'
          ],
          [
            'post',
            '/environments/111111-11111111-c36a-401a-9724-fd8072a607d7/databases/example/backups'
          ],
          ['get', '/notifications/2343b683-b194-4217-982a-6a95c72ad9a8'],
        ],
        'returns' => [
          // Second case.
          $environment_response,
          $environment_response,
          $db_response,
          $environment_response,
          $environment_response,
          $db_response,
          $operation_response,
          // Third case.
          $environment_response,
          $environment_response,
          $db_response,
          $operation_response,
          $notification,
        ],
      ],
    ];

    // First case doesn't require input.
    $tester = $this->getCmdTesterInstanceOf(AcquiaCloudDatabaseBackupCreate::class, $arguments);
    $tester->setInputs([
      '111111-11111111-c36a-401a-9724-fd8072a607d7',
      'example',
      'no'
    ])->execute([]);
    $output = $tester->getDisplay();

    $this->assertStringContainsString('Backup process terminated by user', $output);
    // Second case.
    $tester->setInputs([
      '111111-11111111-c36a-401a-9724-fd8072a607d7',
      'example',
      'yes'
    ])->execute([]);
    $output = $tester->getDisplay();

    $this->assertStringContainsString('Process has been queued. Check the task logs for more information.', $output);

    // Third case.
    $tester->setInputs([
      '111111-11111111-c36a-401a-9724-fd8072a607d7',
      'example',
      'yes'
    ])->execute(['--wait' => TRUE]);
    $output = $tester->getDisplay();

    $this->assertStringContainsString('Database created', $output, 'Wait function works.');
  }

}
