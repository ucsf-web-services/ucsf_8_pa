<?php

namespace Drupal\acquia_contenthub_diagnostic;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a base implementation that Content Hub Requirements will extend.
 */
abstract class ContentHubRequirementBase extends PluginBase implements ContentHubRequirementInterface, ContainerFactoryPluginInterface {

  /**
   * The current value.
   *
   * @var string|\Drupal\Core\StringTranslation\TranslatableMarkup
   */
  protected $value;

  /**
   * The description of the requirement/status.
   *
   * @var string|\Drupal\Core\StringTranslation\TranslatableMarkup
   */
  protected $description;

  /**
   * The requirement's result/severity level.
   *
   * @var int
   */
  protected $severity;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a new ContentHubRequirementBase.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ModuleHandlerInterface $module_handler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->moduleHandler = $module_handler;
    try {
      $this->severity = $this->verify();
    }
    catch (\Exception $e) {
      $this->setValue($this->t("An exception occurred."));
      $this->setDescription($this->t('Error: @error', ['@error' => $e->getMessage()]));
      $this->severity = REQUIREMENT_ERROR;
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var \Drupal\Core\Extension\ModuleHandlerInterface $module_handler */
    $module_handler = $container->get('module_handler');
    return new static($configuration, $plugin_id, $plugin_definition, $module_handler);
  }

  /**
   * Verifies the requirement.
   *
   * @return int
   *   The requirement status code:
   *     - REQUIREMENT_INFO: For info only.
   *     - REQUIREMENT_OK: The requirement is satisfied.
   *     - REQUIREMENT_WARNING: The requirement failed with a warning.
   *     - REQUIREMENT_ERROR: The requirement failed with an error.
   */
  abstract public function verify();

  /**
   * {@inheritdoc}
   */
  public function title() {
    return $this->pluginDefinition['title'];
  }

  /**
   * {@inheritdoc}
   */
  public function value() {
    return $this->value;
  }

  /**
   * {@inheritdoc}
   */
  public function description() {
    return $this->description;
  }

  /**
   * {@inheritdoc}
   */
  public function severity() {
    return $this->severity;
  }

  /**
   * Sets the current value.
   *
   * @param string|\Drupal\Core\StringTranslation\TranslatableMarkup $value
   *   The current value.
   */
  protected function setValue($value) {
    $this->value = $value;
  }

  /**
   * Sets the description of the requirement/status.
   *
   * @param string|\Drupal\Core\StringTranslation\TranslatableMarkup $description
   *   The description of the requirement/status.
   */
  protected function setDescription($description) {
    $this->description = $description;
  }

  /**
   * Get the domain to run the tests on.
   */
  protected function getDomain() {
    global $base_url;
    $domain = $_SERVER['SERVER_NAME'] ?: parse_url($base_url, PHP_URL_HOST);
    if (empty($domain)) {
      $error = "Your site's domain could not be determined.";
      if (PHP_SAPI === 'cli') {
        $error .= ' ' . 'Use a valid Drush site alias or visit the Status report in a browser.';
      }
      throw new \Exception($error);
    }
    return 'https://' . $domain;
  }

}
