<?php

namespace Drupal\Tests\acquia_contenthub\Unit\Controller;

use Drupal\acquia_contenthub\Controller\ContentHubEntityExportController;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\node\Entity\Node;
use Drupal\Tests\UnitTestCase;
use Acquia\ContentHubClient\Entity;
use Acquia\ContentHubClient\Attribute;
use Drupal\acquia_contenthub\ContentHubEntityDependency;
use Drupal\acquia_contenthub\ContentHubEntitiesTracking;

/**
 * Unit test for ContentHubEntityExportController class.
 *
 * @coversDefaultClass \Drupal\acquia_contenthub\Controller\ContentHubEntityExportController
 *
 * @group acquia_contenthub
 */
class ContentHubEntityExportControllerTest extends UnitTestCase {

  /**
   * ContentHub Client Manager.
   *
   * @var \Drupal\acquia_contenthub\Client\ClientManagerInterface
   */
  private $clientManager;

  /**
   * ContentHub Entity Manager.
   *
   * @var \Drupal\acquia_contenthub\EntityManager
   */
  private $entityManager;

  /**
   * ContentHub Entity CDF Normalizer.
   *
   * @var \Drupal\acquia_contenthub\Normalizer\ContentEntityCdfNormalizer|\PHPUnit\Framework\MockObject\MockObject
   */
  private $contentEntityCdfNormalizer;

  /**
   * ContentHub Entity Queue Controller.
   *
   * @var \Drupal\acquia_contenthub\Controller\ContentHubExportQueueController
   */
  private $contentHubExportQueueController;

  /**
   * ContentHub Internal Request.
   *
   * @var \Drupal\acquia_contenthub\ContentHubInternalRequest|\PHPUnit\Framework\MockObject\MockObject
   */
  private $contentHubInternalRequest;

  /**
   * ContentHub Entities Tracking.
   *
   * @var \Drupal\acquia_contenthub\ContentHubEntitiesTracking|\PHPUnit\Framework\MockObject\MockObject
   */
  private $contentHubEntitiesTracking;

  /**
   * Entity Repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  private $entityRepository;

  /**
   * The Drupal Configuration.
   *
   * @var \Drupal\Core\Config\Config|\PHPUnit\Framework\MockObject\MockObject
   */
  private $config;

  /**
   * Configuration Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * Logger Service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  private $loggerFactory;

  /**
   * The Controller for Content Hub Export Entities using bulk upload.
   *
   * @var \Drupal\acquia_contenthub\Controller\ContentHubEntityExportController
   */
  private $contentHubEntityExportController;

  /**
   * The Site Origin.
   *
   * @var string
   */
  private $siteOrigin = '22222222-2222-2222-2222-222222222222';

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->clientManager = $this->getMockBuilder('\Drupal\acquia_contenthub\Client\ClientManagerInterface')
      ->disableOriginalConstructor()
      ->getMock();

    $this->entityManager = $this->getMockBuilder('\Drupal\acquia_contenthub\EntityManager')
      ->disableOriginalConstructor()
      ->getMock();

    $this->contentHubEntitiesTracking = $this->getMockBuilder('\Drupal\acquia_contenthub\ContentHubEntitiesTracking')
      ->disableOriginalConstructor()
      ->getMock();

    $this->contentEntityCdfNormalizer = $this->getMockBuilder('\Drupal\acquia_contenthub\Normalizer\ContentEntityCdfNormalizer')
      ->disableOriginalConstructor()
      ->getMock();

    $this->contentHubExportQueueController = $this->getMockBuilder('\Drupal\acquia_contenthub\Controller\ContentHubExportQueueController')
      ->disableOriginalConstructor()
      ->getMock();

    $this->entityRepository = $this->getMockBuilder('\Drupal\Core\Entity\EntityRepositoryInterface')
      ->disableOriginalConstructor()
      ->getMock();

