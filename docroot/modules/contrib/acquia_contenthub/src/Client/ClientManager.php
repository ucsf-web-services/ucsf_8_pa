<?php

namespace Drupal\acquia_contenthub\Client;

use Exception;
use Acquia\ContentHubClient\ContentHub;
use Drupal\acquia_contenthub\Middleware\MiddlewareCollector;
use GuzzleHttp\Exception\ConnectException as ConnectException;
use GuzzleHttp\Exception\RequestException as RequestException;
use GuzzleHttp\Exception\ServerException as ServerException;
use GuzzleHttp\Exception\ClientException as ClientException;
use GuzzleHttp\Exception\BadResponseException as BadResponseException;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Component\Render\FormattableMarkup;
use Symfony\Component\HttpFoundation\Request as Request;
use Drupal\Component\Uuid\Uuid;

/**
 * Provides a service for managing pending server tasks.
 */
class ClientManager implements ClientManagerInterface {

  /**
   * Logger Factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The Acquia Content Hub Client.
   *
   * @var \Acquia\ContentHubClient\ContentHub
   */
  protected $client;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The Drupal Configuration.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * The Service collector for middlewares.
   *
   * @var \Drupal\acquia_contenthub\Middleware\MiddlewareCollector
   */
  protected $collector;

  /**
   * ClientManager constructor.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\acquia_contenthub\Middleware\MiddlewareCollector $collector
   *   The middleware.
   */
  public function __construct(LoggerChannelFactoryInterface $logger_factory, ConfigFactoryInterface $config_factory, LanguageManagerInterface $language_manager, MiddlewareCollector $collector) {
    $this->loggerFactory = $logger_factory;
    $this->configFactory = $config_factory;
    $this->languageManager = $language_manager;
    $this->collector = $collector;

    // Get the content hub config settings.
    $this->config = $this->configFactory->get('acquia_contenthub.admin_settings');

    // Initializing Client.
    $this->setConnection();
  }

  /**
   * Function returns content hub client.
   *
   * @param array $config
   *   Configuration array.
   *
   * @return \Acquia\ContentHubClient\ContentHub
   *   Returns the Content Hub Client
   *
   * @throws \Drupal\acquia_contenthub\ContentHubException
   *   Throws exception when cannot connect to Content Hub.
   */
  protected function setConnection(array $config = []) {
    $this->client = &drupal_static(__METHOD__);
    if (NULL === $this->client) {

      // Find out the module version in use.
      $module_info = system_get_info('module', 'acquia_contenthub');
      $module_version = (isset($module_info['version'])) ? $module_info['version'] : '0.0.0';
      $drupal_version = (isset($module_info['core'])) ? $module_info['core'] : '0.0.0';
      $client_user_agent = 'AcquiaContentHub/' . $drupal_version . '-' . $module_version;
      $hostname = $this->config->get('hostname');

      // Override configuration.
      $config = array_merge([
        'base_url' => $hostname,
        'client-user-agent' => $client_user_agent,
        'adapterConfig' => [
          'schemaId' => 'Drupal8',
          'defaultLanguageId' => $this->languageManager->getDefaultLanguage()->getId(),
        ],
      ], $config);

      // Get API information.
      $api = $this->config->get('api_key');
      $secret = $this->config->get('secret_key');
      $client_name = $this->config->get('client_name');
      $origin = $this->config->get('origin');

      // If any of these variables is empty, then we do NOT have a valid
      // connection.
      if (!Uuid::isValid($origin) || empty($client_name) || empty($hostname) || empty($api) || empty($secret)) {
        return FALSE;
      }

      $this->client = new ContentHub($origin, $this->collector->getMiddlewares(), $config);
    }
    return $this;
  }

