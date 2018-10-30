<?php

namespace Drupal\webform_views\Plugin\views\filter;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\views\Plugin\views\filter\StringFilter;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Filter for webform category of a webform submission.
 *
 * @ViewsFilter("webform_views_webform_category")
 */
class WebformSubmissionWebformCategory extends StringFilter {

  /**
   * Entity type manager service.
   *
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * WebformSubmissionWebformCategory constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function operators() {
    $operators = parent::operators();

    unset($operators['word']);
    unset($operators['allwords']);
    unset($operators['not_starts']);
    unset($operators['not_ends']);
    unset($operators['not']);
    unset($operators['shorterthan']);
    unset($operators['longerthan']);
    unset($operators['regular_expression']);

    $operators['=']['webform_operator'] = '=';
    $operators['!=']['webform_operator'] = '<>';
    $operators['contains']['webform_operator'] = 'CONTAINS';
    $operators['starts']['webform_operator'] = 'STARTS_WITH';
    $operators['ends']['webform_operator'] = 'ENDS_WITH';

    return $operators;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $webform_ids = $this->getApplicableWebformIds();
    if (empty($webform_ids)) {
      // Since no webforms were matched. Put a condition that yields FALSE.
      $this->query->addWhereExpression($this->options['group'], '1 = 0');
    }
    else {
      $this->ensureMyTable();
      $this->query->addWhere($this->options['group'], "$this->tableAlias.$this->realField", $webform_ids, 'IN');
    }

  }

  /**
   * Get a list of webform IDs that satisfy filter criterion.
   *
   * @return string[]
   *   Array of webform IDs that satisfy filter criterion.
   */
  protected function getApplicableWebformIds() {
    $operator = $this->operators()[$this->operator];

    $query = $this->entityTypeManager->getStorage('webform')->getQuery();
    $query->condition('category', $this->value, $operator['webform_operator']);
    return array_values($query->execute());
  }

}
