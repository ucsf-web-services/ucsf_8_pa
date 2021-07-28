<?php

namespace Acquia\Console\Cloud\Platform;

use Acquia\Console\Cloud\Client\AcquiaCloudClientFactory;
use Acquia\Console\Helpers\Command\PlatformCmdOutputFormatterTrait;
use AcquiaCloudApi\Endpoints\Environments;
use AcquiaCloudApi\Response\EnvironmentResponse;
use Consolidation\Config\Config;
use EclipseGc\CommonConsole\Event\Traits\PlatformArgumentInjectionTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Process\Process;

/**
 * Class AcquiaCloudMultiSitePlatform.
 *
 * @package Acquia\Console\Cloud\Platform
 */
class AcquiaCloudMultiSitePlatform extends AcquiaCloudPlatform {

  use PlatformArgumentInjectionTrait;
  use PlatformCmdOutputFormatterTrait;

  const PLATFORM_NAME = "Acquia Cloud Multi Site";

  /**
   * {@inheritdoc}
   */
  public static function getApplicationQuestion(Config $config, AcquiaCloudClientFactory $factory) {
    $question = parent::getApplicationQuestion($config, $factory);
    $question->setMultiselect(FALSE);

    return $question;
  }

  /**
   * {@inheritdoc}
   */
  public function execute(Command $command, InputInterface $input, OutputInterface $output): int {
    $exit_code = 0;

    $environments = new Environments($this->getAceClient());
    $env_id = current($this->get(self::ACE_ENVIRONMENT_DETAILS));
    $environment = $environments->get($env_id);
    $vendor_path = $this->get(self::ACE_VENDOR_PATHS);

    $sshUrl = $environment->sshUrl;
    [, $url] = explode('@', $sshUrl);
    [$application] = explode('.', $url);
    $sites = $this->getPlatformMultiSites($environment, $application, $output, $vendor_path[$env_id]);
    if (!$sites) {
      $output->writeln('<warning>No sites available. Exiting...</warning>');
      return 1;
    }

    $input_uri = $input->getOption('uri');
    if ($input_uri) {
      if (in_array($input_uri, $sites, TRUE)) {
        $sites = [$input_uri];
      }
      else {
        $output->writeln(sprintf("Given Url does not belong to the sites within the platform. %s", $input_uri));
        return 2;
      }
    }

    $args = $this->dispatchPlatformArgumentInjectionEvent($input, $sites, $command);

    foreach ($sites as $uri) {
      $output->writeln(sprintf("Attempting to execute requested command in environment: %s", $uri));
      $process = new Process("ssh $sshUrl 'cd /var/www/html/$application; cd $vendor_path[$env_id]; ./vendor/bin/commoncli {$args[$uri]->__toString()}'");
      $exit_code += $this->runner->run($process, $this, $output);
    }

    return $exit_code;
  }

  /**
   * Obtains the list of Sites in this Acquia Cloud Multi-site Platform.
   *
   * @return array
   *   An array of all sites urls in this Multi-site Platform keyed by directory.
   */
  public function getMultiSites(): array {
    $output = new StreamOutput(fopen('php://memory', 'r+', FALSE));
    $environments = new Environments($this->getAceClient());
    $env_id = current($this->get(self::ACE_ENVIRONMENT_DETAILS));
    $environment = $environments->get($env_id);
    $vendor_path = $this->get(self::ACE_VENDOR_PATHS);

    $sshUrl = $environment->sshUrl;
    [, $url] = explode('@', $sshUrl);
    [$application] = explode('.', $url);
    return $this->getPlatformMultiSites($environment, $application, $output, $vendor_path[$env_id]);
  }

  /**
   * Gets site URIs from sites.php on the given platform.
   *
   * @param \AcquiaCloudApi\Response\EnvironmentResponse $env_response
   *   Environment Response instance.
   * @param string $application
   *   Acquia Cloud Application name.
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *   The OutputInterface instance.
   * @param string $vendor_path
   *   Path to vendor dir.
   *
   * @return array
   *   Array containing site URIs.
   */
  public function getPlatformMultiSites(EnvironmentResponse $env_response, string $application, OutputInterface $output, string $vendor_path): array {
    $remote_output = new StreamOutput(fopen('php://memory', 'r+', FALSE));

    $process = new Process("ssh {$env_response->sshUrl} 'cd /var/www/html/$application; cd $vendor_path; ./vendor/bin/commoncli ace:multi:sites'");
    $this->runner->run($process, $this, $remote_output);
    rewind($remote_output->getStream());
    $content = stream_get_contents($remote_output->getStream());

    $lines = explode(PHP_EOL, trim($content));
    foreach ($lines as $line) {
      $data = $this->fromJson($line, $output);
      if (!$data) {
        continue;
      }

      if (isset($data->sites)) {
        $sites = array_intersect(array_flip(array_unique((array) $data->sites)), $env_response->domains);
      }
    }

    if (empty($sites)) {
      return [];
    }

    return $this->prefixDomains($sites);
  }

  /**
   * Prefix the domain with protocol.
   *
   * @param array $sites
   *   Array containing URI's where key is multi site directory name.
   *
   * @return array
   *   Array containing site URI's prefixed with HTTP protocol.
   */
  protected function prefixDomains(array $sites) {
    $prefix = $this->get(AcquiaCloudPlatform::ACE_SITE_HTTP_PROTOCOL);
    array_walk($sites, function (&$item, $key) use (&$prefix) {
      $item = $prefix[$key] . $item;
    });

    return $sites;
  }

}
