<?php

/**
 * @file
 * Definition of Drupal\poll\Plugin\field\FieldFormatter\PollChoiceDefaultFormatter.
 */

namespace Drupal\poll\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'poll_choice' formatter.
 *
 * @FieldFormatter(
 *   id = "poll_choice_default",
 *   module = "poll",
 *   label = @Translation("Poll choice"),
 *   field_types = {
 *     "poll_choice"
 *   }
 * )
 */
class PollChoiceDefaultFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items) {
    $elements = array();
    foreach ($items as $delta => $item) {
      $elements[$delta] = array('#markup' => $item->choice);
    }
    return $elements;
  }

}
