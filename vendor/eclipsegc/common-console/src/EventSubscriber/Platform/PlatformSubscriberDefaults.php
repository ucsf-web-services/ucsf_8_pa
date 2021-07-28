<?php

namespace EclipseGc\CommonConsole\EventSubscriber\Platform;

use EclipseGc\CommonConsole\CommonConsoleEvents;
use EclipseGc\CommonConsole\Event\GetPlatformTypeEvent;
use EclipseGc\CommonConsole\Event\GetPlatformTypesEvent;
use EclipseGc\CommonConsole\Platform\DdevPlatform;
use EclipseGc\CommonConsole\Platform\SshPlatform;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlatformSubscriberDefaults implements EventSubscriberInterface {

  public static function getSubscribedEvents() {
    $events[CommonConsoleEvents::GET_PLATFORM_TYPES] = 'onGetPlatformTypes';
    $events[CommonConsoleEvents::GET_PLATFORM_TYPE] = 'onGetPlatformType';
    return $events;
  }

  public function onGetPlatformTypes(GetPlatformTypesEvent $event) {
    $event->addPlatformType(SshPlatform::getPlatformId());
    $event->addPlatformType(DdevPlatform::getPlatformId());
  }

  public function onGetPlatformType(GetPlatformTypeEvent $event) {
    if ($event->getPlatformType() === SshPlatform::getPlatformId()) {
      $event->addClass(SshPlatform::class);
      $event->stopPropagation();
      return;
    }
    if ($event->getPlatformType() === DdevPlatform::getPlatformId()) {
      $event->addClass(DdevPlatform::class);
      $event->stopPropagation();
      return;
    }
  }

}
