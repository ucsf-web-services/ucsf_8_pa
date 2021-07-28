<?php

namespace Acquia\Console\Cloud\EventSubscriber\Platform;

use Acquia\Console\Cloud\Client\AcquiaCloudClientFactory;
use Acquia\Console\Cloud\Platform\AcquiaCloudPlatform;
use Acquia\Console\Helpers\Command\PlatformCmdOutputFormatterTrait;
use AcquiaCloudApi\Connector\Client;
use AcquiaCloudApi\Endpoints\Environments;
use Consolidation\Config\Config;
use EclipseGc\CommonConsole\CommonConsoleEvents;
use EclipseGc\CommonConsole\Event\PlatformConfigEvent;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Process\Process;

/**
 * Class PlatformConfigHttpProtocol.
 *
 * @package Acquia\Console\Cloud\EventSubscriber\Platform
 */
class PlatformConfigHttpProtocol implements EventSubscriberInterface {

  use PlatformCmdOutputFormatterTrait;

  /**
   * Platform types to handle within this subscriber.
   *
   * @var array
   *   Platform types.
   */
  protected $platformTypes = [
    'Acquia Cloud',
    'Acquia Cloud Multi Site'
  ];

  /**
   * AcquiaCloudClientFactory service instance.
   *
   * @var \Acquia\Console\Cloud\Client\AcquiaCloudClientFactory
   */
  protected $factory;

  /**
   * PlatformConfigAppFinderCloud constructor.
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
    $events[CommonConsoleEvents::PLATFORM_CONFIG] = ['onPlatformConfig', 98];
    return $events;
  }

  /**
   * Writes info about Http protocol into the platform config.
   *
   * @param \EclipseGc\CommonConsole\Event\PlatformConfigEvent $event
   *   PlatformConfigEvent instance.
   *
   * @throws \Exception
   */
  public function onPlatformConfig(PlatformConfigEvent $event) {
    $input = $event->getInput();
    $output = $event->getOutput();
    $config = $event->getConfig();
    $platform_type = $config->get('platform.type');

    if (!in_array($platform_type, $this->platformTypes, TRUE)) {
      return;
    }

    $cloud_client = $this->factory->fromCredentials($config->get('acquia.cloud.api_key'), $config->get('acquia.cloud.api_secret'));

    $uris = [];
    switch ($platform_type) {
      case 'Acquia Cloud':
        $uris = $this->getPlatformSites($config, $cloud_client);
        break;

      case 'Acquia Cloud Multi Site':
        $uris = $this->getPlatformMultiSites($config, $output, $cloud_client);
        break;
    }

    if (!$uris) {
      throw new \Exception('Cannot find platform sites.');
    }

    $output->writeln('<info>"We assume that all your sites are using HTTPS."</info>');
    $helper = new QuestionHelper();
    $confirm = new ConfirmationQuestion('<warning>Is this assumption correct?</warning>');
    $answer = $helper->ask($input, $output, $confirm);

    if ($answer) {
      array_walk($uris, function (&$uri) {
        $uri = 'https://';
      });

      $config->set(AcquiaCloudPlatform::ACE_SITE_HTTP_PROTOCOL, $uris);
      return;
    }

    $choice = new ChoiceQuestion('Please pick which sites are running on HTTP:', $uris);
    $choice->setMultiselect(TRUE);
    $answer = $helper->ask($input, $output, $choice);

    foreach ($uris as $env_id => $uri) {
      if (in_array($env_id, $answer, TRUE)) {
        $uris[$env_id] = 'http://';
        continue;
      }
      $uris[$env_id] = 'https://';
    }

    $config->set(AcquiaCloudPlatform::ACE_SITE_HTTP_PROTOCOL, $uris);
  }

  /**
   * Get ACE platform sites.
   *
   * @param \Consolidation\Config\Config $config
   *   Config instance.
   * @param \AcquiaCloudApi\Connector\Client $client
   *   Cloud client.
   *
   * @return array
   *   Array containing site uri info.
   */
  public function getPlatformSites(Config $config, Client $client): array {
    $uris = [];
    $environment_details = $config->get(AcquiaCloudPlatform::ACE_ENVIRONMENT_DETAILS);
    if (empty($environment_details)) {
      return $uris;
    }
    foreach ($environment_details as $environment_id) {
      $environment = $client->request('get', "/environments/{$environment_id}");
      $uris[$environment_id] = is_array($environment) ? '' : $environment->active_domain;
    }

    return $uris;
  }

  /**
   * Get ACE multi site platform sites.
   *
   * @param \Consolidation\Config\Config $config
   *   Config instance.
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *   The output.
   * @param \AcquiaCloudApi\Connector\Client $client
   *   Cloud client.
   *
   * @return array
   *   Array containing site uri info.
   */
  public function getPlatformMultiSites(Config $config, OutputInterface $output, Client $client) {
    $environments = new Environments($client);
    $env_id = current($config->get(AcquiaCloudPlatform::ACE_ENVIRONMENT_DETAILS));
    $vendor_path = $config->get(AcquiaCloudPlatform::ACE_VENDOR_PATHS)[$env_id];
    $environment = $environments->get($env_id);

    $sshUrl = $environment->sshUrl;
    [, $url] = explode('@', $sshUrl);
    [$application] = explode('.', $url);

    $process = new Process("ssh {$sshUrl} 'cd /var/www/html/$application; cd $vendor_path; ./vendor/bin/commoncli ace:multi:sites'");
    $process->run();
    $raw = trim($process->getOutput());

    $lines = explode(PHP_EOL, trim($raw));
    foreach ($lines as $line) {
      $data = $this->fromJson($line, $output);
      if (!$data) {
        continue;
      }

      if (isset($data->sites)) {
        return array_intersect(array_flip(array_unique((array) $data->sites)), $environment->domains);
      }
    }

    return [];
  }

}
