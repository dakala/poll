<?php

/**
 * @file
 * Contains \Drupal\poll\Form\PollVoteDeleteForm.
 */

namespace Drupal\poll\Form;

use Drupal\Core\Entity\ContentEntityConfirmFormBase;

/**
 * Provides a form for deleting a vote.
 */
class PollVoteDeleteForm extends ContentEntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete this vote for %poll', array('%poll' => $this->entity->label()));
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelRoute() {
    return $this->entity->urlInfo();
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
  public function buildForm(array $form, array &$form_state) {
    // Always provide entity id in the same form key as in the entity edit form.
    $form['uid'] = array('#type' => 'value', '#value' => arg(4));
    $form = parent::buildForm($form, $form_state);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array $form, array &$form_state) {
    $account = entity_load('user', $form_state['values']['uid']);
    $pollStorage = \Drupal::entityManager()->getStorage($this->entity->getId());
    $pollStorage->cancelVote($this->entity, $account);
    watchdog('poll', '%user\'s vote in Poll #%poll deleted.', array(
      '%user' => $account->id(),
      '%poll' => $this->entity->getId()
    ));
    drupal_set_message($this->t('The vote has been deleted.'));
    // Display the original poll.
    $form_state['redirect_route'] = array(
      'route_name' => 'poll.poll_view',
      'route_parameters' => array(
        'poll' => $this->entity->id(),
      ),
    );
  }
}
