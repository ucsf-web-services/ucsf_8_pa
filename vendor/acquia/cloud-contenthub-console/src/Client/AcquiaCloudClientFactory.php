<?php

namespace Acquia\Console\Cloud\Client;

use AcquiaCloudApi\Connector\Client;
use AcquiaCloudApi\Connector\Connector;

/**
 * Wrapper for cloud api sdk client factory.
 *
 * @package Acquia\Console\Cloud\Client\AcquiaCloud
 */
class AcquiaCloudClientFactory {

  /**
   * Constructs an acquia cloud client.
   *
   * @param string $api_key
   *   The acquia cloud api key.
   * @param string $secret_key
   *   The acquia cloud secret key.
   *
   * @return \AcquiaCloudApi\Connector\Client
   *   The acquia cloud client.
   */
  public function fromCredentials(string $api_key, string $secret_key): Client {
    $config = [
      'key' => $api_key,
      'secret' => $secret_key,
    ];

    $connector = new Connector($config);
    return Client::factory($connector);
  }

}
