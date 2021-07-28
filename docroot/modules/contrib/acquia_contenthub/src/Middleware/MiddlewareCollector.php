<?php

namespace Drupal\acquia_contenthub\Middleware;

use Acquia\ContentHubClient\Middleware\MiddlewareHmacInterface;

/**
 * Collector for the HMAC Middleware.
 *
 * @package Drupal\acquia_contenthub\Middleware
 */
class MiddlewareCollector implements MiddlewareCollectorInterface {

  /**
   * Middlewares for HMAC.
   *
   * @var middlewares
   */
  protected $middlewares;

  /**
   * {@inheritdoc}
   */
  public function addMiddleware(MiddlewareHmacInterface $middleware) {
    $this->middlewares[] = $middleware;
  }

  /**
   * {@inheritdoc}
   */
  public function getMiddlewares() {
    return $this->middlewares;
  }

}
