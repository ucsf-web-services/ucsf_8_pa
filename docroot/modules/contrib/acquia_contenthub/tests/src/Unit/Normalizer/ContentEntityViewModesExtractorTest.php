<?php

namespace Drupal\Tests\acquia_contenthub\Unit\Normalizer;

use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\HtmlResponse;
use Drupal\Core\Session\AccountInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\acquia_contenthub\Normalizer\ContentEntityViewModesExtractor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

require_once __DIR__ . '/../Polyfill/Drupal.php';

/**
 * PHPUnit test for the ContentEntityViewModesExtractor class.
 *
 * @coversDefaultClass \Drupal\acquia_contenthub\Normalizer\ContentEntityViewModesExtractor
 *
 * @group acquia_contenthub
 */
class ContentEntityViewModesExtractorTest extends UnitTestCase {

  /**
   * The current session user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $currentUser;

  /**
   * Entity display repository.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityDisplayRepository;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityTypeManager;

  /**
   * Entity type repository.
   *
   * @var \Drupal\Core\Entity\EntityTypeRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityTypeRepository;

  /**
   * Entity Config Storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityConfigStorage;

  /**
   * The entity type config.
   *
   * @var \Drupal\acquia_contenthub\ContentHubEntityTypeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityTypeConfig;

  /**
   * Renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $renderer;

  /**
   * The Kernel.
   *
   * @var \Symfony\Component\HttpKernel\HttpKernelInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $kernel;

  /**
   * Account Switcher Service.
   *
   * @var \Drupal\Core\Session\AccountSwitcherInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $accountSwitcher;

  /**
   * Content Entity.
   *
   * @var \Drupal\Core\Entity\ContentEntityInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $contentEntity;

  /**
   * Content Hub Subscription.
   *
   * @var \Drupal\acquia_contenthub\ContentHubSubscription|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $contentHubSubscription;

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $configFactory;

  /**
   * The Block Manager.
   *
   * @var \Drupal\Core\Block\BlockManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $blockManager;

  /**
   * Settings.
   *
   * @var \Drupal\Core\Config\Config|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $settings;

  /**
   * Content Entity View Modes Extractor.
   *
   * @var \Drupal\acquia_contenthub\Normalizer\ContentEntityViewModesExtractor
   */
  protected $contentEntityViewModesExtractor;

  /**
   * The Request Stack Service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->currentUser = $this->getMock('Drupal\Core\Session\AccountProxyInterface');
    $this->entityDisplayRepository = $this->getMock('Drupal\Core\Entity\EntityDisplayRepositoryInterface');
    $this->entityTypeManager = $this->prophesize(EntityTypeManagerInterface::class);
    $this->entityTypeRepository = $this->getMock('Drupal\Core\Entity\EntityTypeRepositoryInterface');
    $this->entityConfigStorage = $this->getMock('\Drupal\Core\Entity\EntityStorageInterface');
    $this->entityTypeManager->getStorage('acquia_contenthub_entity_config')->willReturn($this->entityConfigStorage);
    $this->entityTypeConfig = $this->getMock('Drupal\acquia_contenthub\ContentHubEntityTypeConfigInterface');
    $this->renderer = $this->getMock('Drupal\Core\Render\RendererInterface');
    $this->kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');
    $this->accountSwitcher = $this->getMock('Drupal\Core\Session\AccountSwitcherInterface');
    $this->contentEntity = $this->getMock('Drupal\Core\Entity\ContentEntityInterface');
    $this->contentHubSubscription = $this->getMockBuilder('\Drupal\acquia_contenthub\ContentHubSubscription')
      ->disableOriginalConstructor()
      ->getMock();
    $this->configFactory = $this->getMock('Drupal\Core\Config\ConfigFactoryInterface');
    $this->blockManager = $this->getMock('Drupal\Core\Block\BlockManagerInterface');

    $config = $this->getMockBuilder('Drupal\Core\Config\Config')
      ->disableOriginalConstructor()
      ->getMock();
    $config->expects($this->once())
      ->method('get')
      ->with('user_role')
      ->willReturn(AccountInterface::ANONYMOUS_ROLE);

    $this->configFactory->expects($this->once())
      ->method('get')
      ->with('acquia_contenthub.entity_config')
      ->willReturn($config);

    $this->requestStack = $this->getMockBuilder('Symfony\Component\HttpFoundation\RequestStack')
      ->disableOriginalConstructor()
      ->getMock();
  }

  /**
   * Test the getRenderedViewModes method, configured not to be rendered.
   *
   * @covers ::getRenderedViewModes
   */
  public function testGetRenderedViewModesConfiguredNotToBeRendered() {
    $this->contentEntity->expects($this->once())
      ->method('getEntityTypeId')
      ->willReturn('entity_type_1');
    $this->contentEntity->expects($this->once())
      ->method('bundle')
      ->willReturn('bundle_1');
    $this->contentEntity->expects($this->any())
      ->method('isNew')
      ->willReturn(FALSE);

    $this->entityConfigStorage->expects($this->once())
      ->method('loadMultiple')
      ->with(['entity_type_1'])
      ->willReturn([]);
    $contentEntityViewModesExtractor = new ContentEntityViewModesExtractor($this->currentUser, $this->entityDisplayRepository, $this->entityTypeManager->reveal(), $this->renderer, $this->kernel, $this->accountSwitcher, $this->contentHubSubscription, $this->configFactory, $this->blockManager, $this->requestStack);
    $rendered_view_modes = $contentEntityViewModesExtractor->getRenderedViewModes($this->contentEntity);

    $this->assertNull($rendered_view_modes);
  }

