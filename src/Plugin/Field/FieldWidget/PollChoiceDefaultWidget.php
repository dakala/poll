<?php

/**
 * @file
 * Definition of Drupal\poll\Plugin\field\FieldWidget\PollChoiceDefaultWidget.
 */

namespace Drupal\poll\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;


/**
 * Plugin implementation of the 'poll_choice_default' widget.
 *
 * @FieldWidget(
 *   id = "poll_choice_default",
 *   module = "poll",
 *   label = @Translation("Poll choice"),
 *   field_types = {
 *     "poll_choice"
 *   }
 * )
 */
class PollChoiceDefaultWidget extends WidgetBase {

  /**
   * The default value of a vote.
   */
  const VOTE_DEFAULT_VALUE = 1;

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element['chid'] = array(
      '#type' => 'value',
      '#value' => $items[$delta]->chid,
    );
    $element['choice'] = array(
      '#type' => 'textfield',
      '#placeholder' => t('Choice'),
      '#empty_value' => '',
      '#default_value' => isset($items[$delta]->choice) ? $items[$delta]->choice : '',
      '#prefix' => '<div class="container-inline">',
    );
    $element['vote'] = array(
      '#type' => 'number',
      '#placeholder' => t('Vote'),
      '#empty_value' => '',
      '#default_value' => isset($items[$delta]->vote) ? $items[$delta]->vote : static::VOTE_DEFAULT_VALUE,
      '#min' => 0,
      '#suffix' => '</div>',
    );
    return $element;
  }
}
