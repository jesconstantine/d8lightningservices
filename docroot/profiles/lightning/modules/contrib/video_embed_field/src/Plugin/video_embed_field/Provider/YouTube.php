<?php

namespace Drupal\video_embed_field\Plugin\video_embed_field\Provider;

use Drupal\video_embed_field\ProviderPluginBase;

/**
 * A YouTube provider plugin.
 *
 * @VideoEmbedProvider(
 *   id = "youtube",
 *   title = @Translation("YouTube")
 * )
 */
class YouTube extends ProviderPluginBase {

  /**
   * {@inheritdoc}
   */
  public function renderEmbedCode($width, $height, $autoplay) {
    return [
      '#type' => 'video_embed_iframe',
      '#provider' => 'youtube',
      '#url' => sprintf('https://www.youtube.com/embed/%s', $this->getVideoId()),
      '#query' => [
        'autoplay' => $autoplay,
        'start' => $this->getTimeIndex(),
        'rel' => '0',
      ],
      '#attributes' => [
        'width' => $width,
        'height' => $height,
        'frameborder' => '0',
        'allowfullscreen' => 'allowfullscreen',
      ],
    ];
  }

  /**
   * Get the time index for when the given video starts.
   */
  protected function getTimeIndex() {
    preg_match('/[&\?]t=(?<timeindex>\d*)/', $this->getInput(), $matches);
    return isset($matches['timeindex']) ? $matches['timeindex'] : 0;
  }

  /**
   * {@inheritdoc}
   */
  public function getRemoteThumbnailUrl() {
    $url = 'http://img.youtube.com/vi/%s/%s.jpg';
    $high_resolution = sprintf($url, $this->getVideoId(), 'maxresdefault');
    $backup = sprintf($url, $this->getVideoId(), 'mqdefault');
    try {
      $this->httpClient->head($high_resolution);
      return $high_resolution;
    }
    catch (\Exception $e) {
      return $backup;
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getIdFromInput($input) {
    preg_match('/^https?:\/\/(www\.)?((?!.*list=)youtube\.com\/watch\?.*v=|youtu\.be\/)(?<id>[0-9A-Za-z_-]*)/', $input, $matches);
    return isset($matches['id']) ? $matches['id'] : FALSE;
  }

}
