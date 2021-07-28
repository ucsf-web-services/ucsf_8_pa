<?php

namespace Drupal\acquia_contenthub\EventSubscriber\View;

use Drupal\Core\EventSubscriber\MainContentViewSubscriber;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Subscriber to Remove Page Theme Wrapper.
 *
 * @package Drupal\acquia_contenthub\EventSubscriber\View
 */
class RemovePageThemeWrapperSubscriber extends MainContentViewSubscriber {

  /**
   * Interact with onViewRenderArray event.
   *
   * @return array
   *   Subscribed events.
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[KernelEvents::VIEW][] = ['onViewRenderArray', 10];
    return $events;
  }

  /**
   * Modifies render array.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent $event
   *   Controller Result Event.
   */
  public function onViewRenderArray(GetResponseForControllerResultEvent $event) {
    $request = $event->getRequest();

    if ($request->attributes->all()['_route'] === 'acquia_contenthub.content_entity_display.entity') {
      parent::onViewRenderArray($event);

      $event->getResponse()->setContent(
        self::removeSurroundingMarkup('dialog-off-canvas-main-canvas', $event->getResponse()->getContent())
      );
    }
  }

  /**
   * Removes surrounding markup for html string.
   *
   * @param \Drupal\acquia_contenthub\EventSubscriber\View\string $class_selector
   *   Selector for DOM modification.
   * @param \Drupal\acquia_contenthub\EventSubscriber\View\string $input
   *   HTML to modify.
   *
   * @return string
   *   Modified HTML.
   */
  private static function removeSurroundingMarkup(string $class_selector, string $input): string {
    $doc = new \DOMDocument();
    $doc->preserveWhiteSpace = FALSE;
    $encoded_input = mb_convert_encoding($input, 'HTML-ENTITIES', "UTF-8");
    @$doc->loadHTML($encoded_input);
    $entries = (new \DOMXPath($doc))->query("//div[contains(concat(' ', normalize-space(@class), ' '), ' ${class_selector} ')]");

    foreach ($entries as $node) {
      if (!$node->parentNode) {
        continue;
      }

      $child_nodes = [];

      foreach ($node->childNodes as $ch_node) {
        $child_nodes[] = $ch_node;
      }

      foreach ($child_nodes as $ch_node) {
        $node->parentNode->insertBefore($ch_node, $node);
      }

      $node->parentNode->removeChild($node);
    }

    return $doc->saveHTML();
  }

}
