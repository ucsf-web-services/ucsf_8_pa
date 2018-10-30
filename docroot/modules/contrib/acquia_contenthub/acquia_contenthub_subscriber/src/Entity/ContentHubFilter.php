<?php

namespace Drupal\acquia_contenthub_subscriber\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\acquia_contenthub_subscriber\ContentHubFilterInterface;
use Drupal\user\Entity\User;
use DateTime;

/**
 * Defines the ContentHubFilter entity.
 *
 * @ConfigEntityType(
 *   id = "contenthub_filter",
 *   label = @Translation("ContentHubFilter"),
 *   handlers = {
 *     "list_builder" = "Drupal\acquia_contenthub_subscriber\Controller\ContentHubFilterListBuilder",
 *     "form" = {
 *       "add" = "Drupal\acquia_contenthub_subscriber\Form\ContentHubFilterForm",
 *       "edit" = "Drupal\acquia_contenthub_subscriber\Form\ContentHubFilterForm",
 *       "delete" = "Drupal\acquia_contenthub_subscriber\Form\ContentHubFilterDeleteForm",
 *     }
 *   },
 *   config_prefix = "contenthub_filter",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *   },
 *   links = {
 *     "edit-form" = "/admin/config/system/contenthub_filter/{contenthub_filter}",
 *     "delete-form" = "/admin/config/system/contenthub_filter/{contenthub_filter}/delete",
 *   }
 * )
 */
class ContentHubFilter extends ConfigEntityBase implements ContentHubFilterInterface {

  /**
   * The ContentHubFilter ID.
   *
   * @var string
   */
  public $id;

  /**
   * The ContentHubFilter label.
   *
   * @var string
   */
  public $name;

  /**
   * The Publish setting.
   *
   * @var string
   */
  public $publish_setting;

  /**
   * The Search term.
   *
   * @var string
   */
  public $search_term;

  /**
   * The From Date.
   *
   * @var string
   */
  public $from_date;

  /**
   * The To Date.
   *
   * @var string
   */
  public $to_date;

  /**
   * The Source.
   *
   * @var string
   */
  public $source;

  /**
   * The Tags.
   *
   * @var string
   */
  public $tags;

  /**
   * Entity Types.
   *
   * @var string[]
   */
  public $entity_types;

  /**
   * The Entity Bundles.
   *
   * @var string[]
   */
  public $bundles;

  /**
   * The Author or the user UID who created the filter.
   *
   * @var int
   */
  public $author;

  /**
   * Returns the human-readable publish_setting.
   *
   * @return string
   *   The human-readable publish_setting.
   */
  public function getPublishSetting() {
    $setting = [
      'none' => t('None'),
      'import' => t('Always Import'),
      'publish' => t('Always Publish'),
    ];
    return $setting[$this->publish_setting];
  }

  /**
   * Returns the Publish status for this particular filter.
   *
   * This is the status flag to be saved on node entities.
   *
   * @return int|bool
   *   0 if Unpublished status, 1 for Publish status, FALSE otherwise.
   */
  public function getPublishStatus() {
    $status = [
      'none' => FALSE,
      'import' => 0,
      'publish' => 1,
    ];
    return $status[$this->publish_setting];
  }

  /**
   * Returns the list of entity types.
   *
   * @return \string[]
   *   A list of entity types.
   */
  public function getEntityTypes() {
    return is_array($this->entity_types) ? $this->entity_types : [];
  }

  /**
   * Returns the list of bundles.
   *
   * @return \string[]
   *   An array of bundles.
   */
  public function getBundles() {
    return is_array($this->bundles) ? $this->bundles : [];
  }

  /**
   * Returns the Author name (User account name).
   *
   * @return string
   *   The user account name.
   */
  public function getAuthor() {
    $user = User::load($this->author);
    return $user->getAccountName();
  }

  /**
   * Gets the Filter Conditions to match in a webhook asset.
   *
   * @return array
   *   An array of filter conditions.
   */
  public function getConditions() {
    $conditions = [];

    // Search Term.
    if (!empty($this->search_term)) {
      $conditions[] = 'search_term:' . $this->search_term;
    }

    // Building entity type condition.
    if (!empty($this->entity_types)) {
      $conditions[] = 'entity_types:' . implode(',', $this->entity_types);
    }

    // Building bundle condition.
    if (!empty($this->bundles)) {
      $conditions[] = 'bundle:' . implode(',', $this->bundles);
    }

    // Building tags condition.
    if (!empty($this->tags)) {
      $conditions[] = 'tags:' . $this->tags;
    }

    // Building origin condition.
    if (!empty($this->source)) {
      $conditions[] = 'origins:' . $this->source;
    }

    // <Date From>to<Date-To>.
    if (!empty($this->from_date) || !empty($this->to_date)) {
      $conditions[] = 'modified:' . $this->from_date . 'to' . $this->to_date;
    }

    return $conditions;
  }

  /**
   * Formats the 'Entity Types' and 'Bundles' properties.
   */
  public function formatEntityTypesAndBundles() {
    $entity_types = $this->entity_types;
    $bundles = $this->bundles;
    if (!is_array($entity_types)) {
      $this->entity_types = array_filter(array_map('trim', explode(PHP_EOL, $this->entity_types)));
    }
    if (!is_array($bundles)) {
      $this->bundles = array_filter(array_map('trim', explode(PHP_EOL, $bundles)));
    }
  }

  /**
   * Change Date format from "m-d-Y" to "Y-m-d".
   */
  public function changeDateFormatMonthDayYear2YearMonthDay() {
    if (!empty($this->from_date)) {
      if ($from_date = DateTime::createFromFormat('m-d-Y', $this->from_date)) {
        $this->from_date = $from_date->format('Y-m-d');
      }
    }
    if (!empty($this->to_date)) {
      if ($to_date = DateTime::createFromFormat('m-d-Y', $this->to_date)) {
        $this->to_date = $to_date->format('Y-m-d');
      }
    }
    return $this;
  }

  /**
   * Change Date format from "Y-m-d" to "m-d-Y".
   */
  public function changeDateFormatYearMonthDay2MonthDayYear() {
    if (!empty($this->from_date)) {
      if ($from_date = DateTime::createFromFormat('Y-m-d', $this->from_date)) {
        $this->from_date = $from_date->format('m-d-Y');
      }
    }
    if (!empty($this->to_date)) {
      if ($to_date = DateTime::createFromFormat('Y-m-d', $this->to_date)) {
        $this->to_date = $to_date->format('m-d-Y');
      }
    }
    return $this;
  }

  /**
   * Update values of the original entity to the one submitted by REST.
   *
   * @param \Drupal\acquia_contenthub_subscriber\ContentHubFilterInterface $contenthub_filter_original
   *   The original content hub filter.
   *
   * @return \Drupal\acquia_contenthub_subscriber\ContentHubFilterInterface
   *   The updated content hub filter.
   */
  public function updateValues(ContentHubFilterInterface $contenthub_filter_original) {
    // The following are the only fields that we allow to change through PATCH.
    $replaceable_fields = [
      'name',
      'publish_setting',
      'search_term',
      'from_date',
      'to_date',
      'source',
      'entity_types',
      'bundles',
      'tags',
    ];

    foreach ($this->_restSubmittedFields as $field) {
      if (in_array($field, $replaceable_fields)) {
        $contenthub_filter_original->{$field} = $this->{$field};
      }
    }
    return $contenthub_filter_original;
  }

}
