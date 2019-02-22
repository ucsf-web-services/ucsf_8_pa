<?php

namespace Drupal\ucsf_search\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
/**
 * Provides a 'Search' block.
 *
 * @Block(
 *   id = "ucsf_search_block",
 *   admin_label = @Translation("UCSF Search Block"),
 *   category = @Translation("UCSF")
 * )
 */

class ucsf_search_block extends BlockBase {


  public function build() {



    //converting all this into a twig file is hard!
    $markup = <<<MARKUP
        <div class="form-search-block-div">
          <div class="search-box-container">
            <gcse:searchbox enableAutoComplete="true"></gcse:searchbox>
          </div>
          <div id="cse">
            <gcse:searchresults queryParameterName="gcsearch"></gcse:searchresults>
          </div>
        </div>
MARKUP;


   return array(
     '#type' => 'inline_template',  //this avoids having Drupal strip tags out, like GCSE tags.
     '#template' => $markup,
     '#attached' => array(
       'library' => array(
         'ucsf_search/ucsf_search'
       ),
     )
   );




  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }


}


