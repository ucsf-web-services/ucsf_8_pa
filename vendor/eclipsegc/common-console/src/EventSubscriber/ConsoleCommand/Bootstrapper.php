<?php

namespace EclipseGc\CommonConsole\EventSubscriber\ConsoleCommand;

use EclipseGc\CommonConsole\Command\PlatformBootStrapCommandInterface;
use EclipseGc\CommonConsole\CommonConsoleEvents;
use EclipseGc\CommonConsole\Event\BootstrapEvent;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class Bootstrapper
 *
 * @package EclipseGc\CommonConsole\EventSubscriber\ConsoleCommand
 */
class Bootstrapper implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[ConsoleEvents::COMMAND] = ['onConsoleCommand', 50];
    return $events;
  }

  /**
   * Dispatches a new event for bootstrapping a application on a platform.
   *
   * @param \Symfony\Component\Console\Event\ConsoleCommandEvent $event
   *   The console command event.
   * @param string $event_name
   *   The event name.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
   *   The event dispatcher.
   */
  public function onConsoleCommand(ConsoleCommandEvent $event, string $event_name, EventDispatcherInterface $dispatcher) {
    $command = $event->getCommand();
    if ($command instanceof PlatformBootStrapCommandInterface) {
      $bootstrapEvent = new BootstrapEvent($command->getPlatformBootstrapType(), $event);
      $dispatcher->dispatch(CommonConsoleEvents::PLATFORM_BOOTSTRAP, $bootstrapEvent);
    }
  }

}
