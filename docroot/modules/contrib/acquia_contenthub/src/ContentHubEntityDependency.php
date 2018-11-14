<?php

namespace Drupal\acquia_contenthub;

use Acquia\ContentHubClient\Entity;
use Drupal\Component\Uuid\Uuid;
use Acquia\ContentHubClient\Attribute;

/**
 * Content Hub Dependency Class.
 */
class ContentHubEntityDependency {

  /**
   * Parent is required for dependent to exist.
   *
   * @var int.
   */
  const RELATIONSHIP_DEPENDENT = 1;

  /**
   * Dependent is independent of parent.
   *
   * @var int.
   */
  const RELATIONSHIP_INDEPENDENT = 2;

  /**
   * The parent ContentHubEntity.
   *
   * @var \Drupal\acquia_contenthub\ContentHubEntityDependency
   */
  protected $parent;

  /**
   * The CDF Entity.
   *
   * @var \Acquia\ContentHubClient\Entity
   */
  protected $cdf;

  /**
   * A tracker of all dependencies.
   *
   * @var array
   */
  protected $dependencyChain = [];

  /**
   * The relationship type between parent and dependent.
   *
   * @var int
   */
  protected $dependencyType;

  /**
   * Public constructor.
   *
   * @param \Acquia\ContentHubClient\Entity $cdf
   *   The Entity CDF.
   *
   * @throws \Exception
   */
  public function __construct(Entity $cdf) {
    $this->cdf = $cdf;
    if (in_array($this->cdf->getType(), self::getPostDependencyEntityTypes())) {
      $this->setRelationship(self::RELATIONSHIP_DEPENDENT);
    }
    else {
      $this->setRelationship(self::RELATIONSHIP_INDEPENDENT);
    }
  }

  /**
   * Obtains the Entity's UUID.
   *
   * @return string
   *   The UUID.
   */
  public function getUuid() {
    return $this->cdf->getUuid();
  }

  /**
   * Gets the list of "Entity-dependent" entity types.
   */
  public static function getPostDependencyEntityTypes() {
    // By default "field collections" and "paragraphs" are post-dependencies.
    $post_dependencies = [
      'field_collection_item' => 'field_collection_item',
      'paragraph' => 'paragraph',
    ];

    return $post_dependencies;
  }

  /**
   * Checks whether the current entity is dependent on another one or not.
   *
   * If it is entity dependent, then it needs a parent or host entity.
   *
   * @return bool
   *   TRUE if it is Entity Dependent, FALSE otherwise.
   */
  public function isEntityDependent() {
    return in_array($this->cdf->getType(), $this->getPostDependencyEntityTypes());
  }

  /**
   * Tracks dependencies as a flat chain to combat dependency loops.
   *
   * @param \Drupal\acquia_contenthub\ContentHubEntityDependency $content_hub_entity
   *   An entity to add to the chain.
   *
   * @return $this
   *   The position of the entity in the chain or FALSE.
   */
  public function appendDependencyChain(ContentHubEntityDependency $content_hub_entity) {
    if (!in_array($content_hub_entity->getUuid(), $this->dependencyChain)) {
      $this->dependencyChain[] = $content_hub_entity->getUuid();
    }
    return $this;
  }

  /**
   * Identifies if a dependency exists in the chain.
   *
   * @param \Drupal\acquia_contenthub\ContentHubEntityDependency $content_hub_entity
   *   An entity to check against the chain.
   *
   * @return bool
   *   TRUE if the entity is in the chain, otherwise false.
   */
  public function isInDependencyChain(ContentHubEntityDependency $content_hub_entity) {
    return in_array($content_hub_entity->getUuid(), $this->getDependencyChain());
  }

  /**
   * Returns the dependency chain for the current entity.
   *
   * @return array
   *   The dependency chain.
   */
  public function getDependencyChain() {
    return $this->dependencyChain;
  }

  /**
   * Sets the relationship flag.
   *
   * @param int $type
   *   The Relationship type.
   *
   * @return \Drupal\acquia_contenthub\ContentHubEntityDependency
   *   This object.
   *
   * @throws \Exception
   */
  public function setRelationship($type = self::RELATIONSHIP_INDEPENDENT) {
    switch ($type) {
      case self::RELATIONSHIP_INDEPENDENT:
      case self::RELATIONSHIP_DEPENDENT:
        $this->dependencyType = $type;
        break;

      default:
        throw new \Exception("Unknown relationship: $type.");
    }
    return $this;
  }

  /**
   * Obtains the relationship flag.
   */
  public function getRelationship() {
    return $this->dependencyType;
  }

  /**
   * Sets the parent of the dependency.
   *
   * @param \Drupal\acquia_contenthub\ContentHubEntityDependency $parent
   *   The parent ContentHubEntity.
   *
   * @return $this
   *   This Content Hub Entity.
   */
  public function setParent(ContentHubEntityDependency $parent) {
    $this->parent = $parent;
    $this->parent->appendDependencyChain($this);
    return $this;
  }

  /**
   * Returns the Parent Entity.
   *
   * @return \Drupal\acquia_contenthub\ContentHubEntityDependency
   *   The ContentHubEntity parent object.
   */
  public function getParent() {
    return $this->parent;
  }

  /**
   * Obtains a Raw Remote Content Hub Entity.
   *
   * @return \Acquia\ContentHubClient\Entity|bool
   *   Returns a ContentHubClient\Entity, FALSE otherwise.
   */
  public function getRawEntity() {
    return !empty($this->cdf) ? $this->cdf : FALSE;
  }

