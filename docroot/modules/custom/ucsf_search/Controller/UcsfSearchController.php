<?php
/**
 * Created by PhpStorm.
 * User: Eric Guerin
 * Date: 2/6/19
 * Time: 1:22 PM
 */
namespace Drupal\ucsf_search\Controller;
use Drupal\Core\Controller\ControllerBase;

class UcsfsearchController extends ControllerBase {
    public function content() {


      return [
        '#type' => 'markup',
        '#markup' => $this->t('Welcome to UCSF Search Container!')
      ];

    }
}