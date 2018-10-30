<?php

namespace Drupal\acquia_contenthub\Form;

use Drupal\acquia_contenthub\EntityManager;
use Drupal\Core\Entity\ContentEntityType;
use Drupal\Core\Entity\EntityDisplayRepository;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\user\PermissionHandlerInterface;
use Drupal\user\RoleStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the form to configure the entity types and bundles to be exported.
 */
class EntityConfigSettingsForm extends ConfigFormBase {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfoManager;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepository
   */
  protected $entityDisplayRepository;

  /**
   * The content hub entity manager.
   *
   * @var \Drupal\acquia_contenthub\EntityManager
   */
  protected $entityManager;

  /**
   * The role storage.
   *
   * @var \Drupal\user\RoleStorageInterface
   */
  protected $roleStorage;

  /**
   * The permission handler.
   *
   * @var \Drupal\user\PermissionHandlerInterface
   */
  protected $permissionHandler;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'acquia_contenthub.entity_config';
  }

  /**
   * Constructs an IndexAddFieldsForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity manager.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info_manager
   *   The entity bundle info interface.
   * @param \Drupal\Core\Entity\EntityDisplayRepository $entity_display_repository
   *   The entity display repository.
   * @param \Drupal\acquia_contenthub\EntityManager $entity_manager
   *   The entity manager for Content Hub.
   * @param \Drupal\user\RoleStorageInterface $role_storage
   *   The role storage.
   * @param \Drupal\user\PermissionHandlerInterface $permission_handler
   *   The permission handler.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info_manager, EntityDisplayRepository $entity_display_repository, EntityManager $entity_manager, RoleStorageInterface $role_storage, PermissionHandlerInterface $permission_handler) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityTypeBundleInfoManager = $entity_type_bundle_info_manager;
    $this->entityDisplayRepository = $entity_display_repository;
    $this->entityManager = $entity_manager;
    $this->roleStorage = $role_storage;
    $this->permissionHandler = $permission_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $entity_type_bundle_info_manager = $container->get('entity_type.bundle.info');
    $entity_type_manager = $container->get('entity_type.manager');
    $entity_display_repository = $container->get('entity_display.repository');
    $entity_manager = $container->get('acquia_contenthub.entity_manager');
    $role_storage = $container->get('entity.manager')->getStorage('user_role');
    $permission_handler = $container->get('user.permissions');
    return new static($entity_type_manager, $entity_type_bundle_info_manager, $entity_display_repository, $entity_manager, $role_storage, $permission_handler);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['acquia_contenthub.entity_config'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['description'] = [
      '#type' => 'item',
      '#description' => $this->t('Select the bundles of the entity types you would like to publish to Acquia Content Hub. <br/><br/><strong>Optional</strong><br/>Choose a view mode for each of the selected bundles to be rendered before sending to Acquia Content Hub. <br/>You can choose the view modes to use for rendering the items of different datasources and bundles. We recommend using a dedicated view mode to make sure that only relevant data (especially no field labels) will be transferred to Content Hub.'),
    ];

    $form['entity_config']['entities'] = $this->buildEntitiesForm();
    $form['entity_config']['user_role'] = $this->buildUserRoleForm();
    $form['entity_config']['user_role_warning'] = $this->buildUserRoleWarningForm();

    return parent::buildForm($form, $form_state);
  }

  /**
   * Build entities form.
   *
   * @return array
   *   Entities form.
   */
  private function buildEntitiesForm() {
    $form = [
      '#type' => 'fieldgroup',
      '#title' => $this->t('Entities'),
      '#tree' => TRUE,
    ];
    $entity_types = $this->entityManager->getAllowedEntityTypes();
    foreach ($entity_types as $type => $bundle) {
      $form[$type] = [
        '#title' => $type,
        '#type' => 'details',
        '#tree' => TRUE,
        '#description' => $this->t('Select the content types that you would like to publish to Content Hub.'),
        '#open' => TRUE,
      ];
      $form[$type] += $this->buildEntitiesBundleForm($type, $bundle);
    }
    return $form;
  }

  /**
   * Build entities bundle form.
   *
   * @param string $type
   *   Entity Type.
   * @param array $bundle
   *   Entity Bundle.
   *
   * @return array
   *   Entities bundle form.
   */
  private function buildEntitiesBundleForm($type, array $bundle) {
    /** @var \Drupal\acquia_contenthub\Entity\ContentHubEntityTypeConfig $contenthub_entity_config_id */
    $contenthub_entity_config_id = $this->entityManager->getContentHubEntityTypeConfigurationEntity($type);

    // Building the form.
    $form = [];
    foreach ($bundle as $bundle_id => $bundle_name) {
      $view_modes = $this->entityDisplayRepository->getViewModeOptionsByBundle($type, $bundle_id);

      $entity_type_label = $this->entityTypeManager->getDefinition($type)->getLabel();
      $form[$bundle_id] = [
        '#type' => 'fieldset',
        '#title' => $this->t('%entity_type_label » %bundle_name', ['%entity_type_label' => $entity_type_label, '%bundle_name' => $bundle_name]),
        '#collapsible' => TRUE,
      ];
      $enable_viewmodes = FALSE;
      $enable_index = FALSE;
      $rendering = [];

      if ($contenthub_entity_config_id) {
        $enable_viewmodes = $contenthub_entity_config_id->isEnabledViewModes($bundle_id);
        $enable_index = $contenthub_entity_config_id->isEnableIndex($bundle_id);
        $rendering = $contenthub_entity_config_id->getRenderingViewModes($bundle_id);
      }

      $form[$bundle_id]['enable_index'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Publish'),
        '#default_value' => $enable_index,
        '#description' => $this->t("Enable if you want to index this content into Content Hub."),
      ];

      // Preview image is currently only allow for 'node' type.
      if ($type === 'node') {
        $preview_image_link = $this->getContentTypePreviewImageLink($bundle_id);
        $form[$bundle_id]['enable_index']['#description'] .= ' ' . $this->t("Optionally, you can also configure the content's @preview_image_link.", ['@preview_image_link' => $preview_image_link]);
      }

      $form[$bundle_id]['enable_viewmodes'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Publish View modes'),
        '#disabled' => empty($view_modes),
        '#default_value' => empty($view_modes) ? FALSE : $enable_viewmodes,
        '#description' => empty($view_modes) ? $this->t('It is disabled because there are no available view modes. Please enable at least one.') : NULL,
        '#states' => [
          // Only show this field when the 'enable_index' checkbox is enabled.
          'visible' => [
            ':input[name="entities[' . $type . '][' . $bundle_id . '][enable_index]"]' => ['checked' => TRUE],
          ],
        ],
      ];

      $title = empty($view_modes) ? NULL : $this->t('Do you want to include the result of any of the following view mode(s)?');
      $default_value = (empty($view_modes) || empty($rendering)) ? [] : $rendering;
      $first_element = [
        key($view_modes) => key($view_modes),
      ];
      $form[$bundle_id]['rendering'] = [
        '#type' => 'select',
        '#options' => $view_modes,
        '#multiple' => TRUE,
        '#title' => $title,
        '#default_value' => empty($default_value) ? $first_element : $default_value,
        '#states' => [
          'visible' => [
            ':input[name="entities[' . $type . '][' . $bundle_id . '][enable_index]"]' => ['checked' => TRUE],
            ':input[name="entities[' . $type . '][' . $bundle_id . '][enable_viewmodes]"]' => ['checked' => TRUE],
          ],
        ],
        '#description' => $this->t('You can hold ctrl (or cmd) key to select multiple view mode(s). Including any of these view modes is usually done in combination with Acquia Lift. Please read the documentation for more information.'),
      ];
    }
    return $form;
  }

  /**
   * Get Content Type preview image link.
   *
   * @param string $bundle_id
   *   Bundle Id.
   *
   * @return \Drupal\Core\GeneratedLink
   *   Link object to the node page and its preview image tab.
   */
  private function getContentTypePreviewImageLink($bundle_id) {
    $link_text = $this->t('preview image');
    $link_attributes = ['attributes' => ['target' => '_blank'], 'fragment' => 'edit-acquia-contenthub'];
    $url = Url::fromRoute('entity.node_type.edit_form', ['node_type' => $bundle_id], $link_attributes);
    $link = Link::fromTextAndUrl($link_text, $url)->toString();
    return $link;
  }

  /**
   * Build user role form.
   *
   * @return array
   *   User role form.
   */
  private function buildUserRoleForm() {
    $user_role = $this->config('acquia_contenthub.entity_config')->get('user_role');
    $user_role_names = user_role_names();
    $form = [
      '#type' => 'select',
      '#title' => $this->t('User Role'),
      '#description' => $this->t('Your item will be rendered as seen by a user with the selected role. We recommend to just use "@anonymous" here to prevent data leaking out to unauthorized roles.', ['@anonymous' => $user_role_names[AccountInterface::ANONYMOUS_ROLE]]),
      '#options' => $user_role_names,
      '#multiple' => FALSE,
      '#default_value' => $user_role ?: AccountInterface::ANONYMOUS_ROLE,
      '#required' => TRUE,
    ];
    return $form;
  }

  /**
   * Build user role form warning.
   *
   * @return array
   *   User role warning form.
   */
  private function buildUserRoleWarningForm() {
    $admin_roles = $this->getRestrictedRoles();
    $form = [];
    if (!empty($admin_roles)) {
      $states = [];
      foreach ($admin_roles as $role_id => $role_label) {
        $states[] = [':input[name="user_role"]' => ['value' => $role_id]];
      }
      $form = [
        'container' => [
          '#type' => 'container',
          '#states' => [
            'visible' => $states,
          ],
          'warning' => [
            '#type' => 'inline_template',
            '#template' => '<div class="permission"><span class="title">{{ title }}</span></div>',
            '#context' => [
              'title' => $this->t('Warning: This role has security implications.'),
            ],
          ],
          '#attributes' => [
            'id' => 'user-warning-role',
          ],
        ],
      ];
    }

    return $form;
  }

  /**
   * Gets the roles to display in this form.
   *
   * @return \Drupal\user\RoleInterface[]
   *   An array of role objects.
   */
  private function getRoles() {
    return $this->roleStorage->loadMultiple();
  }

  /**
   * Get roles with security implications.
   *
   * @return array
   *   An array of admin roles and roles with restrict access flag set for any
   *   of role permission.
   */
  private function getRestrictedRoles() {
    $admin_roles = [];
    $permissions = $this->permissionHandler->getPermissions();

    foreach ($this->getRoles() as $role_name => $role) {
      if ($role->isAdmin()) {
        $admin_roles[$role_name] = $role->label();
      }
      else {
        foreach ($role->getPermissions() as $permission_name) {
          if (!empty($permissions[$permission_name]['restrict access'])) {
            $admin_roles[$role_name] = $role->label();
            continue;
          }
        }
      }
    }

    return $admin_roles;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    /** @var \Drupal\rest\RestResourceConfigInterface $contenthub_entity_config_storage */
    $contenthub_entity_config_storage = $this->entityTypeManager->getStorage('acquia_contenthub_entity_config');
    /** @var \Drupal\acquia_contenthub\Entity\ContentHubEntityTypeConfig[] $contenthub_entity_config_ids */
    $contenthub_entity_config_ids = $contenthub_entity_config_storage->loadMultiple();

    $values = $form_state->getValues();
    foreach ($values['entities'] as $entity_type => $bundles) {

      $contenthub_entity_config_ids_bundles = [];
      if (isset($contenthub_entity_config_ids[$entity_type])) {
        $contenthub_entity_config_ids_bundles = $contenthub_entity_config_ids[$entity_type]->getBundles();
      }
      // Checkboxes come with integer values. Convert them to boolean.
      foreach ($bundles as $name => $fields) {
        $bundles[$name]['enable_index'] = (bool) $bundles[$name]['enable_index'];
        $bundles[$name]['enable_viewmodes'] = $bundles[$name]['enable_index'] ? (bool) $bundles[$name]['enable_viewmodes'] : FALSE;

        if (!empty($contenthub_entity_config_ids_bundles)) {
          if (isset($contenthub_entity_config_ids_bundles[$name]['preview_image_field'])) {
            $bundles[$name]['preview_image_field'] = $contenthub_entity_config_ids_bundles[$name]['preview_image_field'];
          }

          if (isset($contenthub_entity_config_ids_bundles[$name]['preview_image_style'])) {
            $bundles[$name]['preview_image_style'] = $contenthub_entity_config_ids_bundles[$name]['preview_image_style'];
          }
        }

      }

      if (!isset($contenthub_entity_config_ids[$entity_type])) {
        // If we do not have this configuration entity, then create it.
        $data = [
          'id' => $entity_type,
          'bundles' => $bundles,
        ];
        $contenthub_entity_config = $contenthub_entity_config_storage->create($data);
        $contenthub_entity_config->save();
      }
      else {
        // Update Configuration entity.
        $contenthub_entity_config_ids[$entity_type]->setBundles($bundles);
        $contenthub_entity_config_ids[$entity_type]->save();
      }
    }

    $config = $this->config('acquia_contenthub.entity_config');
    $config->set('user_role', $values['user_role']);
    $config->save();
  }

  /**
   * Obtains the list of entity types.
   */
  public function getEntityTypes() {
    $types = $this->entityTypeManager->getDefinitions();

    $entity_types = [];
    foreach ($types as $type => $entity) {
      // We only support content entity types at the moment, since config
      // entities don't implement \Drupal\Core\TypedData\ComplexDataInterface.
      if ($entity instanceof ContentEntityType) {
        $bundles = $this->entityTypeBundleInfoManager->getBundleInfo($type);

        // Here we need to load all the different bundles?
        if (isset($bundles) && count($bundles) > 0) {
          foreach ($bundles as $key => $bundle) {
            $entity_types[$type][$key] = $bundle['label'];
          }
        }
        else {
          // In cases where there are no bundles, but the entity can be
          // selected.
          $entity_types[$type][$type] = $entity->getLabel();
        }
      }
    }
    return $entity_types;
  }

}
