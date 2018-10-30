<?php

namespace Drupal\acquia_contenthub_subscriber\Tests;

use Drupal\acquia_contenthub_subscriber\ContentHubFilterInterface;

/**
 * Tests the functionality of Content Hub Filters.
 *
 * @group acquia_contenthub_subscriber
 */
class ContentHubFiltersTest extends WebTestBase {

  /**
   * Tests Operations on Filters as Admin User.
   */
  public function testFiltersAdminUser() {
    $this->drupalLogin($this->adminUser);

    // Create a filter.
    $filter = $this->entityCreate('contenthub_filter', $this->adminUser);
    $filter->save();

    // Test Multi GET request.
    $this->getHttpRequest($filter, TRUE);

    // Test a single GET request.
    $this->getHttpRequest($filter, FALSE);

    // Test POST request.
    $filter = $this->createContentHubFilter();
    $this->postHttpRequest($filter);

    // Test PATCH request.
    $this->patchHttpRequest($filter);

    // Test DELETE request.
    $this->deleteHttpRequest($filter);

    $this->drupalLogout();
  }

  /**
   * Tests Operations on Filters as Unauthorized User.
   */
  public function testFiltersUnAuthorizedUser() {
    $this->drupalLogin($this->unauthorizedUser);
    $this->allHttpRequestDenied();
    $this->drupalLogout();
  }

  /**
   * Tests Operations on Filters as Anonymous User.
   */
  public function testFiltersAnonymousUser() {
    $this->drupalLogin($this->anonymousUser);
    $this->allHttpRequestDenied();
    $this->drupalLogout();
  }

  /**
   * Obtains the Filters Endpoint for different requests.
   *
   * @param string $method
   *   The Http method.
   * @param string $id
   *   The filter ID, if any.
   *
   * @return string
   *   The Endpoint URL.
   */
  protected function getContentHubFilterResourceUrl($method, $id = 'all') {
    $url = '/acquia_contenthub/contenthub_filter';
    switch ($method) {
      case 'GET':
      case 'PATCH':
      case 'DELETE':
        $url .= "/{$id}";
        return $url;

      case 'POST':
        return $url;
    }
  }

  /**
   * Create some basic content hub filter array.
   */
  protected function createContentHubFilter() {
    // Data to be used for serialization.
    $data = $this->entityValues('contenthub_filter');
    return $data;
  }

  /**
   * Performs a GET request on filters endpoint.
   *
   * @param \Drupal\acquia_contenthub_subscriber\ContentHubFilterInterface $filter
   *   The filter to obtain through GET request.
   * @param bool|false $multi
   *   TRUE if querying 'all', FALSE otherwise.
   */
  protected function getHttpRequest(ContentHubFilterInterface $filter, $multi = FALSE) {
    // Create a JSON version of a simple content hub filter.
    $serialized = $this->container->get('serializer')->serialize([$filter], 'json');

    $method = 'GET';
    $id = $multi ? 'all' : $filter->id();
    $url = $this->getContentHubFilterResourceUrl($method, $id);
    $entities = $this->httpRequest($url, $method, NULL, 'application/json');
    $this->assertEqual($entities, $serialized);
  }

  /**
   * Performs a POST request on the filters endpoint.
   *
   * @param array $filter
   *   The filter values.
   */
  protected function postHttpRequest(array $filter) {
    // Create a JSON version of a simple content hub filter.
    $serialized = $this->container->get('serializer')->serialize($filter, 'json');

    // Post to the REST service to create the content hub filter.
    $method = 'POST';
    $url = $this->getContentHubFilterResourceUrl($method);
    $entity_json = $this->httpRequest($url, $method, $serialized, 'application/json');

    // Check that entity was saved in the database.
    $entity = $this->entityConfigStorage->load($filter['id']);
    $serialized = $this->container->get('serializer')->serialize($entity, 'json');
    $this->assertEqual($entity_json, $serialized);
  }

  /**
   * Performs a PATCH request on the filters endpoint.
   *
   * @param array $saved_filter
   *   The filter values.
   */
  protected function patchHttpRequest(array $saved_filter) {
    // Applying changes to the entity.
    $filter = $saved_filter;
    $filter['name'] = $saved_filter['name'] . ' - updated';

    // Create a JSON version of a simple content hub filter.
    $serialized = $this->container->get('serializer')->serialize($filter, 'json');

    // Patch to the REST service to modify the content hub filter.
    $method = 'PATCH';
    $url = $this->getContentHubFilterResourceUrl($method, $filter['id']);
    $entity_json = $this->httpRequest($url, $method, $serialized, 'application/json');

    // Check that changes have been saved.
    $entity = $this->entityConfigStorage->load($filter['id']);
    $serialized = $this->container->get('serializer')->serialize($entity, 'json');
    $this->assertFalse($entity->name === $saved_filter['name']);
    $this->assertEqual($entity_json, $serialized);
  }

  /**
   * Performs a DELETE request on the filters endpoint.
   *
   * @param array $filter
   *   The filter values.
   */
  protected function deleteHttpRequest(array $filter) {
    // Patch to the REST service to modify the content hub filter.
    $method = 'DELETE';
    $url = $this->getContentHubFilterResourceUrl($method, $filter['id']);
    $this->httpRequest($url, $method, NULL, 'application/json');
    $this->assertResponse(204);

    // Trying to load the same entity from the database.
    $entity = $this->entityConfigStorage->load($filter['id']);
    $this->assertNull($entity);

  }

  /**
   * Performs all requests to the filters endpoint.
   */
  protected function allHttpRequestDenied() {
    // Create a filter.
    $saved_filter = $this->entityCreate('contenthub_filter', $this->adminUser);
    $saved_filter->save();

    // Test Multi GET request.
    $method = 'GET';
    $url = $this->getContentHubFilterResourceUrl($method);
    $this->httpRequest($url, $method, NULL, 'application/json');
    $this->assertResponse(403);

    // Test a single GET request.
    $method = 'GET';
    $url = $this->getContentHubFilterResourceUrl($method, $saved_filter->id());
    $this->httpRequest($url, $method, NULL, 'application/json');
    $this->assertResponse(403);

    // Test POST request.
    $filter = $this->createContentHubFilter();
    $method = 'POST';
    $url = $this->getContentHubFilterResourceUrl($method);
    $serialized = $this->container->get('serializer')->serialize($filter, 'json');
    $this->httpRequest($url, $method, $serialized, 'application/json');
    $this->assertResponse(403);

    // Test PATCH request.
    $method = 'PATCH';
    $url = $this->getContentHubFilterResourceUrl($method, $saved_filter->id());
    $save_filter = $saved_filter;
    $save_filter->name .= '- Updated';
    $serialized = $this->container->get('serializer')->serialize($save_filter, 'json');
    $this->httpRequest($url, $method, $serialized, 'application/json');
    $this->assertResponse(403);

    // Test DELETE request.
    $method = 'DELETE';
    $url = $this->getContentHubFilterResourceUrl($method, $saved_filter->id());
    $this->httpRequest($url, $method, NULL, 'application/json');
    $this->assertResponse(403);
  }

}
