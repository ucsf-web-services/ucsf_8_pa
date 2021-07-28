<?php

namespace Drupal\amp\Asset;

use Drupal\Core\Asset\CssCollectionRenderer;
use Drupal\amp\Service\AMPService;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Renders CSS assets.
 *
 * This class retrieves all local css and renders it inline in the head of the
 * page. Neither style links nor @import are allowed in AMP, except for a few
 * whitelisted font providers.
 */
class AmpCssCollectionRenderer extends CssCollectionRenderer {

  /**
   * Whitelist of allowed external style links.
   *
   * @var array
   *
   * @see https://www.ampproject.org/docs/design/responsive/custom_fonts
   */
  protected $linkDomainWhitelist = [
    'cloud.typography.com',
    'fast.fonts.net',
    'fonts.googleapis.com',
    'use.typekit.net',
    'maxcdn.bootstrapcdn.com',
  ];

  /**
   * The base path used by rewriteFileURI().
   *
   * @var string
   */
  public $rewriteFileURIBasePath;

  /**
   * The inner service that we are decorating.
   *
   * @var \Drupal\Core\Asset\CssCollectionRenderer
   */
  protected $cssCollectionRenderer;

  /**
   * AMP Service.
   *
   * @var \Drupal\amp\Service\AMPService
   */
  protected $ampService;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a CssCollectionRenderer.
   *
   * @param \Drupal\Core\Asset\CssCollectionRenderer $cssCollectionRenderer
   *   The decorated CssCollectionRenderer.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state key/value store.
   * @param \Drupal\amp\Service\AMPService $ampService
   *   The AMP service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Configuration factory.
   */
  public function __construct(
  CssCollectionRenderer $cssCollectionRenderer,
  StateInterface $state,
  AMPService $ampService,
  RendererInterface $renderer,
  ConfigFactoryInterface $configFactory) {
    $this->cssCollectionRenderer = $cssCollectionRenderer;
    $this->state = $state;
    $this->ampService = $ampService;
    $this->renderer = $renderer;
    $this->configFactory = $configFactory;

    parent::__construct($state);
  }

  /**
   * {@inheritdoc}
   */
  public function render(array $css_assets) {

    // Retrieve the normal css render array.
    $elements = parent::render($css_assets);

    // Intervene only if this is an AMP page.
    if (!$this->ampService->isAmpRoute()) {
      return $elements;
    }

    // See if relative file rewrites are needed. They are needed only if the
    // css has not already been aggregated.
    $config = $this->configFactory->get('system.performance');
    $needs_rewrite = empty($config->get('css.preprocess'));

    // For tracking the size and contents of the inlined css:
    $size = 0;
    $files = [];

    foreach ($elements as $key => $element) {
      // This element contains @import values for the css files.
      if ($element['#tag'] == 'style' && array_key_exists('#value', $element)) {

        // Now split all the @imports into individual items so we can count,
        // load, and concatenate them.
        $urls = preg_match_all('/@import url\("(.+)\?/', $element['#value'], $matches);
        $all_css = [];
        foreach ($matches[1] as $url) {
          // Skip empty and missing files.
          if ($css = @file_get_contents(DRUPAL_ROOT . $url)) {
            if ($needs_rewrite) {
              $css = $this->doRewrite($url, $css);
            }
            $css = $this->strip($css);
            $size += strlen($css);
            $all_css[] = $css;
            $files[] = [$this->format(strlen($css)), $url];
          }
        }
        // Implode, wrap in @media, and minify results.
        $value = implode("", $all_css);
        $value = '@media ' . $element['#attributes']['media'] . " {\n" . $value . "\n}\n";
        $value = $this->minify($value);
        $value = $this->strip($value);

        $element['#value'] = $value;
        $elements[$key] = $element;
        $elements[$key]['#merged'] = TRUE;
      }
      // This element contains links to css files.
      elseif ($element['#tag'] == 'link' && array_key_exists('href', $element['#attributes'])) {
        $url = $element['#attributes']['href'];
        $provider = parse_url($url, PHP_URL_HOST);
        if (!empty($provider)) {
          // External files rendered as links only if they are on the whitelist.
          if (!in_array($provider, $this->linkDomainWhitelist)) {
            unset($elements[$key]);
          }
        }
        else {
          // Strip any querystring off the url.
          if (strpos($url, '?') !== FALSE) {
            list($url, $query) = explode('?', $url);
          }
          $css = file_get_contents(DRUPAL_ROOT . $url);
          if ($needs_rewrite) {
            $css = $this->doRewrite($url, $css);
          }
          $css = $this->strip($css);
          $size += strlen($css);
          $all_css[] = $css;
          $files[] = [$this->format(strlen($css)), $url];
          $element['#value'] = $css;
          $elements[$key] = $element;
          $elements[$key]['#merged'] = TRUE;
        }
      }
    }
    // Merge the inline results into a single style element with an
    // "amp-custom" attribute, using the amp_custom_style #type.
    $merged = '';
    $replacement_key = NULL;
    foreach ($elements as $key => $element) {
      if (isset($element['#merged'])) {
        $merged .= $element['#value'];
        unset($elements[$key]);
        // The first key found will become the new element's key.
        if (empty($replacement_key)) {
          $replacement_key = $key;
        }
      }
    }

    $elements[$replacement_key] = [
      '#tag' => 'style',
      '#type' => 'amp_custom_style',
      '#value' => $merged,
    ];

    // Display info about inline css if #development=1 is appended to url.
    if ($this->ampService->isDevPage()) {
      $title = 'CSS Filesize';
      $difference = ($size - 50000);
      $over = $difference > 0 ? t('so your css is :difference too big', [
        ':difference' => $this->format(abs($difference)),
      ]) : '';
      $under = $difference <= 0 ? t('so you have :difference to spare', [
        ':difference' => $this->format(abs($difference)),
      ]) : '';
      $output = t('The size of the css on this page is :size. The AMP limit is :limit, :overunder. The included css files and their sizes are listed for ease in finding large files to optimize. For the best information about individual file sizes, visit this page while optimization is turned off.', [
        ':size' => $this->format($size),
        ':limit' => $this->format(50000),
        ':overunder' => $over . $under,
      ]);

      $build = [
        '#type' => 'table',
        '#header' => ['Size', 'File'],
        '#rows' => $files,
      ];
      $table = $this->renderer->renderRoot($build);

      if ($difference > 0) {
        $this->ampService->devMessage($title, 'addError');
        $this->ampService->devMessage($output, 'addError');
        $this->ampService->devMessage($table, 'addError');
      }
      else {
        $this->ampService->devMessage($title);
        $this->ampService->devMessage($output);
        $this->ampService->devMessage($table);
      }
    }
    return $elements;
  }

