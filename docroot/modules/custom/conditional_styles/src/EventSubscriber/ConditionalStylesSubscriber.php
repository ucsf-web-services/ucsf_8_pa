<?php
namespace Drupal\conditional_styles\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Drupal\Core\Url;
//use Drupal\user\Entity\User;
/**
* Redirect .html pages to corresponding Node page.
*/
class ConditionalStylesSubscriber implements EventSubscriberInterface {


  private $redirectCode = 301;

  /**
  * Redirect pattern based url
  * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
  */
  public function customRedirection(RequestEvent $event) {

    $node = \Drupal::routeMatch()->getParameter('node');
    $userRoles = \Drupal::currentUser()->getRoles();

    $admin_context = \Drupal::service('router.admin_context');
    if ($admin_context->isAdminRoute()) {
      // perform tasks.
      return;
    }

    $roles = ['administrator','editor', 'publisher', 'manager'];
    $isAdmin = false;
    foreach ($roles as $role) {
      if (in_array($role, $userRoles)) {
        $isAdmin = true;
      }
    }
    //dpm('are we some sort of admin: '. $isAdmin);
    // this isn't working because of some Drupal cache thing going (!$isAdmin)
    if (is_object($node) && $node->hasField('field_article_type') && $node->hasField('field_external_url')) {
      \Drupal::service('page_cache_kill_switch')->trigger();
      //dpm($node);
      if ($node->get('field_article_type')->first()->getValue()['target_id'] == '413496') {
        if ($node->get('field_external_url')->first() !== null) {
          $url = $node->get('field_external_url')->first()->getValue()['uri'];
          $response = new RedirectResponse($url, $this->redirectCode);
          $response->send();
          return;
        }
      }
    }
  }



  /**
  * Listen to kernel.request events and call customRedirection.
  * {@inheritdoc}
  * @return array Event names to listen to (key) and methods to call (value)
  */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['customRedirection'];
    return $events;
  }


}
