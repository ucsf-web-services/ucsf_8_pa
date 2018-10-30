<?php

namespace Drupal\acquia_contenthub\EventSubscriber;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;
use Drupal\acquia_contenthub\Controller\ContentHubExportQueueController;

/**
 * Class ContentHubExportQueueMessageSubscriber.
 *
 * This class catches kernel.request events and displays messages from enabled
 * plugins.
 *
 * @package Drupal\admin_status
 */
class ContentHubExportQueueMessageSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * The Content Hub Export Queue Controller.
   *
   * @var \Drupal\acquia_contenthub\Controller\ContentHubExportQueueController
   */
  protected $contentHubExportQueueController;

  /**
   * Construct an ContentHubExportQueueMessageSubscriber object.
   *
   * @param \Drupal\acquia_contenthub\Controller\ContentHubExportQueueController $export_queue_controller
   *   THe Content Hub Export Queue Controller.
   */
  public function __construct(ContentHubExportQueueController $export_queue_controller) {
    $this->contentHubExportQueueController = $export_queue_controller;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // Tell Symfony that we want to hear about kernel.request events.
    $events['kernel.request'] = ['kernelRequest'];
    return $events;
  }

  /**
   * Handles kernel.request events.
   *
   * @param \Symfony\Component\EventDispatcher\Event $event
   *   The Symfony event.
   */
  public function kernelRequest(Event $event) {
    // Get our saved config data.
    $queue_items = $this->contentHubExportQueueController->getQueueCount();
    // Get current path.
    $current_path = \Drupal::service('path.current')->getPath();
    // If we are processing the queue, then drop warning messages so they will
    // not pile up at every queue run.
    if ($current_path === '/admin/config/services/acquia-contenthub/export-queue') {
      drupal_get_messages('warning');
    }
    if ($this->currentUser()->hasPermission('administer acquia content hub') && $queue_items > 1) {
      $message = $this->t('You have %items items in the <a href="@link">Content Hub Export Queue</a>. Make sure to export those items to keep your system up to date.', [
        '%items' => $queue_items,
        '@link' => '/admin/config/services/acquia-contenthub/export-queue',
      ]);
      drupal_set_message($message, 'warning');
    }
  }

  /**
   * Obtains the current user.
   *
   * @return \Drupal\Core\Session\AccountProxyInterface
   *   The current user object.
   */
  protected function currentUser() {
    return \Drupal::currentUser();
  }

}
