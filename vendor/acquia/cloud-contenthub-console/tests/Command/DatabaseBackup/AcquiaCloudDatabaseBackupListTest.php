<?php

namespace Acquia\Console\Cloud\Tests\Command\DatabaseBackup;

use Acquia\Console\Cloud\Command\DatabaseBackup\AcquiaCloudDatabaseBackupList;

/**
 * Class AcquiaCloudDatabaseBackupListTest.
 *
 * @coversDefaultClass \Acquia\Console\Cloud\Command\DatabaseBackup\AcquiaCloudDatabaseBackupList
 *
 * @group acquia-console-cloud
 *
 * @package Acquia\Console\Cloud\Tests\Command\DatabaseBackup
 */
class AcquiaCloudDatabaseBackupListTest extends AcquiaCloudDatabaseBackupTestBase {

  /**
   * @covers ::doRunCommand
   */
  public function testDatabaseBackupList() {
    $backups_response = $this->getFixture('ace_backups_resp.php');
    $environment_response = $this->getFixture('ace_environment_response.php')['111111-11111111-c36a-401a-9724-fd8072a607d7'];
    $db_response = $this->getFixture('ace_database_response.php');
    $arguments = [
      'create backup' => [
        'returns' => [
          $environment_response,
          $environment_response,
          $db_response,
          $backups_response,
        ],
      ],
    ];
    $tester = $this->getCmdTesterInstanceOf(
      AcquiaCloudDatabaseBackupList::class,
      $arguments
    );
    $tester->setInputs([1, 0, '2017-01-08T05:00:02Z'])->execute([]);
    $output = $tester->getDisplay();

    $this->assertStringContainsString($this->getFixtureContents('ace_db_list_output.txt'), $output, 'Backups listed for the given database.');
  }

  /**
   * @covers ::doRunCommand
   */
  public function testWithNoBackupsAvailable() {
    $environment_response = $this->getFixture('ace_environment_response.php')['111111-11111111-c36a-401a-9724-fd8072a607d7'];
    $db_response = $this->getFixture('ace_database_response.php');
    $arguments = [
      'create backup' => [
        'returns' => [
          $environment_response,
          $environment_response,
          $db_response,
          []
        ],
      ],
    ];
    $tester = $this->getCmdTesterInstanceOf(
      AcquiaCloudDatabaseBackupList::class,
      $arguments
    );
    $tester->setInputs([1, 0])->execute([]);
    $output = $tester->getDisplay();

    $this->assertStringContainsString('No backups found', $output, 'Backups listed for the given database.');
  }

}
