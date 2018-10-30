<?php

namespace Drupal\Tests\acquia_contenthub\Unit\Form;

use Drupal\Tests\UnitTestCase;
use Drupal\acquia_contenthub\Form\NodeForm;

require_once __DIR__ . '/../Polyfill/Drupal.php';

/**
 * PHPUnit test for the NodeForm class.
 *
 * @coversDefaultClass Drupal\acquia_contenthub\Form\NodeForm
 *
 * @group acquia_contenthub
 */
class NodeFormTest extends UnitTestCase {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  private $currentUser;

  /**
   * The Content Hub Entities Tracking Service.
   *
   * @var \Drupal\acquia_contenthub\ContentHubEntitiesTracking|\PHPUnit_Framework_MockObject_MockObject
   */
  private $contentHubEntitiesTracking;

  /**
   * The NodeForm that is being tested.
   *
   * @var \Drupal\acquia_contenthub\Form\NodeForm
   */
  private $nodeForm;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->currentUser = $this->getMock('Drupal\Core\Session\AccountInterface');
    $this->contentHubEntitiesTracking = $this->getMockBuilder('Drupal\acquia_contenthub\ContentHubEntitiesTracking')
      ->disableOriginalConstructor()
      ->getMock();
    $this->nodeForm = new NodeForm($this->currentUser, $this->contentHubEntitiesTracking);
  }

  /**
   * Tests the getForm() method, node has no id.
   *
   * @covers ::getForm
   */
  public function testGetFormNodeNoId() {
    $node = $this->getMock('\Drupal\node\NodeInterface');
    $node->expects($this->once())
      ->method('id')
      ->willReturn(NULL);

    $this->contentHubEntitiesTracking->expects($this->never())
      ->method('loadImportedByDrupalEntity');

    $form = $this->nodeForm->getForm($node);
    $this->assertNull($form);
  }

  /**
   * Tests the getForm() method, node is not imported.
   *
   * @covers ::getForm
   */
  public function testGetFormNodeNotImported() {
    $node = $this->getMock('\Drupal\node\NodeInterface');
    $node->expects($this->once())
      ->method('id')
      ->willReturn(12);
    $node->expects($this->once())
      ->method('getEntityTypeId')
      ->willReturn('node');
    $this->contentHubEntitiesTracking->expects($this->once())
      ->method('loadImportedByDrupalEntity')
      ->with('node', 12)
      ->willReturn(NULL);

    $this->contentHubEntitiesTracking->expects($this->never())
      ->method('hasLocalChange');

    $form = $this->nodeForm->getForm($node);
    $this->assertNull($form);
  }

  /**
   * Tests the getForm() method, node is imported.
   *
   * @param bool $has_local_change
   *   Has local change flag.
   * @param bool $is_auto_update
   *   Is auto update flag.
   * @param string $has_local_change_text
   *   "Has local change" text.
   *
   * @covers ::getForm
   *
   * @dataProvider providerTestGetFormNodeFormNodeIsImported
   */
  public function testGetFormNodeFormNodeIsImported($has_local_change, $is_auto_update, $has_local_change_text) {
    $node = $this->getMock('\Drupal\node\NodeInterface');
    $node->expects($this->once())
      ->method('id')
      ->willReturn(12);
    $node->expects($this->once())
      ->method('getEntityTypeId')
      ->willReturn('node');
    $this->contentHubEntitiesTracking->expects($this->once())
      ->method('loadImportedByDrupalEntity')
      ->with('node', 12)
      ->willReturn($this->contentHubEntitiesTracking);
    $this->currentUser->expects($this->once())
      ->method('hasPermission')
      ->with('administer acquia content hub')
      ->willReturn(TRUE);

    $this->contentHubEntitiesTracking->expects($this->once())
      ->method('hasLocalChange')
      ->willReturn($has_local_change);
    $this->contentHubEntitiesTracking->expects($this->once())
      ->method('isAutoUpdate')
      ->willReturn($is_auto_update);

    $form = $this->nodeForm->getForm($node);
    $this->assertEquals($has_local_change_text, $form['auto_update_label']['#markup']);
    $this->assertTrue($has_local_change === isset($form['auto_update_local_changes_label']));
  }

  /**
   * Data provider for testGetFormNodeFormNodeIsImported().
   *
   * @return array
   *   Data.
   */
  public function providerTestGetFormNodeFormNodeIsImported() {
    $yes_local_change = TRUE;
    $no_local_change = FALSE;
    $yes_auto_update = TRUE;
    $no_auto_update = FALSE;
    $yes_local_change_text = 'This content has been modified.';
    $no_local_change_text = 'What do you like to do if there are changes in the original article?';

    $data = [];

    $data['yes local change, no auto update'] = [
      $yes_local_change,
      $no_auto_update,
      $yes_local_change_text,
    ];

    $data['no local change, yes auto update'] = [
      $no_local_change,
      $yes_auto_update,
      $no_local_change_text,
    ];

    return $data;
  }

  /**
   * Tests the attachSubmitHandler() method, not attachable.
   *
   * @covers ::attachSubmitHandler
   */
  public function testAttachSubmitHandlerNotAttachable() {
    $form_actions = [];
    $form_submit_handler = 'my_handler';
    $this->nodeForm->attachSubmitHandler($form_actions, $form_submit_handler);
    $this->assertEquals([], $form_actions);
  }

  /**
   * Tests the attachSubmitHandler() method, attachable.
   *
   * @covers ::attachSubmitHandler
   */
  public function testAttachSubmitHandlerAttachable() {
    $form_actions = [
      'preview' => [
        '#type' => 'submit',
      ],
      'ignored_action' => [
        '#type' => 'ignored_type',
        '#submit' => [
          'existing_submit_handler',
        ],
      ],
      'attached_action' => [
        '#type' => 'submit',
        '#submit' => [
          'existing_submit_handler',
        ],
      ],
    ];
    $expected = [
      'preview' => [
        '#type' => 'submit',
      ],
      'ignored_action' => [
        '#type' => 'ignored_type',
        '#submit' => [
          'existing_submit_handler',
        ],
      ],
      'attached_action' => [
        '#type' => 'submit',
        '#submit' => [
          'my_handler',
          'existing_submit_handler',
        ],
      ],
    ];
    $form_submit_handler = 'my_handler';
    $this->nodeForm->attachSubmitHandler($form_actions, $form_submit_handler);
    $this->assertEquals($expected, $form_actions);
  }

  /**
   * Tests the saveSettings() method, has empty form value.
   *
   * @covers ::saveSettings
   */
  public function testSaveSettingsEmptyValue() {
    $form_state = $this->getMock('Drupal\Core\Form\FormState');
    $form_state->expects($this->once())
      ->method('isValueEmpty')
      ->willReturn(TRUE);
    $form_state->expects($this->never())
      ->method('getFormObject');
    $this->nodeForm->saveSettings($form_state);
  }

  /**
   * Tests the saveSettings() method, node is not imported.
   *
   * @covers ::saveSettings
   */
  public function testSaveSettingsNodeIsNotImported() {
    $form_state = $this->getMock('Drupal\Core\Form\FormState');
    $form_state->expects($this->once())
      ->method('isValueEmpty')
      ->willReturn(FALSE);
    $node = $this->getMock('\Drupal\node\NodeInterface');
    $node->expects($this->once())
      ->method('getEntityTypeId')
      ->willReturn('node');
    $node->expects($this->once())
      ->method('id')
      ->willReturn(12);
    $form_object = $this->getMock('Drupal\Core\Entity\EntityFormInterface');
    $form_object->expects($this->once())
      ->method('getEntity')
      ->willReturn($node);
    $form_state->expects($this->once())
      ->method('getFormObject')
      ->willReturn($form_object);
    $this->contentHubEntitiesTracking->expects($this->once())
      ->method('loadImportedByDrupalEntity')
      ->with('node', 12)
      ->willReturn(NULL);

    $this->contentHubEntitiesTracking->expects($this->never())
      ->method('hasLocalChange');

    $this->nodeForm->saveSettings($form_state);
  }

  /**
   * Tests the saveSettings() method, actually set and save.
   *
   * @param bool $old_auto_update_flag
   *   Has local change flag.
   * @param int $new_auto_update_flag
   *   Set to auto update flag.
   * @param string $method_name
   *   The method name to be called before save().
   * @param string $method_parameter
   *   The method parameter to be called before save().
   *
   * @covers ::saveSettings
   *
   * @dataProvider providerTestSaveSettingsSetAndSave
   */
  public function testSaveSettingsSetAndSave($old_auto_update_flag, $new_auto_update_flag, $method_name, $method_parameter) {
    $node = $this->getMock('\Drupal\node\NodeInterface');
    $node->expects($this->once())
      ->method('getEntityTypeId')
      ->willReturn('node');
    $node->expects($this->once())
      ->method('id')
      ->willReturn(12);
    $form_object = $this->getMock('Drupal\Core\Entity\EntityFormInterface');
    $form_object->expects($this->once())
      ->method('getEntity')
      ->willReturn($node);
    $form_state = $this->getMock('Drupal\Core\Form\FormState');
    $form_state->expects($this->once())
      ->method('isValueEmpty')
      ->willReturn(FALSE);
    $form_state->expects($this->once())
      ->method('getFormObject')
      ->willReturn($form_object);
    $this->contentHubEntitiesTracking->expects($this->once())
      ->method('loadImportedByDrupalEntity')
      ->with('node', 12)
      ->willReturn($this->contentHubEntitiesTracking);

    $this->contentHubEntitiesTracking->expects($this->once())
      ->method('isAutoUpdate')
      ->willReturn($old_auto_update_flag);
    $form_state->expects($this->once())
      ->method('getValue')
      ->with('acquia_contenthub')
      ->willReturn(['auto_update' => $new_auto_update_flag]);
    $method = $this->contentHubEntitiesTracking->expects($this->once())
      ->method($method_name);
    if ($method_parameter) {
      $method->with($method_parameter);
    }
    $this->contentHubEntitiesTracking->expects($this->once())
      ->method('save');

    $this->nodeForm->saveSettings($form_state);
  }

  /**
   * Data provider for testSaveSettingsSetAndSave().
   *
   * @return array
   *   Data.
   */
  public function providerTestSaveSettingsSetAndSave() {
    $get_auto_update_true = TRUE;
    $get_auto_update_false = FALSE;
    $set_auto_update_true = 1;
    $set_auto_update_false = 0;
    $expect_call_set_pending_sync = 'setPendingSync';
    $expect_call_set_auto_update = 'setAutoUpdate';
    $expect_parameter_true = TRUE;
    $expect_parameter_false = FALSE;
    $expect_no_parameter = NULL;

    $data = [];

    // Case 1.
    // disabled -> disabled: setAutoUpdate(FALSE)
    $data['no local change, set auto update false'] = [
      $get_auto_update_false,
      $set_auto_update_false,
      $expect_call_set_auto_update,
      $expect_parameter_false,
    ];
    // Case 2.
    // disabled -> enabled: setPendingSync()
    // local change -> enabled: setPendingSync()
    // pending sync -> enabled: setPendingSync()
    // Note: when user indicates "enable", don't set "enable", instead, set to
    // the only state "pending sync" that will lead to "enabled".
    $data['no local change, set auto update true'] = [
      $get_auto_update_false,
      $set_auto_update_true,
      $expect_call_set_pending_sync,
      $expect_no_parameter,
    ];
    // Case 3.
    // enabled -> disabled: setAutoUpdate(FALSE)
    $data['yes local change, set auto update false'] = [
      $get_auto_update_true,
      $set_auto_update_false,
      $expect_call_set_auto_update,
      $expect_parameter_false,
    ];
    // Case 4.
    // enabled -> disabled: setAutoUpdate(TRUE)
    $data['yes local change, set auto update true'] = [
      $get_auto_update_true,
      $set_auto_update_true,
      $expect_call_set_auto_update,
      $expect_parameter_true,
    ];

    return $data;
  }

}
