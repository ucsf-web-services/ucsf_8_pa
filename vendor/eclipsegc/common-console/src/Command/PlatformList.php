<?php

namespace EclipseGc\CommonConsole\Command;

use EclipseGc\CommonConsole\Platform\PlatformStorage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class PlatformList.
 *
 * @package EclipseGc\CommonConsole\Command
 */
class PlatformList extends Command {

  /**
   * {@inheritdoc}
   */
  protected static $defaultName = 'platform:list';

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
    $this->setDescription('List available platforms.')
      ->setAliases(['pl']);
  }

  /**
   * PlatformList constructor.
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
  protected function execute(InputInterface $input, OutputInterface $output) {
    $aliases = $this->platformStorage->loadAll();
    if (!$aliases) {
      $output->writeln('No platform available.');
      return 0;
    }
    
    $table = new Table($output);
    $table->setHeaders(['Alias', 'Type']);
    foreach ($aliases as $platform) {
      $table->addRow([$platform->getAlias(), $platform->get('platform.type')]);
    }
    $table->render();
    
    return 0;
  }

}
