<?php

namespace Drupal\Tests\acquia_contenthub\Unit\Session;

use Drupal\acquia_contenthub\Session\ContentHubUserSession;
use Drupal\Core\Session\AccountInterface;
use Drupal\Tests\UnitTestCase;

/**
 * PHPUnit test for the ContentHubUserSession class.
 *
 * @coversDefaultClass \Drupal\acquia_contenthub\Session\ContentHubUserSession
 *
 * @group acquia_contenthub
 */
class ContentHubUserSessionTest extends UnitTestCase {

  /**
   * Enable or disable the backup and restoration of the $GLOBALS array.
   *
   * @var bool
   */
  protected $backupGlobals = FALSE;

  /**
   * The mock config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * The mock value for the user_role setting.
   *
   * @var string
   */
  protected $userRole;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->configFactory = $this->getMock('Drupal\Core\Config\ConfigFactoryInterface');
    $this->configFactory
      ->method('get')
      ->willReturnCallback(function ($argument) {
        if ($argument == 'acquia_contenthub.entity_config') {
          $contenthub_entity_config = $this->getMockBuilder('Drupal\Core\Config\ImmutableConfig')
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMockForAbstractClass();
          $contenthub_entity_config
            ->method('get')
            ->willReturnCallback(function ($argument) {
              if ($argument == 'user_role') {
                return $this->userRole;
              }
              return NULL;
            });

          return $contenthub_entity_config;
        }
        return NULL;
      });

  }

  /**
   * Test overridden methods.
   *
   * Test overridden methods and constructor for an anonymous role.
   *
   * @covers ::isAnonymous
   * @covers ::isAuthenticated
   * @covers ::__construct
   */
  public function testAnonymousRole() {
    $this->userRole = AccountInterface::ANONYMOUS_ROLE;
    $render_account = new ContentHubUserSession(AccountInterface::ANONYMOUS_ROLE);
    $this->assertTrue($render_account->isAnonymous());
    $this->assertFalse($render_account->isAuthenticated());
    $expected = [AccountInterface::ANONYMOUS_ROLE];
    $this->assertEquals($render_account->getRoles(), $expected);
  }

  /**
   * Test overridden methods.
   *
   * Test overridden methods and constructor for an authenticated role.
   *
   * @covers ::isAnonymous
   * @covers ::isAuthenticated
   * @covers ::__construct
   */
  public function testAuthenticatedRole() {
    $render_account = new ContentHubUserSession(AccountInterface::AUTHENTICATED_ROLE);
    $this->assertFalse($render_account->isAnonymous());
    $this->assertTrue($render_account->isAuthenticated());
    $expected = [AccountInterface::AUTHENTICATED_ROLE];
    $this->assertEquals($render_account->getRoles(), $expected);
  }

  /**
   * Test overridden methods.
   *
   * Test overridden methods and constructor for a custom role.
   *
   * @covers ::isAnonymous
   * @covers ::isAuthenticated
   * @covers ::__construct
   */
  public function testCustomRole() {
    $render_account = new ContentHubUserSession('test_role');
    $this->assertFalse($render_account->isAnonymous());
    $this->assertTrue($render_account->isAuthenticated());
    $expected = [AccountInterface::AUTHENTICATED_ROLE, 'test_role'];
    $this->assertEquals($render_account->getRoles(), $expected);
  }

}
