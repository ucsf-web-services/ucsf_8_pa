<?php

namespace Drupal\Tests\applenews\Traits;

use Drupal\applenews\Entity\ApplenewsArticle;
use Drupal\applenews\Entity\ApplenewsChannel;
use Drupal\applenews\Entity\ApplenewsTemplate;
use Drupal\Core\Entity\EntityInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;

/**
 * Test trait to aid with common Apple News testing things.
 */
trait AppleNewsTestTrait {

  /**
   * Create an Apple News channel for testing.
   *
   * @return \Drupal\applenews\Entity\ApplenewsChannel
   *   The Apple News channel entity created.
   */
  protected function createApplenewsChannel(): ApplenewsChannel {
    $channel = ApplenewsChannel::create([
      'id' => $this->randomMachineName(),
      'name' => $this->randomMachineName(),
      'sections' => [
        [
          'value' => serialize([$this->randomMachineName() => 'Main (Default)']),
        ],
      ],
    ]);
    $channel->save();
    return $channel;
  }

  /**
   * Create an Apple News template for testing.
   *
   * @param string $bundle
   *   Bundle name to associate the created template to.
   * @param array $components
   *   Array of Apple News components.
   * @param array $component_layout
   *   Array of component layout properties.
   *
   * @return \Drupal\applenews\Entity\ApplenewsTemplate
   *   The Apple News template that was created.
   */
  protected function createApplenewsTemplate(string $bundle = 'article', array $components = [], array $component_layout = []): ApplenewsTemplate {
    if (empty($components)) {
      $components = $this->getDefaultComponents($component_layout);
    }
    $template = ApplenewsTemplate::create([
      'id' => $this->randomMachineName(),
      'label' => $this->randomString(),
      'node_type' => $bundle,
      'columns' => 7,
      'width' => 1024,
      'margin' => 60,
      'gutter' => 20,
      'components' => $components,
    ]);
    $template->save();

    return $template;
  }

  /**
   * Get a default set of components consisting of just a title component.
   *
   * @param array $component_layout
   *   Component layout to set for each component in the set returned. If not
   *   sent, defaults to a simple component layout.
   *
   * @return array[]
   *   Default set of Apple News components for setting in a Apple News
   *   template.
   *
   * @see \Drupal\Tests\applenews\Traits\AppleNewsTestTrait::getDefaultComponentLayout
   */
  protected function getDefaultComponents(array $component_layout = []): array {
    if (empty($component_layout)) {
      $component_layout = $this->getDefaultComponentLayout();
    }
    $component_uuid = $this->randomMachineName();
    return [
      $component_uuid => [
        'uuid' => $component_uuid,
        'id' => 'default_text:title',
        'weight' => 1,
        'component_layout' => $component_layout,
        'component_data' => [
          'text' => [
            'field_name' => 'title',
            'field_property' => 'base',
          ],
          'format' => 'none',
        ],
      ],
    ];
  }

  /**
   * Get a default component layout.
   *
   * @return array
   *   Default component layout.
   */
  protected function getDefaultComponentLayout(): array {
    return [
      'column_start' => 0,
      'column_span' => 7,
      'margin_top' => 0,
      'margin_bottom' => 0,
      'ignore_margin' => 'none',
      'ignore_gutter' => 'none',
      'minimum_height' => 10,
      'minimum_height_unit' => 'pt',
    ];
  }

  /**
   * Create an Apple News field instance.
   *
   * @return array
   *   Array with the created field \Drupal\field\Entity\FieldStorageConfig
   *   in the first position and the created \Drupal\field\Entity\FieldConfig
   *   in the second position.
   */
  protected function createAppleNewsField($entity_type, $bundle): array {
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'applenews',
      'entity_type' => $entity_type,
      'type' => 'applenews_default',
      'cardinality' => 1,
    ]);
    $field_storage->save();
    $field_config = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => $bundle,
    ]);
    $field_config->save();
    return [$field_storage, $field_config];
  }

  /**
   * Create a node published to Apple News.
   *
   * Created node will be linked to the given channel and template.
   *
   * @param \Drupal\applenews\Entity\ApplenewsChannel $channel
   *   Apple news channel entity.
   * @param \Drupal\applenews\Entity\ApplenewsTemplate $template
   *   Apple news template entity.
   * @param \Drupal\field\Entity\FieldStorageConfig $applenews_field
   *   Apple news field name.
   *
   * @return \Drupal\node\NodeInterface
   *   Returns the created node.
   */
  protected function createNodePublishedToAppleNews(ApplenewsChannel $channel, ApplenewsTemplate $template, FieldStorageConfig $applenews_field): NodeInterface {
    $sections = $channel->getSections();
    $section_ids = array_keys($sections);
    return Node::create([
      'type' => 'article',
      'status' => 1,
      'title' => $this->randomMachineName(),
      'body' => $this->randomMachineName(16),
      $applenews_field->getName() => [
        'status' => '1',
        'template' => $template->id(),
        'channels' => serialize([
          $channel->getChannelId() => [reset($section_ids) => '1'],
        ]),
        'is_preview' => '0',
      ],
    ]);
  }

  /**
   * Assert that an Apple News article entity exists for the given entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   An entity.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function assertAppleNewsArticleExistsForEntity(EntityInterface $entity) {
    $storage = \Drupal::entityTypeManager()->getStorage('applenews_article');
    $applenews_articles = $storage->loadByProperties(['entity_id' => $entity->id()]);
    $this->assertNotEmpty($applenews_articles);
    $applenews_article = reset($applenews_articles);
    $this->assertInstanceOf(ApplenewsArticle::class, $applenews_article);
  }

  /**
   * Assert that Apple News article entity DOES NOT exist for the given entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   An entity.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function assertAppleNewsArticleNotExistsForEntity(EntityInterface $entity) {
    $storage = \Drupal::entityTypeManager()->getStorage('applenews_article');
    $storage->resetCache();
    $applenews_articles = $storage->loadByProperties(['entity_id' => $entity->id()]);
    $this->assertEmpty($applenews_articles, sprintf('Apple News article exists for %s %s (%s), expected that it would not exist.', $entity->getEntityTypeId(), $entity->label(), $entity->id()));
  }

  /**
   * {@inheritdoc}
   */
  abstract public function randomString($length = 8);

  /**
   * {@inheritdoc}
   */
  abstract protected function randomMachineName($length = 8);

}
