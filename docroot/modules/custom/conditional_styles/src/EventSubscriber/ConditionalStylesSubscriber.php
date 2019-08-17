<?php
namespace Drupal\conditional_styles\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Drupal\Core\Url;
/**
* Redirect .html pages to corresponding Node page.
*/
class ConditionalStylesSubscriber implements EventSubscriberInterface {


  private $redirectCode = 301;

  /**
  * Redirect pattern based url
  * @param GetResponseEvent $event
  */
  public function customRedirection(GetResponseEvent $event) {

  $node = \Drupal::routeMatch()->getParameter('node');
  if ($node->hasField('field_article_type')) {
    if ($node->get('field_article_type')
        ->first()
        ->getValue()['target_id'] == '413496'
    ) {
      //drupal_set_message("Has the Article Type of Media Coverage");
      $url = $node->get('field_external_url')->first()->getValue()['uri'];
      $response = new RedirectResponse($url, $this->redirectCode);
      $response->send();
      return;
      //dpm($url);
      //header('HTTP/1.1 301 Moved Permanently');
      //header('Location:'.$url);
    }
  }
  //dpm($requestUrl);
  /**
  * Here i am redirecting the about-us.html to respective /about-us node.
  * Here you can implement your logic and search the URL in the DB
  * and redirect them on the respective node.

  if ($requestUrl=='/aboutus') {
    $response = new RedirectResponse('/aboutus', $this->redirectCode);
    $response->send();
    exit(0);
    }
   */

  }

  /**
  * Listen to kernel.request events and call customRedirection.
  * {@inheritdoc}
  * @return array Event names to listen to (key) and methods to call (value)
  */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = array('customRedirection');
    return $events;
  }


}