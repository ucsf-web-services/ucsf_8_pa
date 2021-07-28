<?php

namespace Drupal\applenews\Repository;

/**
 * Apple news channel repository.
 *
 * Helper methods for dealing with Apple News channel content entities.
 */
class ApplenewsChannelRepository extends ApplenewsRepositoryBase {

  /**
   * Get all configured Apple News channels.
   *
   * @return \Drupal\applenews\Entity\ApplenewsChannel[]
   *   An array of Apple News channels indexed by id.
   */
  public function getChannels(): array {
    $channels = [];

    try {
      $storage = $this->entityTypeManager->getStorage('applenews_channel');
      $entity_ids = $storage->getQuery()->execute();
      $channels = $storage->loadMultiple($entity_ids);
    }
    catch (\Exception $e) {
      $this->logger->error('Error loading channel: %code : %message', [
        '%code' => $e->getCode(),
        '%message' => $e->getMessage(),
      ]);
    }

    return $channels;
  }

}
