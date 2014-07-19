<?php

/**
 * @file
 * Contains \Drupal\poll\Form\PollDeleteForm.
 */

namespace Drupal\poll\Form;

use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Url;

/**
 * Provides a form for deleting a poll.
 */
class PollDeleteForm extends ContentEntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return t('All associated votes will be deleted too. This action cannot be undone.');
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete this poll %poll', array('%poll' => $this->entity->label()));
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelRoute() {
    return new Url('poll.poll_list');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array $form, array &$form_state) {
    $this->entity->delete();
    watchdog('poll', 'Poll %poll deleted.', array('%poll' => $this->entity->label()));
    drupal_set_message($this->t('The poll %poll has been deleted.', array('%poll' => $this->entity->label())));
    $form_state['redirect_route']['route_name'] = 'poll.poll_list';
  }

}
