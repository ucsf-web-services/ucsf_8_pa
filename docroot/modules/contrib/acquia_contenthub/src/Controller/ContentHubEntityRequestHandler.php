<?php

namespace Drupal\acquia_contenthub\Controller;

use Drupal\acquia_contenthub\EntityManager;
use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\rest\RequestHandler;
use Drupal\rest\RestResourceConfigInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Drupal\Core\Render\RendererInterface;

/**
 * Decorates the REST module's RequestHandler.
 *
 * Because the Acquia Content Hub module needs to expose REST access:
 * - for certain entity types,
 * - but always only GET,
 * - but always only its own special format,
 * - but always only its own special authentication,
 * - but always only its own special authorization (access checking),
 * it doesn't make sense to rely on the REST module's default pattern of having
 * a RestResourceConfig configuration entity. That configuration entity is what
 * determines which methods/formats/authentication are enabled for a given REST
 * resource. But this module has the need to fully control that.
 *
 * So, we still rely on these to benefit from improvements/bugfixes:
 * - \Drupal\rest\RequestHandler
 * - \Drupal\rest\Plugin\rest\resource\EntityResource
 *
 * But we decorate RequestHandler to make it not rely on configuration entities,
 * and to make it instead fully depend on the information in the route. This
 * unfortunately requires some duplication.
 *
 * @todo Remove this when https://www.drupal.org/node/2822201 lands, and this module is able to require Drupal 8.3.x.
 */
class ContentHubEntityRequestHandler extends RequestHandler {

  /**
   * The resource plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $resourcePluginManager;

  /**
   * The Content Hub Entity Manager.
   *
   * @var \Drupal\acquia_contenthub\EntityManager
   */
  protected $entityManager;

  /**
   * The serializer.
   *
   * @var \Symfony\Component\Serializer\SerializerInterface|\Symfony\Component\Serializer\Encoder\DecoderInterface
   */
  protected $serializer;

  /**
   * The Renderer Service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Creates a new RequestHandler instance.
   *
   * @param \Drupal\Component\Plugin\PluginManagerInterface $resource_plugin_manager
   *   The resource plugin manager.
   * @param \Drupal\acquia_contenthub\EntityManager $entity_manager
   *   The Content Hub Entity Manager.
   * @param \Symfony\Component\Serializer\SerializerInterface|\Symfony\Component\Serializer\Encoder\DecoderInterface $serializer
   *   The serializer.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(PluginManagerInterface $resource_plugin_manager, EntityManager $entity_manager, SerializerInterface $serializer, RendererInterface $renderer) {
    $this->resourcePluginManager = $resource_plugin_manager;
    $this->entityManager = $entity_manager;
    $this->serializer = $serializer;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.rest'),
      $container->get('acquia_contenthub.entity_manager'),
      $container->get('serializer'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function handle(RouteMatchInterface $route_match, Request $request, RestResourceConfigInterface $_rest_resource_config = null) {
    // We only support one method, one format, and use one of the derivatives of
    // only one resource plugin. (We receive the exact plugin ID via the route
    // defaults).
    $method = 'GET';
    $format = 'acquia_contenthub_cdf';
    // Force the acquia_contenthub_cdf format.
    if ($request->getRequestFormat() !== $format) {
      $request->setRequestFormat($format);
    }
    $resource = $this->resourcePluginManager->createInstance($route_match->getRouteObject()->getDefault('_acquia_content_hub_rest_resource_plugin_id'));

    // EVERYTHING BELOW THIS IS MERE DUPLICATION OF THE DECORATED CLASS IN THE
    // MOST MINIMAL WAY POSSIBLE AND REMOVING THE DEPENDENCY ON CONFIG ENTITIES.
    // Determine the request parameters that should be passed to the resource
    // plugin.
    $route_parameters = $route_match->getParameters();
    $parameters = [];
    // Filter out all internal parameters starting with "_".
    foreach ($route_parameters as $key => $parameter) {
      if ($key{0} !== '_') {
        $parameters[] = $parameter;
      }
    }

    // Invoke the operation on the resource plugin.
    $unserialized = NULL;
    $response = call_user_func_array([$resource, $method], array_merge($parameters, [$unserialized, $request]));

    // Render response.
    $data = $response->getResponseData();
    $context = new RenderContext();

    if (!$context->isEmpty()) {
      $response->addCacheableDependency($context->pop());
    }

    // Adding cacheable dependency on the config entity for this particular
    // entity type.
    // All requests served by this route will have the cache tag of the config
    // entity that provided the response so if that config entity changes, then
    // this will automatically invalidate the caches of all responses
    // associated with it.
    $entity_type_id = $data->getEntityTypeId();
    if ($content_hub_config_entity = $this->entityManager->getContentHubEntityTypeConfigurationEntity($entity_type_id)) {
      $response->addCacheableDependency($content_hub_config_entity);
    }

    $response->headers->set('Content-Type', $request->getMimeType($format));

    return $response;
  }

}
