<?php

namespace Acquia\Console\Cloud\EventSubscriber\Platform;

use Acquia\Console\Cloud\Client\AcquiaCloudClientFactory;
use Acquia\Console\Cloud\Platform\AcquiaCloudPlatform;
use AcquiaCloudApi\Endpoints\Environments;
use Consolidation\Config\Config;
use EclipseGc\CommonConsole\CommonConsoleEvents;
use EclipseGc\CommonConsole\Event\PlatformConfigEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class PlatformConfigEnvironmentsDetails.
 *
 * @package Acquia\Console\Cloud\EventSubscriber\Platform
 */
class PlatformConfigEnvironmentsDetails implements EventSubscriberInterface {

  /**
   * AcquiaCloudClientFactory service instance.
   *
   * @var \Acquia\Console\Cloud\Client\AcquiaCloudClientFactory
   */
  protected $factory;

  /**
   * PlatformConfigEnvironmentsDetails constructor.
   *
   * @param \Acquia\Console\Cloud\Client\AcquiaCloudClientFactory $factory
   *   ACE Factory.
   */
  public function __construct(AcquiaCloudClientFactory $factory) {
    $this->factory = $factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events[CommonConsoleEvents::PLATFORM_CONFIG] = ['onPlatformConfig', 10000];
    return $events;
  }

  /**
   * Writes environment ids into platform configuration.
   *
   * @param \EclipseGc\CommonConsole\Event\PlatformConfigEvent $event
   *   PlatformConfigEvent instance.
   */
  public function onPlatformConfig(PlatformConfigEvent $event) {
    $config = $event->getConfig();
    if (!in_array($config->get('platform.type'), [
        'Acquia Cloud',
        'Acquia Cloud Multi Site'
    ], TRUE)) {
      return;
    }

    $details = $this->getEnvironmentDetails($config);
    if (!$details) {
      $event->addError('Cannot get Acquia Cloud environment details.');
      $event->stopPropagation();
      return;
    }

    $config->set(AcquiaCloudPlatform::ACE_ENVIRONMENT_DETAILS, $details);
  }

  /**
   * Extract environment ids before saving platform details to disk.
   *
   * Rather than having to make multiple calls for each command per each
   * application in the command run time, we spend a little extra time before
   * saving the values to ensure that we have all the relevant environment ids.
   * This will save us some cloud calls later and make the overall performance
   * faster when running commands.
   *
   * @param \Consolidation\Config\Config $config
   *   Config instance.
   *
   * @return array
   *   Environment info.
   */
  public function getEnvironmentDetails(Config $config): array {
    $client = $this->factory->fromCredentials($config->get(AcquiaCloudPlatform::ACE_API_KEY), $config->get(AcquiaCloudPlatform::ACE_API_SECRET));
    $environment = new Environments($client);
    $environment_details = [];
    foreach ($config->get(AcquiaCloudPlatform::ACE_APPLICATION_ID) as $application_id) {
      /** @var \AcquiaCloudApi\Response\EnvironmentResponse $item */
      foreach ($environment->getAll($application_id) as $item) {
        if ($item->name === $config->get(AcquiaCloudPlatform::ACE_ENVIRONMENT_NAME)) {
          $environment_details[$application_id] = $item->uuid;
          continue;
        }
      }
    }

    return $environment_details;
  }

}
