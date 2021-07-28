<?php

namespace Acquia\Console\Cloud\Command;

use AcquiaCloudApi\Connector\ClientInterface;
use AcquiaCloudApi\Endpoints\Notifications;
use Symfony\Component\Console\Exception\MissingInputException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Trait AceNotificationHandlerTrait.
 *
 * @package Acquia\Console\Cloud\Command
 */
trait AceNotificationHandlerTrait {

  /**
   * Prints out the results of waiting.
   *
   * @param \Symfony\Component\Console\Input\InputInterface $input
   *   The current input. Checks for the wait option.
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *   The output to write to.
   * @param string $notification_url
   *   The notification url.
   * @param \AcquiaCloudApi\Connector\ClientInterface $client
   *   The ace client object.
   */
  public function waitInteractive(InputInterface $input, OutputInterface $output, string $notification_url, ClientInterface $client): void {
    if (!$input->hasOption('wait')) {
      throw new MissingInputException('"wait" input option is missing.');
    }

    if ($input->getOption('wait')) {
      $label = $this->wait($notification_url, $client);
      $output->writeln("<info>$label</info>");
    }
    else {
      $output->writeln('<info>Process has been queued. Check the task logs for more information.</info>');
    }
  }

  /**
   * Wait until task is completed.
   *
   * @param string $notification_url
   *   The notification url.
   * @param \AcquiaCloudApi\Connector\ClientInterface $client
   *   Client object.
   *
   * @return string
   *   The label of the operation.
   */
  public function wait(string $notification_url, ClientInterface $client): string {
    $notifications = new Notifications($client);
    do {
      $resp = $notifications->get($this->getNotificationUuid($notification_url));
      sleep(3);
    } while ($resp->status !== 'completed');

    return $resp->label;
  }

  /**
   * Parses uuid from the notification url.
   *
   * @param string $url
   *   The provided url to parse.
   *
   * @return string
   *   The notification uuid.
   */
  protected function getNotificationUuid(string $url): string {
    $path = parse_url($url, PHP_URL_PATH);
    $parts = explode('/', $path);
    return end($parts);
  }

}
