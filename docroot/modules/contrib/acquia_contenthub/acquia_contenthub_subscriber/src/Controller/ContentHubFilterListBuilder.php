<?php

namespace Drupal\acquia_contenthub_subscriber\Controller;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of Content Hub Filter.
 */
class ContentHubFilterListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    // Check whether there is a REST Resource Config for Content Hub Filters.
    /** @var \Drupal\Core\Entity\EntityStorageInterface $resource_config_storage */
    $resource_config_storage = \Drupal::entityTypeManager()->getStorage('rest_resource_config');
    if (!$resource_config_storage->load('contenthub_filter')) {
      $message = $this->t('Your site does not seem to have a REST Resource Configuration enabled for Content Hub Filters. Please make sure you have installed the configuration in YML file: @file', [
        '@file' => 'acquia_contenthub_subscriber/config/install/rest.resource.contenthub_filter.yml',
      ]);
      \Drupal::messenger()->addError($message);
    }

    $header['name'] = $this->t('Content Hub Filter');
    $header['id'] = $this->t('Machine name');
    $header['publish_setting'] = $this->t('Publish setting');
    $header['author'] = $this->t('Author');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['name'] = $this->getLabel($entity);
    $row['id'] = $entity->id();
    $row['publish_setting'] = $entity->getPublishSetting();
    $row['author'] = $entity->getAuthor();

    // You probably want a few more properties here...
    return $row + parent::buildRow($entity);
  }

}
