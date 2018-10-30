<?php

namespace Drupal\acquia_contenthub\Middleware;

use Acquia\ContentHubClient\Middleware\MiddlewareHmacInterface;

class MiddlewareCollector implements MiddlewareCollectorInterface{

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
