<?php

namespace Drupal\acquia_contenthub\Middleware;

use Acquia\ContentHubClient\Middleware\MiddlewareHmacInterface;

interface MiddlewareCollectorInterface {

  /**
   * @param \Acquia\ContentHubClient\Middleware\MiddlewareHmacInterface $middleware
   * @return mixed
   */
  public function addMiddleware(MiddlewareHmacInterface $middleware);

  /**
   * @return mixed
   */
  public function getMiddlewares();
}