  /**
   * Test the getRenderedViewModes method, has view mode.
   *
   * @covers ::getRenderedViewModes
   */
  public function testGetRenderedViewModesHasViewMode() {
    $this->contentEntity->expects($this->any())
      ->method('getEntityTypeId')
      ->willReturn('entity_type_1');
    $this->contentEntity->expects($this->any())
      ->method('bundle')
      ->willReturn('bundle_1');
    $this->contentEntity->expects($this->any())
      ->method('isNew')
      ->willReturn(FALSE);

    $this->entityConfigStorage->expects($this->any(0))
      ->method('loadMultiple')
      ->with(['entity_type_1'])
      ->willReturn(['entity_type_1' => $this->entityTypeConfig]);
    $this->entityConfigStorage->expects($this->any(1))
      ->method('loadMultiple')
      ->with(['entity_type_1'])
      ->willReturn(['entity_type_1' => $this->entityTypeConfig]);
    $this->entityTypeConfig->expects($this->once())
      ->method('getRenderingViewModes')
      ->with('bundle_1')
      ->willReturn(['view_mode_2']);
    $this->entityDisplayRepository->expects($this->once())
      ->method('getViewModeOptionsByBundle')
      ->with('entity_type_1', 'bundle_1')
      ->willReturn([
        'view_mode_1' => 'view_mode_1 label',
        'view_mode_2' => 'view_mode_2 label',
      ]);

    $this->entityTypeConfig->expects($this->once())
      ->method('getPreviewImageField')
      ->with('bundle_1')
      ->willReturn('field_media->field_image');
    $this->entityTypeConfig->expects($this->once())
      ->method('getPreviewImageStyle')
      ->with('bundle_1')
      ->willReturn('medium');

    $field_media = $this->getMockBuilder('Drupal\Core\Entity\ContentEntityInterface')
      ->disableOriginalConstructor()
      ->getMock();
    $field_image = $this->getMockBuilder('Drupal\Core\Entity\ContentEntityInterface')
      ->disableOriginalConstructor()
      ->getMock();
    $media_entity = $this->getMock('Drupal\Core\Entity\EntityInterface');
    $image_entity = $this->getMock('Drupal\file\FileInterface');
    $image_entity->expects($this->once())
      ->method('bundle')
      ->willReturn('file');
    $image_entity->expects($this->once())
      ->method('getFileUri')
      ->willReturn('a_file_uri');

    $this->contentEntity->field_media = $field_media;
    $this->contentEntity->field_media->entity = $media_entity;
    $this->contentEntity->field_media->entity->field_image = $field_image;
    $this->contentEntity->field_media->entity->field_image->entity = $image_entity;

    $entity_storage = $this->getMock('Drupal\Core\Entity\EntityStorageInterface');
    $this->entityTypeRepository = $this->getMock('Drupal\Core\Entity\EntityTypeRepositoryInterface');

    //$container = $this->getMock('Drupal\Core\DependencyInjection\Container');
    $image_style = $this->getMockBuilder('Drupal\image\Entity\ImageStyle')
      ->disableOriginalConstructor()
      ->getMock();
    $url_generator = $this->getMockBuilder('Drupal\Core\Routing\UrlGenerator')
      ->disableOriginalConstructor()
      ->getMock();
    $url_generator->expects($this->once())
      ->method('getPathFromRoute')
      ->willReturn('a_generated_url');

    // Setting up the Container.
    $container = new ContainerBuilder();
    $container->set('entity_type.manager', $this->entityTypeManager);
    $container->set('entity_type.repository', $this->entityTypeRepository);
    $container->set('url_generator', $url_generator);
    \Drupal::setContainer($container);

    $image_entity->expects($this->once())
      ->method('bundle')
      ->willReturn('file');
    $image_entity->expects($this->once())
      ->method('getFileUri')
      ->willReturn('a_file_uri');
    $this->entityTypeManager->getStorage('image_style')->willReturn($entity_storage);
    $entity_storage->expects($this->once())
      ->method('load')
      ->with('medium')
      ->willReturn($image_style);
    $image_style->expects($this->once())
      ->method('buildUrl')
      ->with('a_file_uri')
      ->willReturn('a_style_decorated_file_uri');

    $this->contentHubSubscription->expects($this->once())
      ->method('setHmacAuthorization')
      ->willReturnCallback(function (Request $request, $use_shared_secret) {
        $request->headers->set('Authorization', 'Acquia ContentHub:testSignature');
        return $request;
      });

    $request = new Request();
    // Set cookies and server variables to the main request that are expected
    // to be carried over to internal subrequests.
    $request->cookies->set('test', 'cookie_test_value');
    $request->server->set('test', 'server_test_value');
    $this->requestStack->expects($this->once())->method('getCurrentRequest')->willReturn($request);

    // Creating response obtained from the internal sub-request. Check that the
    // cookies and server variables are passed to the sub-request.
    $this->kernel->expects($this->once())
      ->method('handle')
      ->willReturnCallback(function (Request $request, $type) {
        $cookie_value = $request->cookies->get('test');
        $server_value = $request->server->get('test');
        $authorization_header = $request->headers->get('Authorization');
        $output = "<html>{$type}|{$cookie_value}|{$server_value}|{$authorization_header}</html>";
        $response = new HtmlResponse();
        $response->setContent($output);
        return $response;
      });

    $contentEntityViewModesExtractor = new ContentEntityViewModesExtractor($this->currentUser, $this->entityDisplayRepository, $this->entityTypeManager->reveal(), $this->renderer, $this->kernel, $this->accountSwitcher, $this->contentHubSubscription, $this->configFactory, $this->blockManager, $this->requestStack);

    $rendered_view_modes = $contentEntityViewModesExtractor->getRenderedViewModes($this->contentEntity);

    $expected_rendered_view_modes = [
      'view_mode_2' => [
        'id' => 'view_mode_2',
        'preview_image' => 'file_create_url:a_style_decorated_file_uri',
        'label' => 'view_mode_2 label',
        'url' => '/a_generated_url',
        'html' => '<html>' . HttpKernelInterface::SUB_REQUEST . '|cookie_test_value|server_test_value|Acquia ContentHub:testSignature</html>',
      ],
    ];

    $this->assertEquals($expected_rendered_view_modes, $rendered_view_modes);
  }

}
