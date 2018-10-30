<?php

namespace Drupal\acquia_contenthub_subscriber\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Psr\Log\LoggerInterface;
use Drupal\Component\Uuid\Uuid;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\acquia_contenthub_subscriber\ContentHubFilterInterface;
use DateTime;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Component\Render\FormattableMarkup;
use Symfony\Component\HttpFoundation\Request;
use Drupal\rest\ModifiedResourceResponse;

/**
 * Provides a resource to perform CRUD operations on Content Hub Filters.
 *
 * @RestResource(
 *   id = "contenthub_filter",
 *   label = @Translation("Content Hub Filter"),
 *   serialization_class = "Drupal\acquia_contenthub_subscriber\Entity\ContentHubFilter",
 *   uri_paths = {
 *     "canonical" = "/acquia_contenthub/contenthub_filter/{contenthub_filter}",
 *     "https://www.drupal.org/link-relations/create" = "/acquia_contenthub/contenthub_filter"
 *   }
 * )
 */
class ContentHubFilterResource extends ResourceBase {

  /**
   * Permission to allow access to this resource.
   *
   * @var string
   */
  protected $permission = 'administer acquia content hub';

  /**
   * A curent user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * A instance of entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Constructs a Drupal\rest\Plugin\ResourceBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user account.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger, EntityManagerInterface $entity_manager, AccountProxyInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->entityManager = $entity_manager;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('rest'),
      $container->get('entity.manager'),
      $container->get('current_user')
    );
  }

  /**
   * Validates input from user.
   *
   * @param \Drupal\acquia_contenthub_subscriber\ContentHubFilterInterface|null $contenthub_filter
   *   The Content Hub Filter entity.
   * @param bool $is_new
   *   Validate taken into consideration it is a new entity or an existent one.
   */
  public function validate(ContentHubFilterInterface $contenthub_filter, $is_new = TRUE) {
    $messages = [];
    if (!empty($contenthub_filter->uuid())) {
      if (!Uuid::isValid($contenthub_filter->uuid())) {
        $messages[] = t('The filter has an invalid "uuid" field.');
      }
    }

    if (empty($contenthub_filter->id())) {
      $messages[] = t('The filter has an invalid "id" field.');
    }
    else {
      if (preg_match("/^[a-zA-Z0-9_]*$/", $contenthub_filter->id(), $matches) !== 1) {
        $messages[] = t('The "id" field has to be a "machine_name" (Only small letters, numbers and underscore allowed).');
      }
      // @TODO: Check that the ID is unique making a query to the database.
    }
    if (!isset($contenthub_filter->name)) {
      $messages[] = t('The filter has to have a "name" field.');
    }

    if (!isset($contenthub_filter->author) || $contenthub_filter->author == 0) {
      $messages[] = t('You are trying to create a new filter without a valid session.');
    }

    // Validating Date fields.
    if (!empty($contenthub_filter->from_date)) {
      if (DateTime::createFromFormat('m-d-Y', $contenthub_filter->from_date) === FALSE) {
        $messages[] = t('Invalid "from_date" field. Valid format is "m-d-Y".');
      }
    }
    if (!empty($contenthub_filter->to_date)) {
      if (DateTime::createFromFormat('m-d-Y', $contenthub_filter->to_date) === FALSE) {
        $messages[] = t('Invalid "to_date" field. Valid format is "m-d-Y".');
      }
    }

    // @TODO: Validate other fields.
    if (count($messages) > 0) {
      $message = implode("\n", $messages);
      throw new HttpException(422, $message);
    }
  }

  /**
   * Responds to GET requests.
   *
   * Returns a list of filters.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing a list of filters.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   */
  public function get($contenthub_filter = NULL) {
    if (!$this->currentUser->hasPermission($this->permission)) {
      throw new AccessDeniedHttpException();
    }

    $entities = NULL;
    if (!empty($contenthub_filter) && $contenthub_filter !== 'all') {
      $entities = [];
      $entities[] = $contenthub_filter;
    }
    $filters = $this->entityManager->getStorage('contenthub_filter')->loadMultiple($entities);

    if (!empty($filters)) {
      foreach ($filters as $key => $filter) {
        // Present the date fields in format "m-d-Y".
        $filters[$key]->changeDateFormatYearMonthDay2MonthDayYear();
      }

      $response = new ResourceResponse(array_values($filters));
      $response->addCacheableDependency($filters);
      return $response;
    }
    elseif ($contenthub_filter == 'all') {
      $response = new ResourceResponse([]);
      $response->addCacheableDependency($filters);
      return $response;
    }

    throw new NotFoundHttpException(t('No Content Hub Filters were found'));

  }

