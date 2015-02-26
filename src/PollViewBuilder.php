<?php

/**
 * @file
 * Contains \Drupal\aggregator\FeedViewBuilder.
 */

namespace Drupal\poll;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityViewBuilder;

/**
 * Render controller for polls.
 */
class PollViewBuilder extends EntityViewBuilder {

  /**
   * {@inheritdoc}
   */
  public function view(EntityInterface $entity, $view_mode = 'full', $langcode = NULL) {

    $callback = 'poll.post_render_cache:renderViewForm';
    $context = array(
      'id' => $entity->id(),
      'view_mode' => $view_mode,
    );
    $placeholder = drupal_render_cache_generate_placeholder($callback, $context);
    $output = array(
      '#post_render_cache' => array(
        $callback => array(
          $context,
        ),
      ),
      '#markup' => $placeholder,
    );

    return $output;

  }

}
