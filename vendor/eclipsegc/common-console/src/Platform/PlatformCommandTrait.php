<?php

namespace EclipseGc\CommonConsole\Platform;

use EclipseGc\CommonConsole\CommonConsoleEvents;
use EclipseGc\CommonConsole\Event\AddPlatformToCommandEvent;
use EclipseGc\CommonConsole\Event\FilterPlatformSites;
use EclipseGc\CommonConsole\PlatformInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Trait PlatformCommandTrait.
 *
 * @package Eclipse\CommonConsole\Platform
 */
trait PlatformCommandTrait {

  /**
   * Available platforms.
   *
   * @var \EclipseGc\CommonConsole\PlatformInterface[]
   */
  protected $platforms;

  /**
   * Available platform aliases.
   *
   * @var string[]
   */
  protected $platformAliases;

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $dispatcher;

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
  public function addPlatform(string $alias, PlatformInterface $platform): void {
    $options = static::getExpectedPlatformOptions();
    foreach ($options as $name => $expectation) {
      if (empty($this->platforms[$name])) {
        $event = new AddPlatformToCommandEvent($expectation, $platform, $alias);
        $this->dispatcher->dispatch(CommonConsoleEvents::ADD_PLATFORM_TO_COMMAND, $event);
        if (!$event->platformMatchesExpectation()) {
          throw new \Exception(sprintf("Invalid Platform value. Expected a platform of type '%s'. Type of '%s' given.", $expectation, $platform::getPlatformId()));
        }
        $this->platforms[$name] = $platform;
        $this->platformAliases[$name] = $alias;
        return;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getPlatform(string $name): ?PlatformInterface {
    return $this->platforms[$name] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getPlatformSites(string $name): array {
    $platform = $this->getPlatform($name);
    // Not all platforms implement the platform sites interface. Set empty
    // array for them. Subscribers to the event can manually add "platform
    // sites" to their list via this mechanism if desireable.
    $sites = $platform instanceof PlatformSitesInterface ? $platform->getPlatformSites() : [];
    $event = new FilterPlatformSites($this, $platform, $sites);
    $this->dispatcher->dispatch(CommonConsoleEvents::FILTER_PLATFORM_SITES, $event);
    return $event->getPlatformSites();
  }

}