  /**
   * Minify and optimize css.
   *
   * Some code copied from color_scheme_form_submit() which rewrites css to
   * replace colors in css files.
   *
   * @param string $style
   *   The css to minify.
   *
   * @return string
   *   The minified css.
   */
  public function minify($style) {
    // Remove comments.
    $style = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $style);
    // Remove space after colons.
    $style = str_replace(': ', ':', $style);
    // Remove whitespace.
    $style = str_replace([
      "\r\n",
      "\r",
      "\n",
      "\t",
      '  ',
      '    ',
      '    ',
    ], '', $style);
    return $style;
  }

  /**
   * Strip css which won't validate as AMPHTML.
   *
   * @param string $value
   *   The css to strip.
   *
   * @return string
   *   The stripped css.
   */
  public function strip($value) {
    // Remove css that won't validate as AMPHTML.
    $invalid = [
      '!important',
    ];
    $value = str_replace($invalid, '', $value);
    return $value;
  }

  /**
   * Format values consistently.
   *
   * @param string $value
   *   The number to minify.
   *
   * @return string
   *   The formatted number.
   */
  public function format($value) {
    return number_format($value, 0);
  }

  /**
   * Rewrite relative urls in css.
   *
   * Rewrite relative css asset paths in css since they won't work correctly
   * when css is rendered inline instead of as attachments.
   * 1) Identify the path to where this CSS file originated from. This, when
   *    passed through rewriteFileURI() will iteratively remove ../ values
   *    within the css to rewrite the url relative to the web root.
   * 2) Prefix all relative paths within this CSS file with the file path.
   *
   * This mimics what the CSSOptimizer does when css is aggregated.
   */
  public function doRewrite($url, $css) {
    $this->rewriteFileURIBasePath = dirname($url) . '/';
    $css = preg_replace_callback('/url\\([\'"]?(?![a-z]+:|\\/+)([^\'")]+)[\'"]?\\)/i', [
      $this,
      'rewriteFileURI',
    ], $css);
    $css = $this->minify($css);
    return $css;
  }

  /**
   * Prefixes all paths within a CSS file for processFile().
   *
   * Copied from \Drupal\Core\Asset\CssOptimizer. We can't use that service
   * because some modules, like the CDN module, decorate or alter it,
   * and this method is not guaranteed by the interface, so we can't rely
   * on it.
   *
   * @param array $matches
   *   An array of matches by a preg_replace_callback() call that scans for
   *   url() references in CSS files, except for external or absolute ones.
   *
   * @return string
   *   The rewritten file path.
   *
   * @see https://www.drupal.org/project/amp/issues/3094944
   */
  public function rewriteFileURI($matches) {
    // Prefix with base and remove '../' segments where possible.
    $path = $this->rewriteFileURIBasePath . $matches[1];
    $last = '';
    while ($path != $last) {
      $last = $path;
      $path = preg_replace('`(^|/)(?!\.\./)([^/]+)/\.\./`', '$1', $path);
    }
    return 'url(' . file_url_transform_relative(file_create_url($path)) . ')';
  }

}
