<?php

namespace Acquia\Console\Cloud\Tests\Command\DatabaseBackup;

use Acquia\Console\Cloud\Platform\AcquiaCloudPlatform;
use Acquia\Console\Cloud\Tests\Command\CommandTestHelperTrait;
use Acquia\Console\Cloud\Tests\Command\PlatformCommandTestHelperTrait;
use Acquia\Console\Cloud\Tests\TestFixtureHelperTrait;
use EclipseGc\CommonConsole\PlatformInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * Class AcquiaCloudDatabaseBackupTestBase.
 *
 * @package Acquia\Console\Cloud\Tests\Command\DatabaseBackup
 */
abstract class AcquiaCloudDatabaseBackupTestBase extends TestCase {

  use TestFixtureHelperTrait;
  use PlatformCommandTestHelperTrait;
  use CommandTestHelperTrait;

  /**
   * Returns a command tester instance.
   *
   * @param string $cmd
   *   The command to instantiate.
   * @param array $client_response
   *   The expected client response.
   * @param array $config_overwrite
   *   The platform configuration override.
   *
   * @return \Symfony\Component\Console\Tester\CommandTester
   *   The command tester.
   *
   * @throws \ReflectionException
   */
  protected function getCmdTesterInstanceOf(string $cmd, array $client_response = [], array $config_overwrite = []) {
    $reflection = new \ReflectionClass($cmd);
    /** @var \Acquia\Console\Cloud\Command\DatabaseBackup\AcquiaCloudDatabaseBackupBase $cmd */
    $cmd = $reflection->newInstanceArgs([$this->getDispatcher()]);
    $platform = $this->getPlatform($client_response);
    if ($config_overwrite) {
      foreach ($config_overwrite as $key => $value) {
        $platform->set($key, $value);
      }
    }
    $cmd->addPlatform('test', $platform);
    return $this->getCommandTester($cmd);
  }

  /**
   * {@inheritdoc}
   */
  public function getPlatform(array $args = []): PlatformInterface {
    $client_mock_callback = function (ObjectProphecy $client) use ($args) {
      foreach ($args as $arg) {
        if (empty($arg['returns'])) {
          continue;
        }
        if (!empty($arg['arguments'])) {
          $client->request(Argument::any(), Argument::any())
            ->shouldBeCalled()
            ->willReturn(...$arg['returns']);
        }
        else {
          $client->request(Argument::any(), Argument::any())
            ->shouldBeCalled()
            ->willReturn(...$arg['returns']);
        }
      }
    };

    return $this->getAcquiaCloudPlatform(
      [
        AcquiaCloudPlatform::ACE_API_KEY => 'test_key',
        AcquiaCloudPlatform::ACE_API_SECRET => 'test_secret',
        AcquiaCloudPlatform::ACE_APPLICATION_ID => ['test1'],
        AcquiaCloudPlatform::ACE_ENVIRONMENT_DETAILS => [
          '111111-11111111-c36a-401a-9724-fd8072a607d7' => '111111-11111111-c36a-401a-9724-fd8072a607d7'
        ]
      ],
      $client_mock_callback
    );
  }

}
