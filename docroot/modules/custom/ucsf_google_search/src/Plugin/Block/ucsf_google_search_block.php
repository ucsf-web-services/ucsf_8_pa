<?php

namespace Drupal\ucsf_google_search\Plugin\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
/**
 * Provides a 'Search' block.
 *
 * @Block(
 *   id = "ucsf_google_search_block",
 *   admin_label = @Translation("UCSF Google Search Block"),
 *   category = @Translation("UCSF")
 * )
 */

class ucsf_google_search_block extends BlockBase {


  public function build() {

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
          'ucsf_google_search/ucsf_google_search'
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

  public function oldCode() {
      /**
       * Google Custom Search page
       */
      $code = '006142056215064927360:qqvc-py3r80'; //live code
      $refinement='';
      if(isset($_GET['search'])) {
        $search = preg_replace("/\r\n|\r|\n/", ' ', $_GET['search']);
        $search = filter_xss(htmlspecialchars($search, ENT_QUOTES));
      }

      $js = <<<EOT
        (function() {
          var cx = '{$code}';
          var gcse = document.createElement('script');
          gcse.type = 'text/javascript';
          gcse.async = true;
          gcse.src = 'https://cse.google.com/cse.js?cx=' + cx;
          var s = document.getElementsByTagName('script')[0];
          s.parentNode.insertBefore(gcse, s);
          })();
EOT;
        //drupal_add_js($js, 'inline');
        $content =<<<EOL
        <div class="form-search-block">
          <form action="{$base_path}search/site" method="get" id="form-search-block">
              <div class="search-box-container">
                <gcse:searchbox></gcse:searchbox>
              </div>
          </form>
          <div id="cse">
            <gcse:searchresults-only queryParameterName="search" {$refinement}></gcse:searchresults-only>
          </div>
        </div>
EOL;

      return $content;
    }

}