  /**
   * Function returns the Acquia Content Hub client.
   */
  public function getConnection(array $config = []) {
    return $this->client;
  }

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
  public function resetConnection(array $variables, array $config = []) {
    // Manually pass in the api/secret keys to the middlewares.
    $api = isset($variables['api']) ? $variables['api'] : '';
    $secret = isset($variables['secret']) ? $variables['secret'] : '';;
    foreach ($this->collector->getMiddlewares() as $middleware) {
      $middleware->setApiKey($api);
      $middleware->setSecretKey($secret);
    }

    // If not overwritten, these should use the same configured variables.
    $hostname = isset($variables['hostname']) ? $variables['hostname'] : $this->config->get('hostname');
    $origin = isset($variables['origin']) ? $variables['origin'] : $this->config->get('origin');

    $module_info = system_get_info('module', 'acquia_contenthub');
    $module_version = (isset($module_info['version'])) ? $module_info['version'] : '0.0.0';
    $drupal_version = (isset($module_info['core'])) ? $module_info['core'] : '0.0.0';
    $client_user_agent = 'AcquiaContentHub/' . $drupal_version . '-' . $module_version;

    // Override configuration.
    $config = array_merge([
      'base_url' => $hostname,
      'client-user-agent' => $client_user_agent,
      'adapterConfig' => [
        'schemaId' => 'Drupal8',
        'defaultLanguageId' => $this->languageManager->getDefaultLanguage()->getId(),
      ],
    ], $config);

    $this->client = new ContentHub($origin, $this->collector->getMiddlewares(), $config);
  }

  /**
   * Checks whether the current client has a valid connection to Content Hub.
   *
   * @return bool
   *   TRUE if client is connected, FALSE otherwise.
   */
  public function isConnected() {
    // Always do a quick check.
    if (empty($this->getConnection())) {
      return FALSE;
    }

    // If we reached here then client has a valid connection.
    return TRUE;
  }

