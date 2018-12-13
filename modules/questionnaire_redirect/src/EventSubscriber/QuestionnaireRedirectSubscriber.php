<?php
/**
 * @file
 * Contains Drupal\questionnaire_redirect\EventSubscriber\QuestionnaireRedirectSubscriber
 */

namespace Drupal\questionnaire_redirect\EventSubscriber;

use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class QuestionnaireRedirectSubscriber implements EventSubscriberInterface {

  public static function getSubscribedEvents() {
    return([
      KernelEvents::REQUEST => [
        ['onRequest'],
      ]
    ]);
  }

  /**
   * Redirect requests.
   *
   * @param GetResponseEvent $event
   * @return void
   */
  public function onRequest(GetResponseEvent $event) {
    $request = $event->getRequest();
    // This is necessary because this also gets called on
    // node sub-tabs such as "edit", "revisions", etc.  This
    // prevents those pages from redirected.
    if ($request->attributes->get('_route') !== 'entity.node.canonical') {
      return;
    }

    $path = \Drupal::request()->getRequestUri();
    $alias = \Drupal::service('path.alias_manager')->getAliasByPath($path);

    // Check if path is already an alias, basically to avoid loop.
    if ($path != $alias) {
      $response = new RedirectResponse($alias, 301);
      $event->setResponse($response);
    }
  }
}
