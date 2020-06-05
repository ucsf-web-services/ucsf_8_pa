<?php

namespace Drupal\acquia_contenthub\EventSubscriber\View;

use Drupal\Core\EventSubscriber\MainContentViewSubscriber;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RemovePageThemeWrapperSubscriber extends MainContentViewSubscriber {

  public static function getSubscribedEvents() {
    $events = [];
    $events[KernelEvents::VIEW][] = ['onViewRenderArray', 10];
    return $events;
  }

  public function onViewRenderArray(GetResponseForControllerResultEvent $event) {
    $request = $event->getRequest();

    if ($request->attributes->all()['_route'] === 'acquia_contenthub.content_entity_display.entity') {
      parent::onViewRenderArray($event);

      $event->getResponse()->setContent(
        self::removeSurroundingMarkup('dialog-off-canvas-main-canvas', $event->getResponse()->getContent())
      );
    }
  }

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