    $this->contentHubInternalRequest = $this->getMockBuilder('\Drupal\acquia_contenthub\ContentHubInternalRequest')
      ->disableOriginalConstructor()
      ->getMock();

    $this->config = $this->getMockBuilder('\Drupal\Core\Config\Config')
      ->disableOriginalConstructor()
      ->getMock();

    $this->configFactory = $this->getMockBuilder('\Drupal\Core\Config\ConfigFactoryInterface')
      ->disableOriginalConstructor()
      ->getMock();
    $this->configFactory->method('get')
      ->with('acquia_contenthub.entity_config')
      ->willReturn($this->config);

    $logger_channel = $this->getMockBuilder(LoggerChannelInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $this->loggerFactory = $this->getMockBuilder(LoggerChannelFactoryInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $this->loggerFactory
      ->method('get')
      ->with('acquia_contenthub')
      ->willReturn($logger_channel);

    $this->instantiateContentHubEntityController();
  }

  /**
   * Instantiates ContentHubEntityExportController object.
   */
  private function instantiateContentHubEntityController() {
    $this->contentHubEntityExportController = new ContentHubEntityExportController(
      $this->clientManager,
      $this->entityManager,
      $this->contentHubEntitiesTracking,
      $this->contentEntityCdfNormalizer,
      $this->contentHubExportQueueController,
      $this->entityRepository,
      $this->contentHubInternalRequest,
      $this->configFactory,
      $this->loggerFactory
    );
  }

  /**
   * Tests getDrupalEntities method.
   *
   * @covers ::getDrupalEntities
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testGetDrupalEntities() {
    /** @var \Symfony\Component\HttpFoundation\JsonResponse $response */
    $response = $this->contentHubEntityExportController->getDrupalEntities();
    $this->assertEquals(
      '{"entities":[]}',
      $response->getContent()
    );
  }

  /**
   * Tests trackExportedEntity method.
   *
   * @covers ::trackExportedEntity
   *
   * @throws \Exception
   */
  public function testTrackExportedEntity() {
    $entity = $this->createContentHubEntity();

    $entity_dependency = new ContentHubEntityDependency($entity);
    $entity_dependency->setStatus(Node::PUBLISHED);
    $cdf = (array) $entity_dependency->getRawEntity();

    $entity = (object) [
      'entity_type' => 'node',
      'entity_id' => 1,
      'entity_uuid' => '00000000-0000-0000-0000-000000000000',
      'modified' => '2016-12-09T20:51:45+00:00',
      'origin' => '11111111-1111-1111-1111-111111111111',
    ];

    $contentHubEntitiesTracking = $this->getContentHubEntitiesTrackingService();
    $contentHubEntitiesTracking->setExportedEntity($entity->entity_type,
      $entity->entity_id,
      $entity->entity_uuid,
      $entity->modified,
      $entity->origin
    );

    $node = $this->getMockBuilder('\Drupal\node\Entity\Node')
      ->disableOriginalConstructor()
      ->getMock();
    $node->method('id')
      ->willReturn(1);

    $this->entityRepository->method('loadEntityByUuid')
      ->willReturn($node);

    $this->contentHubEntitiesTracking
      ->expects($this::exactly(2))
      ->method('setExportedEntity')
      ->willReturn($contentHubEntitiesTracking);

    $this->contentHubEntitiesTracking
      ->expects($this::exactly(2))
      ->method('getSiteOrigin')
      ->willReturn('00000000-0000-0000-0000-000000000000');

    $this->contentHubEntitiesTracking
      ->expects($this::exactly(4))
      ->method('save');

    $this->contentHubEntityExportController->trackExportedEntity($cdf, FALSE);
    $this->assertEquals(FALSE, $contentHubEntitiesTracking->isExported());

    $this->contentHubEntityExportController->trackExportedEntity($cdf, TRUE);
    $this->assertEquals(TRUE, $contentHubEntitiesTracking->isExported());

    $contentHubEntitiesTracking = $this->getContentHubEntitiesTrackingService();
    $contentHubEntitiesTracking->setExportedEntity($entity->entity_type,
      $entity->entity_id,
      $entity->entity_uuid,
      $entity->modified,
      $entity->origin
    );

    $this->contentHubEntitiesTracking
      ->expects($this::exactly(2))
      ->method('loadExportedByUuid')
      ->willReturn($contentHubEntitiesTracking);

    $this->contentHubEntityExportController->trackExportedEntity($cdf, FALSE);
    $this->assertEquals(FALSE, $contentHubEntitiesTracking->isExported());

    $this->contentHubEntityExportController->trackExportedEntity($cdf, TRUE);
    $this->assertEquals(TRUE, $contentHubEntitiesTracking->isExported());
  }

