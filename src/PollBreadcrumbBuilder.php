<?php

/**
 * @file
 * Contains \Drupal\poll\PollBreadcrumbBuilder.
 */

namespace Drupal\poll;

use Drupal\Core\Breadcrumb\BreadcrumbBuilderBase;

/**
 * Provides a custom poll breadcrumb builder.
 */
class PollBreadcrumbBuilder extends BreadcrumbBuilderBase {

  /**
   * {@inheritdoc}
   */
  public function applies(array $attributes) {
    return !empty($attributes['poll']) && ($attributes['poll'] instanceof PollInterface);
  }

  /**
   * {@inheritdoc}
   */
  public function build(array $attributes) {
    $poll = $attributes['poll'];
    $breadcrumb[] = $this->l($poll->label(), 'poll.poll_view', array('poll' => $poll->id()));

    $breadcrumb[] = $this->l($this->t('Home'), '<front>');
    $breadcrumb = array_reverse($breadcrumb);

    return $breadcrumb;
  }
}