  /**
   * Checks whether the client name given is available in this Subscription.
   *
   * @param string $client_name
   *   The client name to check availability.
   *
   * @return bool
   *   TRUE if available, FALSE otherwise.
   */
  public function isClientNameAvailable($client_name) {
    if ($site = $this->createRequest('getClientByName', [$client_name])) {
      if (isset($site['uuid']) && Uuid::isValid($site['uuid'])) {
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * Extracts HMAC signature from the request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The Request to evaluate signature.
   * @param string $secret_key
   *   The Secret Key.
   *
   * @return string
   *   A base64 encoded string signature.
   */
  public function getRequestSignature(Request $request, $secret_key = '') {
    // Extract signature information from the request.
    $headers = array_map('current', $request->headers->all());
    $http_verb = $request->getMethod();

    // Adding the Request Query string.
    $path = $request->getRequestUri();
    $body = $request->getContent();

    // If the headers are not given, then the request is probably not coming
    // from the Content Hub. Replace them for empty string to fail validation.
    $content_type = isset($headers['content-type']) ? $headers['content-type'] : '';
    $date = isset($headers['date']) ? $headers['date'] : '';
    $message_array = [
      $http_verb,
      md5($body),
      $content_type,
      $date,
      '',
      $path,
    ];
    $message = implode("\n", $message_array);
    $s = hash_hmac('sha256', $message, $secret_key, TRUE);
    $signature = base64_encode($s);
    return $signature;
  }

  /**
   * Makes an API Call Request to Acquia Content Hub, with exception handling.
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
  public function createRequest($request, array $args = [], array $exception_messages = []) {
    try {
      // Check that we have a valid connection.
      if (empty($this->getConnection())) {
        $error = t('This client is NOT registered to Content Hub. Please register first');
        throw new Exception($error);
      }

      // Process each individual request.
      switch ($request) {
        // Case for all API calls with no arguments that do NOT require
        // authentication.
        case 'ping':
        case 'definition':
          return $this->getConnection()->$request();

        // Case for all API calls with no argument that require authentication.
        case 'getSettings':
        case 'purge':
        case 'restore':
        case 'reindex':
        case 'mapping':
        case 'regenerateSharedSecret':
          return $this->client->$request();

        // Case for all API calls with 1 argument.
        case 'register':
        case 'getClientByName':
        case 'createEntity':
        case 'createEntities':
        case 'putEntities':
        case 'readEntity':
        case 'readEntities':
        case 'updateEntities':
        case 'deleteEntity':
        case 'listEntities':
        case 'addWebhook':
        case 'deleteWebhook':
          // This request only requires one argument (webhook_uuid), but we
          // are using the second one to pass the webhook_url.
        case 'searchEntity':
          if (!isset($args[0])) {
            $error = t('Request %request requires %num argument.', [
              '%request' => $request,
              '%num' => 1,
            ]);
            throw new Exception($error);
          }
          return $this->client->$request($args[0]);

        // Case for all API calls with 2 arguments.
        case 'logs':
        case 'updateEntity':
          if (!isset($args[0]) || !isset($args[1])) {
            $error = t('Request %request requires %num arguments.', [
              '%request' => $request,
              '%num' => 2,
            ]);
            throw new Exception($error);
          }
          return $this->client->$request($args[0], $args[1]);
      }
    }
    // Catch Exceptions.
    catch (ServerException $ex) {
      $msg = $this->getExceptionMessage($request, $args, $ex, $exception_messages);
    }
    catch (ConnectException $ex) {
      $msg = $this->getExceptionMessage($request, $args, $ex, $exception_messages);
    }
    catch (ClientException $ex) {
      $response = json_decode($ex->getResponse()->getBody(), TRUE);
      $msg = $this->getExceptionMessage($request, $args, $ex, $exception_messages, $response);
    }
    catch (RequestException $ex) {
      $msg = $this->getExceptionMessage($request, $args, $ex, $exception_messages);
    }
    catch (BadResponseException $ex) {
      $response = json_decode($ex->getResponse()->getBody(), TRUE);
      $msg = $this->getExceptionMessage($request, $args, $ex, $exception_messages, $response);
    }
    catch (ServerErrorResponseException $ex) {
      $response = json_decode($ex->getResponse()->getBody(), TRUE);
      $msg = $this->getExceptionMessage($request, $args, $ex, $exception_messages, $response);
    }
    catch (Exception $ex) {
      $msg = $this->getExceptionMessage($request, $args, $ex, $exception_messages);
    }

    // Now show and log the error message.
    if (isset($msg)) {
      if ($msg !== FALSE) {
        $this->loggerFactory->get('acquia_contenthub')->error($msg);
        // Throw $ex;.
      }
      else {
        // If the message is FALSE, then there is no error message, which
        // means the request was expecting an exception to be successful.
        return TRUE;
      }
    }

    return FALSE;

  }

  /**
   * Obtains the appropriate exception message for the selected exception.
   *
   * This is the place to set up exception messages per request.
   *
   * @param string $request
   *   The Request to Plexus, as defined in the content-hub-php library.
   * @param array $args
   *   The Request arguments.
   * @param object $ex
   *   The Exception object.
   * @param array $exception_messages
   *   The array of messages to overwrite, keyed by Exception name.
   * @param object|void $response
   *   The response to the request.
   *
   * @return null|string
   *   The text to write in the messages.
   */
  protected function getExceptionMessage($request, array $args, $ex, array $exception_messages = [], $response = NULL) {
    // Obtain the class name.
    $exception = implode('', array_slice(explode('\\', get_class($ex)), -1));

    switch ($exception) {
      case 'ServerException':
        if (isset($exception_messages['ServerException'])) {
          $msg = $exception_messages['ServerException'];
        }
        else {
          $msg = new FormattableMarkup('Could not reach the Content Hub. Please verify your hostname and Credentials. [Error message: @msg]', ['@msg' => $ex->getMessage()]);
        }
        break;

      case 'ConnectException':
        if (isset($exception_messages['ConnectException'])) {
          $msg = $exception_messages['ConnectException'];
        }
        else {
          $msg = new FormattableMarkup('Could not reach the Content Hub. Please verify your hostname URL. [Error message: @msg]', ['@msg' => $ex->getMessage()]);
        }
        break;

      case 'ClientException':
      case 'BadResponseException':
      case 'ServerErrorResponseException':
        if (isset($exception_messages[$exception])) {
          $msg = $exception_messages[$exception];
        }
        else {
          if (isset($response) && isset($response['error'])) {
            // In the case of ClientException there are custom message that need
            // to be set depending on the request.
            $error = $response['error'];
            switch ($request) {
              // Customize the error message per request here.
              case 'register':
                $client_name = $args[0];
                $msg = new FormattableMarkup('Error registering client with name="@name" (Error Code = @error_code: @error_message)',
                  [
                    '@error_code' => $error['code'],
                    '@name' => $client_name,
                    '@error_message' => $error['message'],
                  ]);
                break;

              case 'getClientByName':
                // If status code = 404, then this site name is available.
                $code = $ex->getResponse()->getStatusCode();
                if ($code == 404) {
                  // All good! client name is available!
                  return FALSE;
                }
                else {
                  $msg = new FormattableMarkup('Error trying to connect to the Content Hub" (Error Code = @error_code: @error_message)', [
                    '@error_code' => $error['code'],
                    '@error_message' => $error['message'],
                  ]);
                }
                break;

              case 'addWebhook':
                $webhook_url = $args[0];
                $msg = new FormattableMarkup('There was a problem trying to register Webhook URL = %URL. Please try again. (Error Code = @error_code: @error_message)', [
                  '%URL' => $webhook_url,
                  '@error_code' => $error['code'],
                  '@error_message' => $error['message'],
                ]);
                break;

              case 'deleteWebhook':
                // This function only requires one argument (webhook_uuid), but
                // we are using the second one to pass the webhook_url.
                $webhook_url = isset($args[1]) ? $args[1] : $args[0];
                $msg = new FormattableMarkup('There was a problem trying to <b>unregister</b> Webhook URL = %URL. Please try again. (Error Code = @error_code: @error_message)', [
                  '%URL' => $webhook_url,
                  '@error_code' => $error['code'],
                  '@error_message' => $error['message'],
                ]);
                break;

              case 'purge':
                $msg = new FormattableMarkup('Error purging entities from the Content Hub [Error Code = @error_code: @error_message]', [
                  '@error_code' => $error['code'],
                  '@error_message' => $error['message'],
                ]);
                break;

              case 'readEntity':
                $uuid = $args[0];
                $msg = new FormattableMarkup('Entity with UUID="@uuid" was referenced by another entity, but could not be found in Content Hub. To resolve this warning, enable publishing of that entity type on the publishing site and re-export the entity. (Error Code = @error_code: @error_message)', [
                  '@error_code' => $error['code'],
                  '@uuid' => $uuid,
                  '@error_message' => $error['message'],
                ]);
                break;

              case 'createEntity':
                $msg = new FormattableMarkup('Error trying to create an entity in Content Hub (Error Code = @error_code: @error_message)', [
                  '@error_code' => $error['code'],
                  '@error_message' => $error['message'],
                ]);
                break;

              case 'createEntities':
                $msg = new FormattableMarkup('Error trying to create entities in Content Hub (Error Code = @error_code: @error_message)', [
                  '@error_code' => $error['code'],
                  '@error_message' => $error['message'],
                ]);
                break;

              case 'updateEntity':
                $uuid = $args[1];
                $msg = new FormattableMarkup('Error trying to update an entity with UUID="@uuid" in Content Hub (Error Code = @error_code: @error_message)', [
                  '@uuid' => $uuid,
                  '@error_code' => $error['code'],
                  '@error_message' => $error['message'],
                ]);
                break;

              case 'updateEntities':
                $msg = new FormattableMarkup('Error trying to update some entities in Content Hub (Error Code = @error_code: @error_message)', [
                  '@error_code' => $error['code'],
                  '@error_message' => $error['message'],
                ]);
                break;

              case 'deleteEntity':
                $uuid = $args[0];
                $msg = new FormattableMarkup('Error trying to delete entity with UUID="@uuid" in Content Hub (Error Code = @error_code: @error_message)', [
                  '@uuid' => $uuid,
                  '@error_code' => $error['code'],
                  '@error_message' => $error['message'],
                ]);
                break;

              case 'searchEntity':
                $msg = new FormattableMarkup('Error trying to make a search query to Content Hub. Are your credentials inserted correctly? (Error Code = @error_code: @error_message)', [
                  '@error_code' => $error['code'],
                  '@error_message' => $error['message'],
                ]);
                break;

              default:
                $msg = new FormattableMarkup('Error trying to connect to the Content Hub" (Error Code = @error_code: @error_message)', [
                  '@error_code' => $error['code'],
                  '@error_message' => $error['message'],
                ]);
            }

          }
          else {
            $msg = new FormattableMarkup('Error trying to connect to the Content Hub (Error Message = @error_message)', [
              '@error_message' => $ex->getMessage(),
            ]);
          }
        }
        break;

      case 'RequestException':
        if (isset($exception_messages['RequestException'])) {
          $msg = $exception_messages['RequestException'];
        }
        else {
          switch ($request) {
            // Customize the error message per request here.
            case 'register':
              $client_name = $args[0];
              $msg = new FormattableMarkup('Could not get authorization from Content Hub to register client @name. Are your credentials inserted correctly? (Error message = @error_message)', [
                '@name' => $client_name,
                '@error_message' => $ex->getMessage(),
              ]);
              break;

            case 'createEntity':
              $msg = new FormattableMarkup('Error trying to create an entity in Content Hub (Error Message: @error_message)', [
                '@error_message' => $ex->getMessage(),
              ]);
              break;

            case 'createEntities':
              $msg = new FormattableMarkup('Error trying to create entities in Content Hub (Error Message = @error_message)', [
                '@error_message' => $ex->getMessage(),
              ]);
              break;

            case 'updateEntity':
              $uuid = $args[1];
              $msg = new FormattableMarkup('Error trying to update entity with UUID="@uuid" in Content Hub (Error Message = @error_message)', [
                '@uuid' => $uuid,
                '@error_message' => $ex->getMessage(),
              ]);
              break;

            case 'updateEntities':
              $msg = new FormattableMarkup('Error trying to update some entities in Content Hub (Error Message = @error_message)', [
                '@error_message' => $ex->getMessage(),
              ]);
              break;

            case 'deleteEntity':
              $uuid = $args[0];
              $msg = new FormattableMarkup('Error trying to delete entity with UUID="@uuid" in Content Hub (Error Message = @error_message)', [
                '@uuid' => $uuid,
                '@error_message' => $ex->getMessage(),
              ]);
              break;

            case 'searchEntity':
              $msg = new FormattableMarkup('Error trying to make a search query to Content Hub. Are your credentials inserted correctly? (Error Message = @error_message)', [
                '@error_message' => $ex->getMessage(),
              ]);
              break;

            default:
              $msg = new FormattableMarkup('Error trying to connect to the Content Hub. Are your credentials inserted correctly? (Error Message = @error_message)', [
                '@error_message' => $ex->getMessage(),
              ]);
          }
        }
        break;

      case 'Exception':
      default:
        if (isset($exception_messages['Exception'])) {
          $msg = $exception_messages['Exception'];
        }
        else {
          $msg = new FormattableMarkup('Error trying to connect to the Content Hub (Error Message = @error_message)', [
            '@error_message' => $ex->getMessage(),
          ]);
        }
        break;

    }

    return $msg;
  }

}