  /**
   * Tests exportEntities method.
   *
   * @covers ::exportEntities
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testExportEntities() {
    $candidate_entities = $this->getSampleCandidateEntities();
    $this->contentHubInternalRequest
      ->method('getEntityCdfByInternalRequest')
      ->willReturn($this->getEntities());

    list(, , $node) = $this->getBasicEntities();
    $this->entityRepository->method('loadEntityByUuid')
      ->willReturn($node);

    $actual_result = $this->contentHubEntityExportController->exportEntities($candidate_entities);
    $this->assertEquals(FALSE, $actual_result);

    $this->config
      ->method('get')
      ->with('export_with_queue')
      ->willReturn(TRUE);
    $this->instantiateContentHubEntityController();

    $this->entityRepository->method('loadEntityByUuid')
      ->willReturnOnConsecutiveCalls(...array_values($this->getSampleCandidateEntities()));

    $entity = $this->getSampleContentHubEntity();
    $contentHubEntitiesTracking = $this->getContentHubEntitiesTrackingService();
    $contentHubEntitiesTracking->setExportedEntity($entity->entity_type,
      $entity->entity_id,
      $entity->entity_uuid,
      $entity->modified,
      $entity->origin
    );
    $this->contentHubEntitiesTracking
      ->method('setExportedEntity')
      ->willReturn($contentHubEntitiesTracking);

    $this->contentHubExportQueueController->expects($this->once())
      ->method('enqueueExportEntities')
      ->willReturnCallback(function ($candidate_entities) {
        return $candidate_entities;
      });

    $actual_result = $this->contentHubEntityExportController->exportEntities($candidate_entities);
    $this->assertEquals(TRUE, $actual_result);
  }

  /**
   * Tests queueExportedEntity method.
   *
   * @covers ::queueExportedEntity
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testQueueExportedEntity() {
    $entity = $this->getSampleContentHubEntity();
    $contentHubEntitiesTracking = $this->getContentHubEntitiesTrackingService();
    $contentHubEntitiesTracking->setExportedEntity($entity->entity_type,
      $entity->entity_id,
      $entity->entity_uuid,
      $entity->modified,
      $entity->origin
    );

    /** @var \Drupal\Core\Entity\ContentEntityInterface|\PHPUnit\Framework\MockObject\MockObject $node */
    $node = $this->getMockBuilder('\Drupal\node\Entity\Node')
      ->disableOriginalConstructor()
      ->getMock();
    $node->method('id')
      ->willReturn($entity->entity_id);
    $node->method('uuid')
      ->willReturn($entity->entity_uuid);
    $node->method('getEntityTypeId')
      ->willReturn($entity->entity_type);

    $this->contentHubEntitiesTracking
      ->expects($this::exactly(2))
      ->method('save');

    $this->entityRepository
      ->method('loadEntityByUuid')
      ->willReturn($node);

    $this->contentHubEntitiesTracking
      ->method('getSiteOrigin')
      ->willReturn('00000000-0000-0000-0000-000000000000');

    $this->contentHubEntitiesTracking
      ->method('setExportedEntity')
      ->willReturn($contentHubEntitiesTracking);

