<?php

namespace Acquia\Console\Cloud\Command;

use Acquia\Console\Cloud\Platform\AcquiaCloudPlatform;
use EclipseGc\CommonConsole\Platform\PlatformCommandTrait;
use EclipseGc\CommonConsole\PlatformCommandInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class AcquiaCloudCommandBase.
 *
 * @package Acquia\Console\Cloud\Command
 */
abstract class AcquiaCloudCommandBase extends Command implements PlatformCommandInterface {

  use PlatformCommandTrait;

  /**
   * ACE Client.
   *
   * @var \AcquiaCloudApi\Connector\ClientInterface
   */
  protected $acquiaCloudClient = NULL;

  /**
   * The current platform.
   *
   * @var \Acquia\Console\Cloud\Platform\AcquiaCloudPlatform
   */
  protected $platform;

  /**
   * AcquiaCloudCommandBase constructor.
   *
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher service.
   * @param string|null $name
   *   The command name.
   */
  public function __construct(EventDispatcherInterface $event_dispatcher, string $name = NULL) {
    parent::__construct($name);

    $this->dispatcher = $event_dispatcher;
  }

  /**
   * {@inheritdoc}
   */
  public static function getExpectedPlatformOptions(): array {
    return ['source' => AcquiaCloudPlatform::getPlatformId()];
  }

  /**
   * {@inheritdoc}
   */
  protected function initialize(InputInterface $input, OutputInterface $output) {
    $this->platform = $this->getPlatform('source');
    if (!$this->platform) {
      throw new \Exception('Platform is not available.');
    }

    $this->acquiaCloudClient = $this->platform->getAceClient();
    if (!$this->acquiaCloudClient) {
      throw new \Exception('Client is not available.');
    }
  }

}
