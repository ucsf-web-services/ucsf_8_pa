<?php

namespace EclipseGc\CommonConsole\EventSubscriber\InputDefinition;

use EclipseGc\CommonConsole\CommonConsoleEvents;
use EclipseGc\CommonConsole\Event\CreateApplicationEvent;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class AliasArgument
 *
 * @package EclipseGc\CommonConsole\EventSubscriber\InputDefinition
 */
class BareOutputOption implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[CommonConsoleEvents::CREATE_APPLICATION] = 'onCreateApplication';
    return $events;
  }

  /**
   * Add a bare output option.
   *
   * @param \EclipseGc\CommonConsole\Event\CreateApplicationEvent $event
   *   The create input event.
   */
  public function onCreateApplication(CreateApplicationEvent $event) {
    // Removes styling on the output. Useful especially for remote calls to
    // allow the local output formatter to apply to the returned output.
    $event->getApplication()->getDefinition()->addOption(new InputOption('bare', NULL, InputOption::VALUE_NONE, 'Prevents output styling.'));
  }

}
