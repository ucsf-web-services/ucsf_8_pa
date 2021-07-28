<?php

namespace Drupal\applenews\Repository;

use Drupal\applenews\Entity\ApplenewsTemplate;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\TypedData\Exception\MissingDataException;

/**
 * Apple news channel repository.
 *
 * Helper methods for dealing with Apple News template config entities.
 */
class ApplenewsTemplateRepository extends ApplenewsRepositoryBase {

  /**
   * Get all configured Apple News templates for the given entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   An entity.
   *
   * @return \Drupal\applenews\Entity\ApplenewsTemplate[]
   *   An array of Apple News templates indexed by id.
   */
  public function getTemplatesForEntity(EntityInterface $entity): array {
    $templates = [];

    try {
      $storage = $this->entityTypeManager->getStorage('applenews_template');
      $entity_ids = $storage->getQuery()
        ->condition('node_type', $entity->bundle())
        ->execute();
      return $storage->loadMultiple($entity_ids);
    }
    catch (\Exception $e) {
      $this->logger->error('Error loading templates: %code : %message', [
        '%code' => $e->getCode(),
        '%message' => $e->getMessage(),
      ]);
    }

    return $templates;
  }

  /**
   * Get an Apple News template by the given id if it exists.
   *
   * @param string $template_id
   *   A template id.
   *
   * @return \Drupal\applenews\Entity\ApplenewsTemplate
   *   The Apple News template for the given id.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   *   When we could not find a template by the given id.
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getTemplateByTemplateId(string $template_id): ApplenewsTemplate {
    /** @var \Drupal\applenews\Entity\ApplenewsTemplate $template */
    $template = $this->entityTypeManager->getStorage('applenews_template')->load($template_id);
    if (empty($template)) {
      throw new MissingDataException(sprintf('Could not find a template by id %s.', $template_id));
    }
    return $template;
  }

}
