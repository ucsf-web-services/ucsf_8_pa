<?php

namespace Acquia\Console\Cloud\EventSubscriber\Platform;

use Acquia\Console\Cloud\Platform\AcquiaCloudMultiSitePlatform;
use EclipseGc\CommonConsole\Event\GetPlatformTypeEvent;
use EclipseGc\CommonConsole\Event\GetPlatformTypesEvent;

/**
 * Class PlatformSubscriberAcquiaCloudMultiSite.
 *
 * @package Acquia\Console\Cloud\EventSubscriber\Platform
 */
class PlatformSubscriberAcquiaCloudMultiSite extends PlatformSubscriberAcquiaCloud {

  /**
   * Expose available platform(s) to CommonConsole.
   *
   * @param \EclipseGc\CommonConsole\Event\GetPlatformTypesEvent $event
   *   The get platform TYPES event.
   */
  public function onGetPlatformTypes(GetPlatformTypesEvent $event) {
    $event->addPlatformType(AcquiaCloudMultiSitePlatform::getPlatformId());
  }

  /**
   * Add class and/or factory data for an available platform.
   *
   * @param \EclipseGc\CommonConsole\Event\GetPlatformTypeEvent $event
   *   The get platform TYPE event.
   *
   * @throws \Exception
   */
  public function onGetPlatformType(GetPlatformTypeEvent $event) {
    if ($event->getPlatformType() === AcquiaCloudMultiSitePlatform::getPlatformId()) {
      $event->addClass(AcquiaCloudMultiSitePlatform::class);
      $event->stopPropagation();
      ;
    }
  }

}
