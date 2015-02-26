<?php

/**
 * @file
 * Contains \Drupal\poll\Form\PollVoteDeleteForm.
 */

namespace Drupal\poll\Form;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Provides a form for deleting a vote.
 */
class PollVoteDeleteForm extends ContentEntityConfirmFormBase implements ContainerAwareInterface {
  use ContainerAwareTrait;

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete this vote for %poll', array('%poll' => $this->entity->label()));
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
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
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $uid = $this->container->get('request_stack')->getCurrentRequest()->attributes->get('user');
    $account = User::load($uid);
    $pollStorage = \Drupal::entityManager()->getStorage('poll');
    $pollStorage->cancelVote($this->entity, $account);
    \Drupal::logger('poll')->notice('%user\'s vote in Poll #%poll deleted.', array(
      '%user' => $account->id(),
      '%poll' => $this->entity->id()
    ));
    drupal_set_message($this->t('Your vote was cancelled.'));

    // Display the original poll.
    $form_state->setRedirect('entity.poll.canonical', array('poll' => $this->entity->id()));
  }
}
