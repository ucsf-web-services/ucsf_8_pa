<?php

namespace EclipseGc\CommonConsole\EventSubscriber\AddPlatform;

use EclipseGc\CommonConsole\CommonConsoleEvents;
use EclipseGc\CommonConsole\Event\AddPlatformToCommandEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlatformIsInstanceOf implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[CommonConsoleEvents::ADD_PLATFORM_TO_COMMAND] = ['onAddPlatformToCommand', 100];
    return $events;
  }

  /**
   * The method called when a platform is added to a command.
   *
   * @param \EclipseGc\CommonConsole\Event\AddPlatformToCommandEvent $event
   *   The Add Platform to Command event.
   */
  public function onAddPlatformToCommand(AddPlatformToCommandEvent $event) {
    $expectation = $event->getExpectation();
    if ((class_exists($expectation) || interface_exists($expectation)) && $event->getPlatform() instanceof $expectation) {
      $event->setPlatformExpectationMatch(TRUE);
      $event->stopPropagation();
    }
  }

}
