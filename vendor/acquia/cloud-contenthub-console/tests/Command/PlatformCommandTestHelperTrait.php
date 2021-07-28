<?php

namespace Acquia\Console\Cloud\Tests\Command;

use Acquia\Console\Acsf\Client\AcsfClient;
use Acquia\Console\Acsf\Client\AcsfClientFactory;
use Acquia\Console\Acsf\Platform\ACSFPlatform;
use Acquia\Console\Cloud\Client\AcquiaCloudClientFactory;
use Acquia\Console\Cloud\Platform\AcquiaCloudPlatform;
use AcquiaCloudApi\Connector\Client;
use Consolidation\Config\Config;
use EclipseGc\CommonConsole\CommonConsoleEvents;
use EclipseGc\CommonConsole\EventSubscriber\AddPlatform\AnyPlatform;
use EclipseGc\CommonConsole\EventSubscriber\AddPlatform\PlatformIdMatch;
use EclipseGc\CommonConsole\Platform\PlatformStorage;
use EclipseGc\CommonConsole\PlatformInterface;
use EclipseGc\CommonConsole\ProcessRunner;
use Prophecy\Argument;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Process\Process;

/**
 * Trait PlatformCommandTestHelperTrait.
 *
 * @package Acquia\Console\Cloud\Tests\Command
 */
trait PlatformCommandTestHelperTrait {

  /**
   * Returns a dispatcher bootstrapped for platform commands.
   *
   * @return \Symfony\Component\EventDispatcher\EventDispatcherInterface
   *   The event dispatcher service.
   */
  protected function getDispatcher(): EventDispatcherInterface {
    $subscriber = new PlatformIdMatch();
    $any_platform = new AnyPlatform();
    $dispatcher = new EventDispatcher();
    $dispatcher->addListener(CommonConsoleEvents::ADD_PLATFORM_TO_COMMAND, [
      $subscriber,
      'onAddPlatformToCommand'
    ]);
    $dispatcher->addListener(CommonConsoleEvents::ADD_PLATFORM_TO_COMMAND, [
      $any_platform,
      'onAddPlatformToCommand'
    ]);
    return $dispatcher;
  }

  /**
   * Returns a platform of type Acquia Cloud.
   *
   * @param array $platform_config
   *   The platform configuration array.
   * @param callable|null $client_mock_modifier
   *   [Optional] The callable function to alter client. It accepts a
   *   MockObject.
   *
   * @return \Acquia\Console\Cloud\Platform\AcquiaCloudPlatform
   *   Return Acquia Cloud Platform mock instance.
   */
  protected function getAcquiaCloudPlatform(array $platform_config, callable $client_mock_modifier = NULL): AcquiaCloudPlatform {
    $client_factory = $this->prophesize(AcquiaCloudClientFactory::class);
    $platform_storage = $this->prophesize(PlatformStorage::class);

    if ($client_mock_modifier) {
      $client = $this->prophesize(Client::class);
      $client_mock_modifier($client);
      $client_factory->fromCredentials(Argument::any(), Argument::any())
        ->shouldBeCalled()
        ->willReturn($client->reveal());
    }
    else {
      $platform_storage->save(Argument::any())
        ->shouldBeCalled()
        ->willReturn($this->saveMocks());
    }

    $process_runner = $this->prophesize(ProcessRunner::class);
    $dispatcher = $this->prophesize(EventDispatcher::class);

    return new AcquiaCloudPlatform(
      $this->parseConfigArray($platform_config),
      $process_runner->reveal(),
      $platform_storage->reveal(),
      $client_factory->reveal(),
      $dispatcher->reveal()
    );
  }

  /**
   * Returns a platform of type ACSF.
   *
   * @param array $platform_config
   *   Platform Config array.
   * @param callable|null $client_mock_modifier
   *   [Optional] The callable function to alter client. It accepts a
   *   MockObject.
   *
   * @return \Acquia\Console\Acsf\Platform\ACSFPlatform
   *   ACSF Platform Mock instance.
   */
  protected function getAcsfPlatform(array $platform_config, callable $client_mock_modifier = NULL): ACSFPlatform {
    $acsf_factory = $this->prophesize(AcsfClientFactory::class);
    $platform_storage = $this->prophesize(PlatformStorage::class);

    if ($client_mock_modifier) {
      $client = $this->prophesize(AcsfClient::class);
      $client_mock_modifier($client);
      $acsf_factory->fromCredentials(Argument::any(), Argument::any(), Argument::any())
        ->willReturn($client->reveal());
    }
    else {
      $platform_storage->save(Argument::any())
        ->shouldBeCalled()
        ->willReturn($this->saveMocks());
    }

    $ace_factory = $this->prophesize(AcquiaCloudClientFactory::class);
    $process_runner = $this->prophesize(ProcessRunner::class);
    $dispatcher = $this->prophesize(EventDispatcher::class);

    return new ACSFPlatform(
      $this->parseConfigArray($platform_config),
      $process_runner->reveal(),
      $platform_storage->reveal(),
      $ace_factory->reveal(),
      $acsf_factory->reveal(),
      $dispatcher->reveal()
    );
  }

  /**
   * Converts configuration array to Consolidation/Config object.
   *
   * @param array $platform_config
   *   The configuration array.
   *
   * @return \Consolidation\Config\Config
   *   The platform's config object.
   */
  private function parseConfigArray(array $platform_config) {
    $config = new Config();
    foreach ($platform_config as $key => $value) {
      $config->set($key, $value);
    }
    return $config;
  }

  /**
   * Returns mock instance for save().
   *
   * @return \EclipseGc\CommonConsole\PlatformInterface
   *   Platform Mock Object.
   */
  private function saveMocks(): PlatformInterface {
    return new Class implements PlatformInterface {

      /**
       *  {@inheritDoc}
       */
      public static function getQuestions() {
      }

      /**
       * {@inheritDoc}
       */
      public static function getPlatformId(): string {
      }

      /**
       * {@inheritDoc}
       */
      public function getAlias(): string {
      }

      /**
       * {@inheritDoc}
       */
      public function execute(Command $command, InputInterface $input, OutputInterface $output): int {
      }

      /**
       * {@inheritDoc}
       */
      public function out(Process $process, OutputInterface $output, string $type, string $buffer): void {
      }

      /**
       * {@inheritDoc}
       */
      public function get(string $key) {
      }

      /**
       * {@inheritDoc}
       */
      public function set(string $key, $value) {
      }

      /**
       * {@inheritDoc}
       */
      public function export(): array {
      }

      /**
       * {@inheritDoc}
       */
      public function save(): PlatformInterface {
      }

    };
  }

}
