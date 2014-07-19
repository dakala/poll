<?php

/**
 * @file
 * Definition of \Drupal\poll\Form\PollViewFormController.
 */

namespace Drupal\poll;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Component\Utility\String;
use Drupal;

/**
 * Base for controller for poll term edit forms.
 */
class PollViewFormController extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, array &$form_state) {
    if ($this->showResults($this->entity, $form_state)) {
      $form['results']['#markup'] = $this->showPollResults($this->entity);
    }
    else {
      $options = $this->entity->getOptions();
      if ($options) {
        $form['choice'] = array(
          '#type' => 'radios',
          '#title' => t('Choices'),
          '#title_display' => 'invisible',
          '#options' => $options,
        );
      }
      $form['#theme'] = 'poll_vote';
      $form['#entity'] = $this->entity;
      // Set form caching because we could have multiple of these forms on
      // the same page, and we want to ensure the right one gets picked.
      $form_state['cache'] = TRUE;
      // Set a flag to hide results which will be removed if we want to view
      // results when the form is rebuilt.
      $form_state['input']['show_results'] = FALSE;
    }
    return $form;
  }

  /**
   * Returns the action form elements for the current entity form.
   */
  protected function actions(array $form, array &$form_state) {
    $actions = parent::actions($form, $form_state);
    if (count($actions)) {
      foreach ($actions as $name => $action) {
        if ($name != 'submit') {
          unset($actions[$name]);
        }
      }
    }

    if ($this->showResults($this->entity, $form_state)) {
      // Remove all actions.
      $actions = array();
      // Allow user to cancel their vote.
      if ($this->entity->hasUserVoted() && $this->entity->getCancelVoteAllow()) {
        $actions['#type'] = 'actions';
        $actions['cancel']['#type'] = 'submit';
        $actions['cancel']['#button_type'] = 'primary';
        $actions['cancel']['#value'] = $this->t('Cancel vote');
        $actions['cancel']['#submit'] = array(array($this, 'cancel'));
        $actions['cancel']['#weight'] = '0';
      }
      if (!$this->entity->hasUserVoted()) {
        $actions['#type'] = 'actions';
        $actions['back']['#type'] = 'submit';
        $actions['back']['#button_type'] = 'primary';
        $actions['back']['#value'] = $this->t('View poll');
        $actions['back']['#submit'] = array(array($this, 'back'));
        $actions['back']['#weight'] = '0';
      }
    }
    else {
      $actions['#type'] = 'actions';
      $actions['submit']['#type'] = 'submit';
      $actions['submit']['#button_type'] = 'primary';
      $actions['submit']['#value'] = $this->t('Vote');
      $actions['submit']['#validate'] = array(array($this, 'validate'));
      $actions['submit']['#weight'] = '0';

      // view results before voting
      if ($this->entity->result_vote_allow->value) {
        $actions['result']['#type'] = 'submit';
        $actions['result']['#button_type'] = 'primary';
        $actions['result']['#value'] = $this->t('View results');
        $actions['result']['#submit'] = array(array($this, 'result'));
        $actions['result']['#weight'] = '1';
      }
    }

    return $actions;
  }

  /**
   * Cancel vote submit function.
   *
   * @param array $form
   * @param array $form_state
   */
  public function cancel(array $form, array &$form_state) {
    $form_state['redirect_route'] = array(
      'route_name' => 'poll.poll_vote_delete',
      'route_parameters' => array(
        'poll' => $this->entity->id(),
        'user' => \Drupal::currentUser()->id(),
      ),
    );
  }

  /**
   * View vote results submit function.
   *
   * @param array $form
   * @param array $form_state
   */
  public function result(array $form, array &$form_state) {
    $form_state['input']['show_results'] = TRUE;
    $form_state['rebuild'] = TRUE;
  }

  /**
   * Back to poll view submit function.
   *
   * @param array $form
   * @param array $form_state
   */
  public function back(array $form, array &$form_state) {
    $form_state['input']['show_results'] = FALSE;
    $form_state['rebuild'] = TRUE;
  }

  /**
   * Save a user's vote submit function.
   *
   * @param array $form
   * @param array $form_state
   */
  public function save(array $form, array &$form_state) {
    $options = array();
    $options['chid'] = $form_state['values']['choice'];
    $options['uid'] = \Drupal::currentUser()->id();
    $options['pid'] = $this->entity->id();
    $options['hostname'] = \Drupal::request()->getClientIp();
    $options['timestamp'] = REQUEST_TIME;
    // save vote
    $pollStorage = \Drupal::entityManager()->getStorage($this->entity->getId());
    $status = $pollStorage->saveVote($options);
    if($status) {
      drupal_set_message($this->t('Your vote has been recorded.'));
    }
    else {
      drupal_set_message($this->t('Sorry, your vote could not be recorded.'), 'error');
    }
    $form_state['redirect'] = $this->entity->url();
  }

  /**
   * @inheritdoc
   */
  public function validate(array $form, array &$form_state) {
    if (!isset($form_state['values']['choice']) || $form_state['values']['choice'] == NULL) {
      Drupal::formBuilder()
        ->setErrorByName('choice', $form_state, $this->t('Your vote could not be recorded because you did not select any of the choices.'));
    }
  }

  /**
   * Determine whether we should display the poll results.
   *
   * @param PollInterface $poll
   * @param $form_state
   *
   * @return bool
   */
  public function showResults(PollInterface $poll, $form_state) {
    $account = $this->currentUser();
    switch (TRUE) {
      // The "View results" button, when available, has been clicked.
      case (isset($form_state['input']) && isset($form_state['input']['show_results']) && $form_state['input']['show_results']):
        return TRUE;
      // The poll is closed.
      case ($poll->isClosed()):
        return TRUE;
      // Anonymous user is trying to view a poll they aren't allowed to vote in.
      case ($account->isAnonymous() && !$poll->getAnonymousVoteAllow()):
        return TRUE;
      // The user has already voted.
      case ($poll->hasUserVoted()):
        return TRUE;
      default:
        return FALSE;
    }
  }

  /**
   * Display a themed poll results.
   *
   * @param $poll
   * @param bool $block
   *
   * @return false|string
   */
  function showPollResults($poll, $block = FALSE) {
    $total_votes = 0;
    foreach ($poll->votes as $vote) {
      $total_votes += $vote;
    }

    $options = $poll->getOptions();
    $poll_results = array();
    foreach ($poll->votes as $pid => $vote) {
      $percentage = round($vote * 100 / max($total_votes, 1));
      $display_votes = (!$block) ? ' (' . \Drupal::translation()
          ->formatPlural($vote, '1 vote', '@count votes') . ')' : '';

      $poll_results[] = array(
        '#theme' => 'poll_meter',
        '#prefix' => '<dt class="choice-title">' . String::checkPlain($options[$pid]) . "</dt>\n" . '<dd class="choice-result">',
        '#suffix' => "</dd>\n",
        '#display_value' => t('!percentage%', array('!percentage' => $percentage)) . $display_votes,
        '#min' => 0,
        '#max' => $total_votes,
        '#value' => $vote,
        '#percentage' => $percentage,
        '#attributes' => array('class' => array('bar')),
      );
    }

    $output = array(
      '#theme' => 'poll_results',
      '#raw_title' => $poll->label(),
      '#results' => $poll_results,
      '#votes' => $total_votes,
      '#raw_links' => isset($poll->links) ? $poll->links : array(),
      '#block' => $block,
      '#nid' => $poll->pid,
      '#vote' => isset($poll->vote) ? $poll->vote : NULL
    );

    return drupal_render($output);
  }

}