  /**
   * Responds to POST requests.
   *
   * @param \Drupal\acquia_contenthub_subscriber\ContentHubFilterInterface|null $contenthub_filter
   *   The Content Hub Filter.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The Content Hub Filter after it has been saved.
   */
  public function post(ContentHubFilterInterface $contenthub_filter = NULL) {
    if (!$this->currentUser->hasPermission($this->permission)) {
      throw new AccessDeniedHttpException();
    }

    if (empty($contenthub_filter)) {
      throw new BadRequestHttpException('No Content Hub Filter content received.');
    }

    // This Filter is owned by the user who created it.
    if (empty($contenthub_filter->author)) {
      $uid = $this->currentUser->id();

      // Anonymous should not be able to have 'Administer Acquia Content Hub'
      // permission but if it ever does, this filter will be owned by admin.
      $contenthub_filter->author = $uid ?: 1;
    }

    // Verify that we have valid Content Hub Filter Entity.
    $this->validate($contenthub_filter, TRUE);

    // Now that it has been validated, we need to convert the Date to the
    // appropriate storage format in "Y-m-d".
    $contenthub_filter->changeDateFormatMonthDayYear2YearMonthDay();

    // We are ONLY creating new entities through POST requests.
    if (!$contenthub_filter->isNew()) {
      $message = t('Only new entities can be created. Filter "!name" already exists (id = "!id", uuid = "!uuid").', [
        '!id' => $contenthub_filter->id(),
        '!name' => $contenthub_filter->name,
        '!uuid' => $contenthub_filter->uuid(),
      ]);
      throw new BadRequestHttpException($message);
    }

    // Validation has passed, now try to save the entity.
    try {
      $contenthub_filter->save();
      $this->logger->notice('Created entity %type with ID %id.', ['%type' => $contenthub_filter->getEntityTypeId(), '%id' => $contenthub_filter->id()]);

      // Convert back the Dates to format "m-d-Y".
      $contenthub_filter->changeDateFormatYearMonthDay2MonthDayYear();
      return new ResourceResponse($contenthub_filter);
    }
    catch (EntityStorageException $e) {
      $message = new FormattableMarkup('Internal Server Error [!message].', [
        '!message' => $e->getMessage(),
      ]);
      throw new HttpException(500, $message, $e);
    }
  }

  /**
   * Responds to PATCH requests.
   *
   * @param string $parameter
   *   The URL parameter.
   * @param \Drupal\acquia_contenthub_subscriber\ContentHubFilterInterface $contenthub_filter
   *   The Content Hub Filter entity submitted by REST.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The HTTP request object.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The Content Hub Filter after it has been saved.
   */
  public function patch($parameter, ContentHubFilterInterface $contenthub_filter, Request $request) {
    if (!$this->currentUser->hasPermission($this->permission)) {
      throw new AccessDeniedHttpException();
    }

    if (empty($contenthub_filter)) {
      throw new BadRequestHttpException('No Content Hub Filter content received.');
    }

    // Obtain the original Content Hub Filter.
    $contenthub_filter_original = $this->entityManager->getStorage('contenthub_filter')->load($contenthub_filter->getOriginalId());
    if (empty($contenthub_filter_original)) {
      throw new BadRequestHttpException('No Content Hub Filter "id" received. You have to provide an existing entity "id" in the body message.');
    }

    // This Filter is owned by the user who initially created it.
    if (empty($contenthub_filter->author)) {
      // Anonymous should not be able to have 'Administer Acquia Content Hub'
      // permission but if it ever does, this filter will be owned by admin.
      $contenthub_filter->author = $contenthub_filter_original->author ?: 1;
    }

    // Verify that we have valid values submitted in Content Hub Filter.
    $this->validate($contenthub_filter, FALSE);

    // Now that it has been validated, we need to convert the Date to the
    // appropriate storage format in "Y-m-d".
    $contenthub_filter->changeDateFormatMonthDayYear2YearMonthDay();

    // Update original entity with submitted values.
    /** @var \Drupal\acquia_contenthub_subscriber\ContentHubFilterInterface $contenthub_filter_updated */
    $contenthub_filter_updated = $contenthub_filter->updateValues($contenthub_filter_original);

    // Validation has passed, now try to save the original updated entity.
    try {
      $contenthub_filter_updated->save();
      $this->logger->notice('Updated entity %type with ID %id.', ['%type' => $contenthub_filter_updated->getEntityTypeId(), '%id' => $contenthub_filter_updated->id()]);

      // Convert back the Dates to format "m-d-Y".
      $contenthub_filter_updated->changeDateFormatYearMonthDay2MonthDayYear();
      return new ResourceResponse($contenthub_filter_updated);
    }
    catch (EntityStorageException $e) {
      $message = new FormattableMarkup('Internal Server Error [!message].', [
        '!message' => $e->getMessage(),
      ]);
      throw new HttpException(500, $message, $e);
    }
  }

  /**
   * Responds to DELETE requests.
   *
   * @param string $contenthub_filter_id
   *   The Content Hub Filter ID.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The success status of the request.
   */
  public function delete($contenthub_filter_id) {
    if (!$this->currentUser->hasPermission($this->permission)) {
      throw new AccessDeniedHttpException();
    }

    if (empty($contenthub_filter_id)) {
      throw new BadRequestHttpException('No Content Hub Filter information was sent to be deleted.');
    }
    // Obtain the original Content Hub Filter.
    $contenthub_filter = $this->entityManager->getStorage('contenthub_filter')->load($contenthub_filter_id);
    if (empty($contenthub_filter)) {
      throw new BadRequestHttpException("No Content Hub Filter exists for id = \"{$contenthub_filter_id}\".");
    }

    try {
      $contenthub_filter->delete();
      // DELETE responses have an empty body.
      return new ModifiedResourceResponse(NULL, 204);
    }
    catch (EntityStorageException $e) {
      $message = new FormattableMarkup('Internal Server Error [!message].', [
        '!message' => $e->getMessage(),
      ]);
      throw new HttpException(500, $message, $e);

    }
  }

}
