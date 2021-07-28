<?php

namespace EclipseGc\CommonConsole\Command;

use EclipseGc\CommonConsole\Platform\PlatformCommandTrait;
use EclipseGc\CommonConsole\PlatformCommandInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Lists all the available sites of a platform.
 *
 * @package EclipseGc\CommonConsole\Command
 */
class PlatformSites extends Command implements PlatformCommandInterface {

  use PlatformCommandTrait;

  /**
   * {@inheritdoc}
   */
  protected static $defaultName = 'platform:sites';

  public function __construct(EventDispatcherInterface $dispatcher, string $name = NULL) {
    $this->dispatcher = $dispatcher;
    parent::__construct($name);
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setDescription('List available sites registered in the platform.');
  }

  public static function getExpectedPlatformOptions(): array {
    return [
      'source' => '\EclipseGc\CommonConsole\Platform\PlatformSitesInterface'
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    /** @var \EclipseGc\CommonConsole\Platform\PlatformSitesInterface $platform */
    $platform = $this->getPlatform('source');
    $sites = $platform->getPlatformSites();
    if ($sites) {
      $table = new Table($output);
      $table->setHeaders(['Domain', 'Type']);
      $table->setRows($sites);
      $table->render();
    }
    else {
      $output->writeln(sprintf('<error>No sites found for the specified platform %s</error>', $this->platformAliases['source']));
    }
  }

}
