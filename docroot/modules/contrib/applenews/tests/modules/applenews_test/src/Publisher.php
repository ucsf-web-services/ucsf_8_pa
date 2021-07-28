<?php

namespace Drupal\applenews_test;

use Drupal\applenews\Publisher as BasePublisher;

/**
 * Override the Apple News publisher service for testing purposes.
 */
class Publisher extends BasePublisher {

  /**
   * Set a response in a queue.
   *
   * Responses are picked off in FIFO order for any request to the Apple News
   * Publisher API.
   *
   * @param string $response
   *   JSON response.
   */
  public static function setResponse($response) {
    $responses = \Drupal::state()->get(static::class, []);
    $responses[] = $response;
    \Drupal::state()->set(static::class, $responses);
  }

  /**
   * Pick the next response off of the response queue.
   *
   * @return mixed
   *   The next response from the FIFO response queue.
   */
  public static function getResponse() {
    $responses = \Drupal::state()->get(static::class, []);
    $response = array_shift($responses);
    \Drupal::state()->set(static::class, $responses);
    return json_decode($response, FALSE);
  }

  /**
   * {@inheritdoc}
   */
  public function getChannel($channel_id) {
    return static::getResponse();
  }

  /**
   * {@inheritdoc}
   */
  public function getArticle($article_id) {
    return static::getResponse();
  }

  /**
   * {@inheritdoc}
   */
  public function getSection($section_id) {
    return static::getResponse();
  }

  /**
   * {@inheritdoc}
   */
  public function getSections($channel_id) {
    return static::getResponse();
  }

  /**
   * {@inheritdoc}
   */
  public function postArticle($channel_id, array $data) {
    return static::getResponse();
  }

  /**
   * {@inheritdoc}
   */
  public function updateArticle($article_id, array $data) {
    return static::getResponse();
  }

  /**
   * {@inheritdoc}
   */
  public function deleteArticle($article_id) {
    return static::getResponse();
  }

}
