<?php

namespace Acquia\Console\Cloud\Platform;

use Acquia\Console\Cloud\Client\AcquiaCloudClientFactory;
use AcquiaCloudApi\Connector\Client;
use AcquiaCloudApi\Endpoints\Applications;
use AcquiaCloudApi\Endpoints\Environments;
use Consolidation\Config\Config;
use Consolidation\Config\ConfigInterface;
use EclipseGc\CommonConsole\Event\Traits\PlatformArgumentInjectionTrait;
use EclipseGc\CommonConsole\Platform\PlatformBase;
use EclipseGc\CommonConsole\Platform\PlatformSitesInterface;
use EclipseGc\CommonConsole\Platform\PlatformStorage;
use EclipseGc\CommonConsole\PlatformDependencyInjectionInterface;
use EclipseGc\CommonConsole\ProcessRunner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Process\Process;

/**
 * Class AcquiaCloudPlatform.
 *
 * @package Acquia\Console\Cloud\Platform
 */
class AcquiaCloudPlatform extends PlatformBase implements PlatformSitesInterface, PlatformDependencyInjectionInterface {

  use PlatformArgumentInjectionTrait;

  const PLATFORM_NAME = "Acquia Cloud";

  public const ACE_API_KEY = 'acquia.cloud.api_key';

  public const ACE_API_SECRET = 'acquia.cloud.api_secret';

  public const ACE_APPLICATION_ID = 'acquia.cloud.application_ids';

  public const ACE_ENVIRONMENT_NAME = 'acquia.cloud.environment.name';

  public const ACE_ENVIRONMENT_DETAILS = 'acquia.cloud.environment.ids';

  public const ACE_VENDOR_PATHS = 'acquia.cloud.environment.vendor_paths';

  public const ACE_SITE_HTTP_PROTOCOL = 'acquia.cloud.environment.sites';

  /**
   * The Acquia Cloud Client Factory object.
   *
   * @var \Acquia\Console\Cloud\Client\AcquiaCloudClientFactory
   */
  protected $clientFactory;

