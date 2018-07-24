<?php

namespace Drupal\acquia_contenthub\Client;

use Symfony\Component\HttpFoundation\Request;

/**
 * Interface for ClientManager.
 */
interface ClientManagerInterface {

  /**
   * Gets a Content Hub Client Object.
   *
   * @param array $config
   *   Configuration array.
   *
   * @return \Acquia\ContentHubClient\ContentHub
   *   Returns the Content Hub Client
   */
  public function getConnection(array $config);

  /**
   * Resets the connection to allow to pass connection variables.
   *
   * This should be used when we need to pass connection variables instead
   * of using the ones stored in Drupal variables.
   *
   * @param array $variables
   *   The array of variables to pass through.
   * @param array $config
   *   The Configuration options.
   */
  public function resetConnection(array $variables, array $config = []);

  /**
   * Checks whether the current client has a valid connection to Content Hub.
   *
   * @return bool
   *   TRUE if client is connected, FALSE otherwise.
   */
  public function isConnected();

  /**
   * Makes an API Call Request to Content Hub, with exception handling.
   *
   * It handles generic exceptions and allows for text overrides.
   *
   * @param string $request
   *   The name of the request.
   * @param array $args
   *   The arguments to pass to the request.
   * @param array $exception_messages
   *   The exception messages to overwrite.
   *
   * @return bool|mixed
   *   The return value of the request if succeeds, FALSE otherwise.
   */
  public function createRequest($request, array $args = [], array $exception_messages = []);

  /**
   * Extracts HMAC signature from the request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The Request to evaluate signature.
   * @param string $secret_key
   *   The secret key used to generate the signature.
   *
   * @return string
   *   The HMAC signature for this request.
   */
  public function getRequestSignature(Request $request, $secret_key = '');

}
