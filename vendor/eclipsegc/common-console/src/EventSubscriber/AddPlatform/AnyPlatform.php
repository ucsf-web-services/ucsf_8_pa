<?php

namespace EclipseGc\CommonConsole\EventSubscriber\AddPlatform;

use EclipseGc\CommonConsole\CommonConsoleEvents;
use EclipseGc\CommonConsole\Event\AddPlatformToCommandEvent;
use EclipseGc\CommonConsole\PlatformCommandInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AnyPlatform implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[CommonConsoleEvents::ADD_PLATFORM_TO_COMMAND] = ['onAddPlatformToCommand', 1000];
    return $events;
  }

  /**
   * The method called when a platform is added to a command.
   *
   * @param \EclipseGc\CommonConsole\Event\AddPlatformToCommandEvent $event
   *   The Add Platform to Command event.
   */
  public function onAddPlatformToCommand(AddPlatformToCommandEvent $event) {
    if ($event->getExpectation() === PlatformCommandInterface::ANY_PLATFORM) {
      $event->setPlatformExpectationMatch(TRUE);
      $event->stopPropagation();
    }
  }

}
