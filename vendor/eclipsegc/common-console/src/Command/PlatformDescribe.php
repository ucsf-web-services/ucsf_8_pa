<?php

namespace EclipseGc\CommonConsole\Command;

use EclipseGc\CommonConsole\Platform\PlatformStorage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class PlatformDescribe.
 *
 * @package EclipseGc\CommonConsole\Command
 */
class PlatformDescribe extends Command {

  /**
   * {@inheritdoc}
   */
  protected static $defaultName = 'platform:describe';

  /**
   * The platform storage.
   *
   * @var \EclipseGc\CommonConsole\Platform\PlatformStorage
   */
  protected $platformStorage;

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setDescription('Obtain more details about a platform.')
      ->setHelp('Get detailed information about a specific platform. The argument should be an existing platform, omit the "@" sign.')
      ->setAliases(['pd']);
  }

  /**
   * PlatformDescribe constructor.
   *
   * @param \EclipseGc\CommonConsole\Platform\PlatformStorage $storage
   *   The platform storage service.
   * @param string|null $name
   *   The name of the command.
   */
  public function __construct(PlatformStorage $storage, string $name = NULL) {
    parent::__construct($name);

    $this->platformStorage = $storage;
  }

  /**
   * {@inheritdoc}
   */
  protected function initialize(InputInterface $input, OutputInterface $output) {
    $alias = $input->getArgument('alias');
    if (!$alias) {
      throw new \Exception('Command requires the "alias" argument!');
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $alias = $input->getArgument('alias');
    $platform = $this->platformStorage->load($alias);

    $output->writeln(Yaml::dump($platform->export(), 7));
    return 0;
  }

}