  /**
   * Returns the Content Hub Entity Type.
   *
   * @return bool|string
   *   The Entity Type or FALSE.
   */
  public function getEntityType() {
    return !empty($this->cdf) ? $this->cdf->getType() : FALSE;
  }

  /**
   * Obtains remote dependencies for this particular entity.
   *
   * @return array
   *   An array of UUIDs
   */
  public function getRemoteDependencies() {
    $dependencies = [];
    // Finding assets (files) dependencies.
    foreach ($this->cdf->getAssets() as $asset) {
      preg_match('#\[(.*)\]#', $asset['replace-token'], $match);
      $uuid = $match[1];
      if (Uuid::isValid($uuid)) {
        // It is a valid UUID => Then it should refer to an entity.
        $dependencies[] = $uuid;
      }
    }

    // Adding this exclude some attributes, which we don't want to take into
    // consideration the dependency information contained on them.
    $excluded_attributes = $this->getExcludedAttributesFromDependencies();

    // Check if 'entity_embed' exists.
    $exists_entity_embed = \Drupal::moduleHandler()->moduleExists('entity_embed');

    // Finding attributes (entity references) dependencies.
    foreach ($this->cdf->getAttributes() as $name => $attribute) {
      if (!in_array($name, $excluded_attributes)) {
        $type = $attribute['type'];
        if ($type == Attribute::TYPE_REFERENCE) {
          // Obtaining values for every language.
          $languages = array_keys($attribute['value']);
          foreach ($languages as $lang) {
            $dependencies[] = $attribute['value'][$lang];
          }
        }
        elseif ($type == Attribute::TYPE_ARRAY_REFERENCE) {
          // Obtaining values for every language.
          $languages = array_keys($attribute['value']);
          foreach ($languages as $lang) {
            $dependencies = array_merge($dependencies, $attribute['value'][$lang]);
          }
        }
        elseif ($type == Attribute::TYPE_ARRAY_STRING) {
          // Obtaining values for every language.
          $languages = array_keys($attribute['value']);
          foreach ($languages as $lang) {
            if (!is_array($attribute['value'][$lang])) {
              continue;
            }

            // Process all text items.
            foreach ($attribute['value'][$lang] as $item) {
              $field = json_decode($item, TRUE);
              $value = isset($field['value']) ? $field['value'] : '';
              if ($exists_entity_embed && !empty($value)) {
                // Parse uuid from text.
                $entity_embed_handler = new ContentHubEntityEmbedHandler();
                $uuids = $entity_embed_handler->getReferencedUuids($value);
                $dependencies = array_merge($dependencies, $uuids);
              }

              // Check for the existence of Link Fields that might link to
              // other entities (which should be considered dependencies).
              $entity_info = ContentHubEntityLinkFieldHandler::load()->getDependentEntityInfo($field);
              if (!empty($entity_info)) {
                $uuid = $entity_info[1];
                $dependencies[] = $uuid;
              }
            }
          }
        }
      }
    }
    return array_unique($dependencies);
  }

  /**
   * Excludes attributes from providing dependency information.
   *
   * Provides a list of attributes in which we do not want to take into
   * consideration the dependency information contained on them.
   *
   * @return array
   *   The array of attributes to exclude.
   */
  protected function getExcludedAttributesFromDependencies() {

    // Set excludes for all entities.
    $excludes = ['author', 'comments'];

    // Do not exclude parent attribute for taxonomy_term.
    if ($this->getEntityType() != 'taxonomy_term') {
      $excludes[] = 'parent';
    }

    return $excludes;
  }

  /**
   * Sets the author for the current node entity, if $author is given.
   *
   * @param string|null $author
   *   The author's UUID if given.
   */
  public function setAuthor($author = NULL) {
    if ($this->getEntityType() == 'node' && Uuid::isValid($author)) {
      // Set the entity's author for node entities.
      if (isset($this->getRawEntity()['attributes']['author'])) {
        // Get the language.
        $languages = array_keys($this->cdf['attributes']['author']['value']);
        foreach ($languages as $lang) {
          $this->cdf['attributes']['author']['value'][$lang] = $author;
        }
      }
      else {
        // Set the author for each language.
        $lang_author = [];
        foreach ($this->cdf['attributes']['langcode']['value'] as $lang) {
          $lang_author[$lang] = $author;
        }
        $this->cdf['attributes']['author'] = [
          'type' => 'reference',
          'value' => $lang_author,
        ];
      }
    }
  }

  /**
   * Sets the status flag for a node entity, if given.
   *
   * @param int|null $status
   *   The Status flag for a node entity.
   */
  public function setStatus($status = NULL) {
    if ($this->getEntityType() == 'node' && isset($status)) {

      // Set the entity's status for node entities.
      if (isset($this->getRawEntity()['attributes']['status'])) {
        // Get the language.
        $languages = array_keys($this->cdf['attributes']['status']['value']);
        foreach ($languages as $lang) {
          $this->cdf['attributes']['status']['value'][$lang] = $status;
        }
      }
      else {
        // Set the status for each language.
        $lang_status = [];
        foreach ($this->cdf['attributes']['langcode']['value'] as $lang) {
          $lang_status[$lang] = $status;
        }
        $this->cdf['attributes']['status'] = [
          'type' => 'integer',
          'value' => $lang_status,
        ];
      }
    }
  }

}
