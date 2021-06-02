<?php

namespace Drupal\domain_theme_switch\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DomainThemeSwitchConfigForm.
 *
 * @package Drupal\domain_theme_switch\Form
 */
class DomainThemeSwitchConfigForm extends ConfigFormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * Construct function.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory load.
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManager $entity_type_manager, ThemeHandlerInterface $theme_handler
  ) {
    parent::__construct($config_factory);
    $this->entityTypeManager = $entity_type_manager;
    $this->themeHandler = $theme_handler;
  }

  /**
   * Create function return static domain loader configuration.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   Load the ContainerInterface.
   *
   * @return static
   *   return domain loader configuration.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'), $container->get('entity_type.manager'), $container->get('theme_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'domain_theme_switch.settings',
    ];
  }

  /**
   * Form ID is domain_theme_switch_config_form.
   *
   * @return string
   *   Return form ID.
   */
  public function getFormId() {
    return 'domain_theme_switch_config_form';
  }

  /**
   * Function to get the list of installed themes.
   *
   * @return array
   *   The complete theme registry data array.
   */
  public function getThemeList() {
    $themeName = [];
    foreach ($this->themeHandler->listInfo() as $key => $value){
      $themeName[$key] = $value->info['name'];
    }
    return $themeName;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('domain_theme_switch.settings');
    $defaultSiteTheme = $this->config('system.theme')->get('default');
    $defaultAdminTheme = $this->config('system.theme')->get('admin');

    $themeNames = $this->getThemeList();
    $domains = $this->entityTypeManager->getStorage('domain')->loadMultiple();
    foreach ($domains as $domain) {
      $domainId = $domain->id();
      $hostname = $domain->get('name');
      $form[$domainId] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Select Theme for "@domain"', ['@domain' => $hostname]),
      ];
      $form[$domainId][$domainId . '_site'] = [
        '#title' => $this->t('Site theme for domain'),
        '#type' => 'select',
        '#options' => $themeNames,
        '#default_value' => (NULL !== $config->get($domainId . '_site')) ? $config->get($domainId . '_site') : $defaultSiteTheme,
      ];
      $form[$domainId][$domainId . '_admin'] = [
        '#title' => $this->t('Admin theme for domain'),
        '#suffix' => $this->t('Change permission to allow domain admin theme @link.', [
          '@link' => Link::fromTextAndUrl($this->t('change permission'),
              Url::fromRoute('user.admin_permissions', [], ['fragment' => 'module-domain_theme_switch']))->toString(),
        ]),
        '#type' => 'select',
        '#options' => $themeNames,
        '#default_value' => (NULL !== $config->get($domainId . '_admin')) ? $config->get($domainId . '_admin') : $defaultAdminTheme,
      ];
    }
    if (count($domains) === 0) {
      $form['domain_theme_switch_message'] = [
        '#markup' => $this->t('Zero domain records found. Please @link to create the domain.', [
          '@link' => Link::fromTextAndUrl($this->t('click here'), Url::fromRoute('domain.admin'))->toString(),
        ]),
      ];
      return $form;
    }
    return parent::buildForm($form, $form_state);
  }

  /**
   * Validate function for the form.
   *
   * @param array $form
   *   Form items.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Formstate for validate.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $domains = $this->entityTypeManager->getStorage('domain')->loadMultiple();
    $config = $this->config('domain_theme_switch.settings');
    foreach ($domains as $domain) {
      $domainId = $domain->id();
      $config->set($domainId . '_site', $form_state->getValue($domainId . '_site'));
      $config->set($domainId . '_admin', $form_state->getValue($domainId . '_admin'));
    }
    $config->save();
  }

}
