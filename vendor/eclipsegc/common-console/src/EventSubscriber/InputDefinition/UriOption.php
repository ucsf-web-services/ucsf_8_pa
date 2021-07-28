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
class UriOption implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[CommonConsoleEvents::CREATE_APPLICATION] = 'onCreateApplication';
    return $events;
  }

  /**
   * Add a uri option.
   *
   * @param \EclipseGc\CommonConsole\Event\CreateApplicationEvent $event
   *   The create input event.
   */
  public function onCreateApplication(CreateApplicationEvent $event) {
    // Provide the option for specifying request url.
    $event->getApplication()->getDefinition()->addOption(new InputOption('uri', NULL, InputOption::VALUE_OPTIONAL, 'The url from which to mock a request.'));
  }

}
