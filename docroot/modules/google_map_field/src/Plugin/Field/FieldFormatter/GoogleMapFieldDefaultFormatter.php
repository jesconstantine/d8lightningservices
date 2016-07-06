<?php
/**
 * @file
 * Contains \Drupal\google_map_field\Plugin\field\formatter\GoogleMapFieldDefaultFormatter.
 */

namespace Drupal\google_map_field\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Component\Utility\SafeMarkup;

/**
 * Plugin implementation of the 'google_map_field' formatter.
 *
 * @FieldFormatter(
 *   id = "google_map_field_default",
 *   label = @Translation("Google Map Field default"),
 *   field_types = {
 *     "google_map_field"
 *   }
 * )
 */
class GoogleMapFieldDefaultFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = array();
    foreach ($items as $delta => $item) {
      $element = array(
        '#theme' => 'google_map_field',
        '#name' => SafeMarkup::checkPlain($item->name),
        '#lat' => SafeMarkup::checkPlain($item->lat),
        '#lon' => SafeMarkup::checkPlain($item->lon),
        '#zoom' => SafeMarkup::checkPlain($item->zoom),      );
      $element['#attached']['library'][] = 'google_map_field/google-map-field-renderer';
      $element['#attached']['library'][] = 'google_map_field/google-map-apis';
      $elements[$delta] = array('#markup' => drupal_render($element));
    }

    return $elements;
  }

}
