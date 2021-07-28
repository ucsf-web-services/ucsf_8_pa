<?php

namespace Drupal\acquia_contenthub\Middleware;

use Acquia\ContentHubClient\Middleware\MiddlewareHmacInterface;

/**
 * Middleware interface for the collector.
 *
 * @package Drupal\acquia_contenthub\Middleware
 */
interface MiddlewareCollectorInterface {

  /**
   * Adds the middleware for HMAC.
   *
   * @param \Acquia\ContentHubClient\Middleware\MiddlewareHmacInterface $middleware
   *   Middleware for HMAC.
   *
   * @return mixed
   *   Middleware to add.
   */
  public function addMiddleware(MiddlewareHmacInterface $middleware);

  /**
   * Gets the middleware.
   *
   * @return mixed
   *   Middleware for client.
   */
  public function getMiddlewares();

}
