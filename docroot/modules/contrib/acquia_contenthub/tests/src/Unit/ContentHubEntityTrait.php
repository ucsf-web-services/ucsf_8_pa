<?php

namespace Drupal\Tests\acquia_contenthub\Unit;

use Acquia\ContentHubClient\Entity;
use Acquia\ContentHubClient\Attribute;
use Acquia\ContentHubClient\Asset;

/**
 * Defines a Trait for creating a Content Hub Entity.
 */
trait ContentHubEntityTrait {

  /**
   * Creates a Content Hub Entity for testing purposes.
   *
   * @param array $values
   *   An array of values.
   *
   * @return \Acquia\ContentHubClient\Entity
   *   A Content Hub Entity fully loaded.
   */
  private function createContentHubEntity(array $values = []) {
    // Defining a default entity.
    $values = $values + [
      'uuid' => '00000000-1111-0000-0000-000000000000',
      'type' => 'node',
      'origin' => '00000000-0000-0000-0000-000000000000',
      'created' => '2017-12-21T20:12:11+00:00Z',
      'modified' => '2014-12-21T20:12:11+00:00Z',
      'attributes' => [
        'type' => [
          'type' => 'string',
          'value' => [
            'en' => 'article',
          ],
        ],
        'langcode' => [
          'type' => 'string',
          'value' => [
            'en' => 'en',
          ],
        ],
        'title' => [
          'type' => 'string',
          'value' => [
            'en' => 'Title Test',
          ],
        ],
        'url' => [
          'type' => 'string',
          'value' => [
            'en' => 'http://localhost/test/node/1',
          ],
        ],
      ],
      'assets' => [],
    ];
    // Creating a Content Hub Entity.
    $entity = new Entity();
    $entity->setUuid($values['uuid']);
    $entity->setType($values['type']);
    $entity->setOrigin($values['origin']);
    $entity->setCreated($values['created']);
    $entity->setModified($values['modified']);

    // Adding Attributes.
    foreach ($values['attributes'] as $name => $attr) {
      $attribute = new Attribute($attr['type']);
      $attribute->setValues($attr['value']);
      $entity->setAttribute($name, $attribute);
    }

    // Adding Assets.
    foreach ($values['assets'] as $myasset) {
      $asset = new Asset();
      $asset->setUrl($myasset['url']);
      $asset->setReplaceToken($myasset['replace-token']);
      $entity->addAsset($asset);
    }
    return $entity;
  }

}