    $this->contentHubEntityExportController->queueExportedEntity($node);
    $this->assertEquals(TRUE, $contentHubEntitiesTracking->isQueued());

    $contentHubEntitiesTracking = $this->getContentHubEntitiesTrackingService();
    $contentHubEntitiesTracking->setExportedEntity($entity->entity_type,
      $entity->entity_id,
      $entity->entity_uuid,
      $entity->modified,
      $entity->origin
    );

    $this->contentHubEntitiesTracking
      ->method('loadExportedByUuid')
      ->willReturn($contentHubEntitiesTracking);

    $this->contentHubEntityExportController->queueExportedEntity($node);
    $this->assertEquals(TRUE, $contentHubEntitiesTracking->isQueued());
  }

  /**
   * Tests isRequestFromAcquiaContentHub method.
   *
   * @covers ::isRequestFromAcquiaContentHub
   */
  public function testIsRequestFromAcquiaContentHub() {
    $_SERVER['HTTP_USER_AGENT'] = 'Go-http-client';
    $this->assertTrue($this->contentHubEntityExportController->isRequestFromAcquiaContentHub());

    $_SERVER['HTTP_USER_AGENT'] = 'Unknown';
    $this->assertFalse($this->contentHubEntityExportController->isRequestFromAcquiaContentHub());

    unset($_SERVER['HTTP_USER_AGENT']);
    $this->assertFalse($this->contentHubEntityExportController->isRequestFromAcquiaContentHub());
  }

  /**
   * Returns array of basic Drupal entities: taxonomy term, node and user.
   *
   * @return array
   *   A set of basic Drupal entities.
   */
  protected function getBasicEntities() {
    $taxonomy_term = $this->getMockBuilder('Drupal\taxonomy\Entity\Term')
      ->disableOriginalConstructor()
      ->getMock();
    $taxonomy_term->method('getEntityTypeId')
      ->willReturn('taxonomy_term');

    $node = $this->getMockBuilder('Drupal\node\Entity\Node')
      ->disableOriginalConstructor()
      ->getMock();
    $node->method('getEntityTypeId')
      ->willReturn('node');

    $user = $this->getMockBuilder('Drupal\user\Entity\User')
      ->disableOriginalConstructor()
      ->getMock();
    $user->method('getEntityTypeId')
      ->willReturn('user');

    return [$taxonomy_term, $node, $user];
  }

  /**
   * Returns array of sample candidate entities.
   *
   * @see testExportEntities()
   *
   * @return array
   *   A set of sample candidate entities keyed by UUID.
   */
  private function getSampleCandidateEntities() {
    list($taxonomy_term, $node, $user) = $this->getBasicEntities();

    return [
      '00000000-0000-0000-0000-000000000001' => $taxonomy_term,
      '00000000-0000-0000-0000-000000000002' => $node,
      '00000000-0000-0000-0000-000000000003' => $user,
      '00000000-0000-0000-0000-000000000004' => $user,
    ];
  }

  /**
   * Returns array of sample candidate referenced entities.
   *
   * @see testExportEntities()
   *
   * @return array
   *   A set of sample candidate referenced entities keyed by UUID.
   */
  private function getSampleCandidateReferencedEntities() {
    list($taxonomy_term, $node, $user) = $this->getBasicEntities();

    return [
      '00000000-0000-0000-0000-000000000005' => $taxonomy_term,
      '00000000-0000-0000-0000-000000000002' => $node,
      '00000000-0000-0000-0000-000000000006' => $user,
    ];
  }

  /**
   * Returns sample ContentHub entity object.
   *
   * @return \stdClass
   *   ContentHub object.
   */
  private function getSampleContentHubEntity() {
    return (object) [
      'entity_type' => 'node',
      'entity_id' => 1,
      'entity_uuid' => '00000000-0000-0000-0000-000000000000',
      'modified' => '2016-12-09T20:51:45+00:00',
      'origin' => '11111111-1111-1111-1111-111111111111',
    ];
  }

  /**
   * Helper method to stub response.
   *
   * @return array
   *   CDF response.
   */
  private function getEntities() {
    return [
      'entities' =>
        [
          [
            'uuid' => '00000000-1111-0000-0000-000000000000',
            'type' => 'node',
            'origin' => '00000000-0000-0000-0000-000000000000',
            'created' => '2017-11-10T13:35:23+00:00',
            'modified' => '2017-11-10T15:08:47+00:00',
            'attributes' =>
              [
                'langcode' =>
                  [
                    'type' => 'string',
                    'value' =>
                      [
                        'en' => 'en',
                      ],
                  ],
                'type' =>
                  [
                    'type' => 'string',
                    'value' =>
                      [
                        'en' => 'article',
                      ],
                  ],
                'title' =>
                  [
                    'type' => 'string',
                    'value' =>
                      [
                        'en' => 'test',
                      ],
                  ],
                'revision_log' =>
                  [
                    'type' => 'array<string>',
                    'value' =>
                      [
                        'en' => NULL,
                      ],
                  ],
                'body' =>
                  [
                    'type' => 'array<string>',
                    'value' =>
                      [
                        'en' =>
                          [
                            0 => '{"value":"\u003Cp\u003E test\u003C\/p\u003E\r\n","format":"basic_html","summary":""}',
                          ],
                      ],
                  ],
                'url' =>
                  [
                    'type' => 'string',
                    'value' =>
                      [
                        'en' => 'http://localhost/node/1',
                      ],
                  ],
              ],
          ],
        ],
    ];
  }

  /**
   * Creates a Content Hub Entity for testing purposes.
   *
   * @param array $values
   *   An array of values.
   *
   * @return \Acquia\ContentHubClient\Entity
   *   A Content Hub Entity fully loaded.
   *
   * @throws \Exception
   */
  private function createContentHubEntity(array $values = []) {
    // Defining a default entity.
    $default_entities = $this->getEntities();
    $values = $values + $default_entities['entities'][0];

    // Creating a Content Hub Entity.
    $entity = new Entity();
    $entity->setUuid($values['uuid']);
    $entity->setType($values['type']);
    $entity->setOrigin($values['origin']);
    $entity->setCreated($values['created']);
    $entity->setModified($values['modified']);

    // Adding Attributes.
    foreach ($values['attributes'] as $name => $attr) {
      $attribute = new Attribute($attr['type']);
      $attribute->setValues($attr['value']);
      $entity->setAttribute($name, $attribute);
    }

    return $entity;
  }

  /**
   * Loads a ContentHubEntitiesTracking object.
   *
   * @return \Drupal\acquia_contenthub\ContentHubEntitiesTracking
   *   The loaded object.
   */
  private function getContentHubEntitiesTrackingService() {
    /** @var \Drupal\Core\Database\Connection|\PHPUnit\Framework\MockObject\MockObject $database */
    $database = $this->getMockBuilder('\Drupal\Core\Database\Connection')
      ->disableOriginalConstructor()
      ->getMock();

    $admin_config = $this->getMockBuilder('\Drupal\Core\Config\ImmutableConfig')
      ->disableOriginalConstructor()
      ->getMock();
    $admin_config->method('get')
      ->with('origin')
      ->willReturn($this->siteOrigin);

    /** @var \Drupal\Core\Config\ConfigFactoryInterface|\PHPUnit\Framework\MockObject\MockObject $config_factory */
    $config_factory = $this->getMockBuilder('\Drupal\Core\Config\ConfigFactoryInterface')
      ->disableOriginalConstructor()
      ->getMock();
    $config_factory->method('get')
      ->with('acquia_contenthub.admin_settings')
      ->willReturn($admin_config);

    return new ContentHubEntitiesTracking($database, $config_factory);
  }

}
