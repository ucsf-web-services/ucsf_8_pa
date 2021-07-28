<?php

namespace EclipseGc\CommonConsole\EventSubscriber\InputDefinition;

use EclipseGc\CommonConsole\CommonConsoleEvents;
use EclipseGc\CommonConsole\Event\CreateApplicationEvent;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class AliasArgument
 *
 * @package EclipseGc\CommonConsole\EventSubscriber\InputDefinition
 */
class AliasArgument implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[CommonConsoleEvents::CREATE_APPLICATION] = ['onCreateApplication', 1000];
    return $events;
  }

  /**
   * Add alias argument to the input definition.
   *
   * @param \EclipseGc\CommonConsole\Event\CreateApplicationEvent $event
   *   The create input event.
   */
  public function onCreateApplication(CreateApplicationEvent $event) {
    // Provide the option for specifying platform aliases.
    $event->getApplication()->getDefinition()->addArgument(new InputArgument('alias', InputArgument::OPTIONAL, 'Provide a platform alias for remote execution.'));
  }

}
