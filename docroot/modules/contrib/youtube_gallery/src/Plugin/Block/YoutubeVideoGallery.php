<?php

namespace Drupal\youtube_gallery\Plugin\Block;

/**
 * @file
 * Create a custom block for rendering youtube videos.
 */

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\youtube_gallery\Controller\youtubeConfig;

/**
 * Provides a 'youtube gallery' block.
 *
 * @Block(
 *   id = "youtube_gallery_block",
 *   admin_label = @Translation("Youtube Gallery"),
 *   category = @Translation("Youtube Channel Videos ")
 * )
 */
class YoutubeVideoGallery extends BlockBase implements ContainerFactoryPluginInterface {

  protected $youtube;

  /**
   * Implemets construct for create depenndency injection.
   */
  public function __construct(
        array $configuration,
        $plugin_id,
        $plugin_definition,
        youtubeConfig $youtubeConfig
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->youtube = $youtubeConfig;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('youtube_gallery.content')
    );
  }

  /**
   * Build a youtube gallery block.
   */
  public function build() {

    global $base_url;

    $content = $this->youtube->getYoutubeVideos();

    if ($content != NULL) {

      $total = $this->youtube->getMaxVideos();

      $output = '';

      for ($i = 0; $i < $total; $i++) {

        $output    .= "<div class='youtube-gallery-block-list'>";
        $videoId    = $content['items'][$i]['snippet']['resourceId']['videoId'];
        $videoTitle = $content['items'][$i]['snippet']['title'];
        $thumbnail  = $content['items'][$i]['snippet']['thumbnails']['medium']['url'];
        $duration   = $this->youtube->getVideoDuration($videoId);

        $link = $base_url . "/youtube-gallery/" . $videoId;

        $output .= "<br>";

        $output .= "<a href=" . $link . " >" . $videoTitle . "</a><br>";

        $output .= "<br><a href=" . $link . "><img src=" . $thumbnail . "/></a>";
        $output .= "<br> Duration: " . $duration;
        $output .= "</div>";
      }
    }
    else {

      $output = "<h2> No records found. !!!</h2>";
    }

    return [
      '#type' => 'markup',
      '#markup' => $output,
      '#allowed_tags' => ['iframe', 'a', 'html', 'br', 'img', 'h2'],
    ];

  }

}