  /**
   * AcquiaCloudPlatform constructor.
   *
   * @param \Consolidation\Config\ConfigInterface $config
   *   The configuration object.
   * @param \EclipseGc\CommonConsole\ProcessRunner $runner
   *   The process runner service.
   * @param \EclipseGc\CommonConsole\Platform\PlatformStorage $storage
   *   The platform storage service.
   * @param \Acquia\Console\Cloud\Client\AcquiaCloudClientFactory $clientFactory
   *   The Acquia Cloud client factory service.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
   *   The event dispatcher service.
   */
  public function __construct(
    ConfigInterface $config,
    ProcessRunner $runner,
    PlatformStorage $storage,
    AcquiaCloudClientFactory $clientFactory,
    EventDispatcherInterface $dispatcher
  ) {
    parent::__construct($config, $runner, $storage);
    $this->clientFactory = $clientFactory;
    $this->dispatcher = $dispatcher;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, ConfigInterface $config, ProcessRunner $runner, PlatformStorage $storage): PlatformDependencyInjectionInterface {
    return new static(
      $config,
      $runner,
      $storage,
      $container->get('http_client_factory.acquia_cloud'),
      $container->get('event_dispatcher')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function getPlatformId(): string {
    return static::PLATFORM_NAME;
  }

  /**
   * {@inheritdoc}
   */
  public static function getQuestions() {
    return [
      self::ACE_API_KEY => new Question("Acquia Cloud API Key? (Instructions: https://docs.acquia.com/acquia-cloud/develop/api/auth/) "),
      self::ACE_API_SECRET => new Question("Acquia Cloud Secret? "),
      self::ACE_APPLICATION_ID => [
        'question' => [AcquiaCloudPlatform::class, 'getApplicationQuestion'],
        'services' => ['http_client_factory.acquia_cloud']
      ],
      self::ACE_ENVIRONMENT_NAME => [
        'question' => [AcquiaCloudPlatform::class, 'getEnvironmentQuestion'],
        'services' => ['http_client_factory.acquia_cloud']
      ]
    ];
  }

  /**
   * Creates a question about available applications for the api key/secret.
   *
   * @param \Consolidation\Config\Config $config
   *   The values thus collected.
   * @param \Acquia\Console\Cloud\Client\AcquiaCloudClientFactory $factory
   *   The Acquia Cloud Client Factory.
   *
   * @return \Symfony\Component\Console\Question\ChoiceQuestion
   *   Choice question created.
   */
  public static function getApplicationQuestion(Config $config, AcquiaCloudClientFactory $factory) {
    $client = $factory->fromCredentials($config->get(self::ACE_API_KEY), $config->get(self::ACE_API_SECRET));
    $applications = new Applications($client);
    $options = [];
    /** @var \AcquiaCloudApi\Response\ApplicationResponse $item */
    foreach ($applications->getAll() as $item) {
      if ($item->hosting->type === 'acsf') {
        continue;
      }
      $options[$item->uuid] = $item->name;
    }
    $question = new ChoiceQuestion("Choose an Application: ", $options);
    $question->setMultiselect(TRUE);
    return $question;
  }

  /**
   * Creates question for available environments for the selected application.
   *
   * @param \Consolidation\Config\Config $config
   *   The values thus collected.
   * @param \Acquia\Console\Cloud\Client\AcquiaCloudClientFactory $factory
   *   The Acquia Cloud Client Factory.
   *
   * @return \Symfony\Component\Console\Question\ChoiceQuestion
   *   Choice question created.
   */
  public static function getEnvironmentQuestion(Config $config, AcquiaCloudClientFactory $factory) {
    $client = $factory->fromCredentials($config->get(self::ACE_API_KEY), $config->get(self::ACE_API_SECRET));
    $environment = new Environments($client);
    $options = [];
    foreach ($config->get(self::ACE_APPLICATION_ID) as $application_id) {
      /** @var \AcquiaCloudApi\Response\EnvironmentResponse $item */
      foreach ($environment->getAll($application_id) as $item) {
        $options[$item->name][] = $item->uuid;
      }
    }
    foreach ($options as $env => $envs) {
      if (count($envs) != count($config->get(self::ACE_APPLICATION_ID))) {
        unset($options[$env]);
      }
    }
    $options = array_keys($options);
    $options = array_combine($options, $options);
    /** @var \AcquiaCloudApi\Response\EnvironmentResponse $item */
    return new ChoiceQuestion("Choose an Environment: ", $options);
  }

  /**
   * {@inheritdoc}
   */
  public function execute(Command $command, InputInterface $input, OutputInterface $output) : int {
    $environments = new Environments($this->getAceClient());
    $sites = $this->getPlatformSites();
    if (!$sites) {
      $output->writeln('<warning>No sites available. Exiting...</warning>');
      return 1;
    }

    $input_uri = $input->getOption('uri');
    $sites = array_column($sites, 'uri');
    $args = $this->dispatchPlatformArgumentInjectionEvent($input, $sites, $command);
    $exit_code = 0;
    $vendor_paths = $this->get(self::ACE_VENDOR_PATHS);
    foreach ($this->get(self::ACE_ENVIRONMENT_DETAILS) as $application_id => $environment_id) {
      $uri = $this->getActivedomain($environment_id);
      if (isset($input_uri) && $input_uri !== $uri) {
        continue;
      }
      $environment = $environments->get($environment_id);
      $output->writeln(sprintf("Attempting to execute requested command in environment: %s", $uri));
      $sshUrl = $environment->sshUrl;
      [, $url] = explode('@', $sshUrl);
      [$application] = explode('.', $url);
      $process = new Process("ssh $sshUrl 'cd /var/www/html/$application; cd $vendor_paths[$environment_id]; ./vendor/bin/commoncli {$args[$uri]->__toString()}'");
      $exit_code += $this->runner->run($process, $this, $output);
    }

    return $exit_code;
  }

  /**
   * {@inheritdoc}
   */
  public function out(Process $process, OutputInterface $output, string $type, string $buffer) : void {
    if (Process::ERR === $type) {
      if (substr(trim($buffer), -32) === ": Permission denied (publickey).") {
        $output->writeln("<warning>Your SSH key is likely missing from Acquia Cloud. Follow this document to troubleshoot it: </warning><url>https://docs.acquia.com/acquia-cloud/manage/ssh/enable/add-key/</url>");
      }
    }
  }

  /**
   * Gets an Ace Client for this platform.
   *
   * @return \AcquiaCloudApi\Connector\Client
   *   ACE Client.
   */
  public function getAceClient() : Client {
    return $this->clientFactory->fromCredentials($this->get(self::ACE_API_KEY), $this->get(self::ACE_API_SECRET));
  }

  /**
   * {@inheritdoc}
   */
  public function getPlatformSites(): array {
    $client = $this->getAceClient();
    $environments = new Environments($client);
    $sites = [];
    $environment_details = $this->get(self::ACE_ENVIRONMENT_DETAILS);
    if (empty($environment_details)) {
      return $sites;
    }
    foreach ($environment_details as $application_id => $environment_id) {
      $environment = $environments->get($environment_id);
      $sites[$environment->uuid] = [
        'uri' => $this->getActiveDomain($environment_id),
        'platform_id' => static::getPlatformId()
      ];
    }
    return $sites;
  }

  /**
   * Returns the active domain of an environment.
   *
   * @param string $env_id
   *   The environment's unique identifier.
   *
   * @return string
   *   Return the domain or an empty string.
   */
  public function getActiveDomain(string $env_id): string {
    $response = $this
      ->getAceClient()
      ->request('get', "/environments/{$env_id}");

    return is_array($response) ? '' : $this->prefixDomain($response->active_domain, $env_id);
  }

  /**
   * Gets active domains for all site in the platform.
   *
   * @return array
   *   Array containing all active domains within the platform.
   */
  public function getActiveDomains(): array {
    $client = $this->getAceClient();

    $domains = [];
    foreach ($this->get(self::ACE_ENVIRONMENT_DETAILS) as $application_id => $environment_id) {
      $response = $client->request('get', "/environments/{$environment_id}");
      $domains[] = [
        'active_domain' => $this->prefixDomain($response->active_domain, $environment_id),
        'env_uuid' => $environment_id,
      ];
    }

    return $domains;
  }

  /**
   * Prefix uris with http protocol.
   *
   * @param string $domain
   *   Plain domain.
   * @param string $env_id
   *   Environment id.
   *
   * @return string
   *   Uri with http:// or https:// prefix.
   */
  public function prefixDomain(string $domain, string $env_id): string {
    $http_conf = $this->get(self::ACE_SITE_HTTP_PROTOCOL);
    $prefix = isset($http_conf[$env_id]) ? $http_conf[$env_id] : 'https://';
    return $prefix . $domain;
  }

}
