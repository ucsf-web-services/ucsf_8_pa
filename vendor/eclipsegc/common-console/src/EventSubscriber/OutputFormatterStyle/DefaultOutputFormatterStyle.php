<?php


namespace EclipseGc\CommonConsole\EventSubscriber\OutputFormatterStyle;


use EclipseGc\CommonConsole\CommonConsoleEvents;
use EclipseGc\CommonConsole\Event\OutputFormatterStyleEvent;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DefaultOutputFormatterStyle implements EventSubscriberInterface {

  public static function getSubscribedEvents() {
    $events[CommonConsoleEvents::OUTPUT_FORMATTER_STYLE] = 'onOutputFormatterStyle';
    return $events;
  }

  public function onOutputFormatterStyle(OutputFormatterStyleEvent $event) {
    $event->addOutputFormatterStyle('warning', new OutputFormatterStyle('black', 'yellow'));
    $event->addOutputFormatterStyle('url', new OutputFormatterStyle('white', 'blue', ['bold']));
  }

}