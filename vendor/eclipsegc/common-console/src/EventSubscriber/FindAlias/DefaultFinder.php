<?php

namespace EclipseGc\CommonConsole\EventSubscriber\FindAlias;

use EclipseGc\CommonConsole\CommonConsoleEvents;
use EclipseGc\CommonConsole\Event\FindAliasEvent;
use EclipseGc\CommonConsole\Platform\PlatformStorage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class DefaultFinder
 *
 * @package EclipseGc\CommonConsole\EventSubscriber\FindAlias
 */
class DefaultFinder implements EventSubscriberInterface {

  /**
   * @var \EclipseGc\CommonConsole\Platform\PlatformStorage
   */
  protected $storage;

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[CommonConsoleEvents::ALIAS_FIND] = 'onFindAlias';
    return $events;
  }

  /**
   * DefaultFinder constructor.
   *
   * @param \EclipseGc\CommonConsole\Platform\PlatformStorage $storage
   *   The platform storage object.
   */
  public function __construct(PlatformStorage $storage) {
    $this->storage = $storage;
  }

  /**
   * The default platform finder for specific aliases.
   *
   * @param \EclipseGc\CommonConsole\Event\FindAliasEvent $event
   *   The find alias event.
   *
   * @throws \Exception
   */
  public function onFindAlias(FindAliasEvent $event) {
    $platform = $this->storage->load($event->getAlias());
    $event->setPlatform($platform);
    $event->stopPropagation();
  }

}
