<?php

namespace Drupal\Tests\acquia_contenthub\Unit;

use Drupal\acquia_contenthub\ContentHubSubscription;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Drupal\Core\Session\AccountSwitcherInterface;
use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Drupal\Tests\UnitTestCase;
use Drupal\Core\State\StateInterface;
use Drupal\acquia_contenthub\ContentHubInternalRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

/**
 * PHPUnit for the ContentHubInternalRequest class.
 *
 * @coversDefaultClass \Drupal\acquia_contenthub\ContentHubInternalRequest
 *
 * @group acquia_contenthub
 */
class ContentHubInternalRequestTest extends UnitTestCase {

  /**
   * The dependency injection container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerBuilder|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $container;

  /**
   * Content Hub Client Manager.
   *
   * @var \Drupal\acquia_contenthub\Client\ClientManager|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $clientManager;

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $loggerFactory;

  /**
   * The Basic HTTP Kernel to make requests.
   *
   * @var \Symfony\Component\HttpKernel\HttpKernelInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $kernel;

  /**
   * Content Hub Subscription.
   *
   * @var \Drupal\acquia_contenthub\ContentHubSubscription|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $contentHubSubscription;

  /**
   * The account switcher service.
   *
   * @var \Drupal\Core\Session\AccountSwitcherInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $accountSwitcher;

  /**
   * The Request Stack Service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $requestStack;

  /**
   * The Content Hub Internal Request.
   *
   * @var \Drupal\acquia_contenthub\ContentHubInternalRequest
   */
  protected $contentHubInternalRequest;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Setting up Drupal container.
    $this->container = $this->getMock('Drupal\Core\DependencyInjection\Container');
    \Drupal::setContainer($this->container);

    $prophecy = $this->prophesize(Config::class);
    $prophecy->get('user_role')->willReturn(AccountInterface::ANONYMOUS_ROLE);
    $config = $prophecy->reveal();

    $config_factory = $this->prophesize(ConfigFactoryInterface::class);
    $config_factory->get('acquia_contenthub.entity_config')->willReturn($config);
    $config_factory->getEditable('acquia_contenthub.admin_settings')->willReturn($config);

    $this->loggerFactory = $this->prophesize(LoggerChannelFactoryInterface::class)->reveal();
    $this->kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');

    $this->accountSwitcher = $this->prophesize(AccountSwitcherInterface::class)->reveal();
    $this->requestStack = $this->getMockBuilder('Symfony\Component\HttpFoundation\RequestStack')
      ->disableOriginalConstructor()
      ->getMock();

    $this->clientManager = $this->getMockBuilder('Drupal\acquia_contenthub\Client\ClientManager')
      ->disableOriginalConstructor()
      ->getMock();

    $state = $this->prophesize(StateInterface::class);
    $state->get('acquia_contenthub.shared_secret')->willReturn('shared_secret');
    $this->contentHubSubscription = new ContentHubSubscription($this->loggerFactory, $config_factory->reveal(), $this->clientManager, $state->reveal());

    // Initializing service.
    $this->contentHubInternalRequest = new ContentHubInternalRequest($this->kernel, $this->contentHubSubscription, $this->accountSwitcher, $config_factory->reveal(), $this->loggerFactory, $this->requestStack);
  }

  /**
   * Test the getEntityCdfByInternalRequest method.
   *
   * This test only verifies that we are passing cookies and server variables
   * to the child subrequest created by this service.
   *
   * @covers ::getEntityCdfByInternalRequest
   */
  public function testGetEntityCdfByInternalRequest() {
    // Setting up services.
    $GLOBALS['base_path'] = '/';
    $url_generator = $this->getMock('Drupal\Core\Routing\UrlGeneratorInterface');
    $url_generator
      ->method('generateFromRoute')
      ->willReturnCallback(function ($name, $parameters, $options, $bubbleable_metadata) {
        if ($name == 'acquia_contenthub.entity.node.GET.acquia_contenthub_cdf') {
          return '/acquia-contenthub-cdf/' . $parameters['entity_type'] . '/' . $parameters['entity_id'] . '?_format=' . $parameters['_format'];
        }
        return NULL;
      });

    $this->container->expects($this->once())->method('get')->with('url_generator')->willReturn($url_generator);
    $this->clientManager->expects($this->once())->method('isConnected')->willReturn(TRUE);
    $this->clientManager->expects($this->once())->method('getRequestSignature')->withAnyParameters()->willReturn('testSignature');

    $session = new Session(new MockArraySessionStorage());
    $request = new Request();
    $request->setSession($session);

    // Set cookies and server variables to the main request that are expected
    // to be carried over to internal subrequests.
    $request->cookies->set('test', 'cookie_test_value');
    $request->server->set('test', 'server_test_value');

    // Assign request to request stack.
    $this->kernel
      ->method('handle')
      ->willReturnCallback(function (Request $request, $type) {
        $output = [
          'type' => $type,
          'uri' => $request->getRequestUri(),
          'headers' => $request->headers->all(),
          'cookies' => $request->cookies->all(),
          'server' => $request->server->all(),
        ];
        $response = new JsonResponse($output);
        return $response;
      });
    $this->requestStack->expects($this->once())->method('getCurrentRequest')->willReturn($request);

    // Executing the test.
    $entity_type = 'node';
    $entity_id = 1;
    $output = $this->contentHubInternalRequest->getEntityCdfByInternalRequest($entity_type, $entity_id, FALSE);
    unset($GLOBALS['base_path']);

    // Verify that we obtained the cookies and server variables passed to the
    // subrequest.
    $this->assertEquals(HttpKernelInterface::SUB_REQUEST, $output['type']);
    $this->assertTrue(isset($output['headers']['authorization']));
    $this->assertEquals('Acquia ContentHub:testSignature', $output['headers']['authorization'][0]);
    $this->assertTrue(isset($output['cookies']['test']));
    $this->assertEquals('cookie_test_value', $output['cookies']['test']);
    $this->assertTrue(isset($output['server']['test']));
    $this->assertEquals('server_test_value', $output['server']['test']);
  }

}
