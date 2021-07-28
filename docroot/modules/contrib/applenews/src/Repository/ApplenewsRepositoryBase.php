<?php

namespace Drupal\applenews\Repository;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Base class for Apple News repository classes.
 */
class ApplenewsRepositoryBase {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Apple news channel logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * ApplenewsConfigRepository constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Psr\Log\LoggerInterface $logger
   *   Apple news channel logger.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, LoggerInterface $logger) {
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger;
  }

}
