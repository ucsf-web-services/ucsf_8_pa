<?php

namespace Drupal\video_embed_field\Plugin\video_embed_field\Provider;

use Drupal\video_embed_field\ProviderPluginBase;

/**
 * A Vimeo provider plugin.
 *
 * @VideoEmbedProvider(
 *   id = "vimeo",
 *   title = @Translation("Vimeo")
 * )
 */
class Vimeo extends ProviderPluginBase {

  /**
   * {@inheritdoc}
   */
  public function renderEmbedCode($width, $height, $autoplay) {
    /*
    * Modification to allow the ability to add 1 option to the end of a vimeo embed
    * Get the raw input of what was entered for the url.
    * Match to get the option after the url
    * Conmbine it with the videoId string to append it to iframe #url item
    */
    $input = $this->getInput();
    preg_match('/^https?:\/\/(www\.)?vimeo.com\/(channels\/[a-zA-Z0-9]*\/)?(?<id>[0-9]*)(\/[a-zA-Z0-9]+)?(\#t=(\d+)s)?(\?((?<option>[a-zA-Z0-9]*=[a-zA-Z0-9]*)))?$/',$input ,$option );
    if($option['option']){
      $url = $this->getVideoId(). "?".$option['option']."&";
    }
    else {
      $url = $this->getVideoId();
    }

    $iframe = [
      '#type' => 'video_embed_iframe',
      '#provider' => 'vimeo',
      '#url' => sprintf('https://player.vimeo.com/video/%s', $url),
      '#query' => [
        'autoplay' => $autoplay,
      ],
      '#attributes' => [
        'width' => $width,
        'height' => $height,
        'frameborder' => '0',
        'allowfullscreen' => 'allowfullscreen',
      ],
    ];
    if ($time_index = $this->getTimeIndex()) {
      $iframe['#fragment'] = sprintf('t=%s', $time_index);
    }
    return $iframe;
  }

  /**
   * {@inheritdoc}
   */
  public function getRemoteThumbnailUrl() {
    return $this->oEmbedData()->thumbnail_url;
  }

  /**
   * Get the vimeo oembed data.
   *
   * @return array
   *   An array of data from the oembed endpoint.
   */
  protected function oEmbedData() {
    return json_decode(file_get_contents('http://vimeo.com/api/oembed.json?url=' . $this->getInput()));
  }

  /**
   * {@inheritdoc}
   */
  public static function getIdFromInput($input) {
    preg_match('/^https?:\/\/(www\.)?vimeo.com\/(channels\/[a-zA-Z0-9]*\/)?(?<id>[0-9]*)(\/[a-zA-Z0-9]+)?(\#t=(\d+)s)?(\?background=(?<option>[a-zA-Z0-9]*))?$/', $input, $matches);
    return isset($matches['id']) ? $matches['id'] : FALSE;
  }

  /**
   * Get the time index from the URL.
   *
   * @return string|FALSE
   *   A time index parameter to pass to the frame or FALSE if none is found.
   */
  protected function getTimeIndex() {
    preg_match('/\#t=(?<time_index>(\d+)s)$/', $this->input, $matches);
    return isset($matches['time_index']) ? $matches['time_index'] : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->oEmbedData()->title;
  }

}
