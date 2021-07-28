<?php

namespace Acquia\Console\Cloud\Command;

use Acquia\Console\Cloud\Platform\AcquiaCloudPlatform;
use AcquiaCloudApi\Endpoints\Crons;
use AcquiaCloudApi\Response\CronsResponse;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AcquiaCloudCronList.
 *
 * @package Acquia\Console\Cloud\Command
 */
class AcquiaCloudCronList extends AcquiaCloudCommandBase {

  /**
   * {@inheritdoc}
   */
  protected static $defaultName = 'ace:cron:list';

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setDescription('Lists Scheduled Jobs.');
    $this->setAliases(['ace-cl']);
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $rows = [];

    $cron = new Crons($this->acquiaCloudClient);
    foreach ($this->getEnvironmentInfo() as $env_id) {
      $jobs = $cron->getAll($env_id);
      $rows = array_merge($rows, $this->getCronInfo($jobs));
    }

    $table = new Table($output);
    $table->setHeaders([
      'Environment id',
      'Environment name',
      'Label',
      'Cron ID'
    ]);
    $table->addRows($rows);
    $table->render();

    return 0;
  }

  /**
   * Get environment info from platform config.
   *
   * @return array
   *   Environment config.
   */
  protected function getEnvironmentInfo(): array {
    return $this->platform->get(AcquiaCloudPlatform::ACE_ENVIRONMENT_DETAILS);
  }

  /**
   * Gets necessary info about crons from CronResponse.
   *
   * @param \AcquiaCloudApi\Response\CronsResponse $jobs
   *   Cron response.
   *
   * @return array
   *   Array containing information about cron jobs.
   */
  protected function getCronInfo(CronsResponse $jobs): array {
    $info = [];
    foreach ($jobs as $job) {
      $info[] = [
        'env_id' => $job->environment->id,
        'name' => $job->environment->name,
        'label' => $job->label,
        'id' => $job->id,
      ];
    }

    return $info;
  }

}
