<?php

namespace Drupal\domain_site_settings\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Lists all domains for which we can edit the settings.
 *
 * @package Drupal\domain_site_settings\Controller
 */
class DomainSiteSettingsController extends ControllerBase {

  /**
   * Function Provide the list of modules.
   *
   * @return array
   *   Domain list.
   */
  public function domainList() {
    $domains = $this->entityTypeManager()->getStorage('domain')->loadMultiple();
    $rows = [];
    /** @var \Drupal\domain\DomainInterface $domain */
    foreach ($domains as $domain) {
      $row = [
        $domain->label(),
        $domain->getCanonical(),
        Link::fromTextAndUrl($this->t('Edit'), Url::fromRoute('domain_site_settings.config_form', ['domain' => $domain->id()])),
      ];
      $rows[] = $row;
    }
    // Build a render array which will be themed as a table.
    $build['pager_example'] = [
      '#rows' => $rows,
      '#header' => [
        $this->t('Name'),
        $this->t('Hostname'),
        $this->t('Edit Settings'),
      ],
      '#type' => 'table',
      '#empty' => $this->t('No domain record found.'),
    ];
    return $build;
  }

}
