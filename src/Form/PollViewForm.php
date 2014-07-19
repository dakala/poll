<?php

/**
 * @file
 * Contains \Drupal\ban\Form\BanAdmin.
 */

namespace Drupal\poll\Form;

use Drupal\Core\Form\FormBase;
use Drupal\poll\PollInterface;
use Drupal\Component\Utility\String;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Displays banned IP addresses.
 */
class PollViewForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'poll_view_form';
  }

  public function buildForm(array $form, array &$form_state, $poll = NULL) {
    // Add the poll to the form.
    $form['poll']['#type'] = 'value';
    $form['poll']['#value'] = $poll;

    if ($this->showResults($poll, $form_state)) {
      $form['results']['#markup'] = $this->showPollResults($poll);
    }
    else {
      $options = $poll->getOptions();
      if ($options) {
        $form['choice'] = array(
          '#type' => 'radios',
          '#title' => t('Choices'),
          '#title_display' => 'invisible',
          '#options' => $options,
        );
      }
      // Add the poll to the form.
      $form['poll']['#type'] = 'value';
      $form['poll']['#value'] = $poll;

      $form['#theme'] = 'poll_vote';
      $form['#entity'] = $poll;
      // Set form caching because we could have multiple of these forms on
      // the same page, and we want to ensure the right one gets picked.
      $form_state['cache'] = TRUE;
      // Set a flag to hide results which will be removed if we want to view
      // results when the form is rebuilt.
      $form_state['input']['show_results'] = FALSE;
    }

    $form['actions'] = $this->actions($form, $form_state, $poll);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    // Leaving empty
    $this->save($form, $form_state);
  }

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

  protected function actions(array $form, array &$form_state, $poll) {
    // Remove all actions.
    $actions = array();
    if ($this->showResults($poll, $form_state)) {
      // Allow user to cancel their vote.
      if ($poll->hasUserVoted() && $poll->getCancelVoteAllow()) {
        $actions['#type'] = 'actions';
        $actions['cancel']['#type'] = 'submit';
        $actions['cancel']['#button_type'] = 'primary';
        $actions['cancel']['#value'] = t('Cancel vote');
        $actions['cancel']['#submit'] = array(array($this, 'cancel'));
        $actions['cancel']['#weight'] = '0';
      }
      if (!$poll->hasUserVoted()) {
        $actions['#type'] = 'actions';
        $actions['back']['#type'] = 'submit';
        $actions['back']['#button_type'] = 'primary';
        $actions['back']['#value'] = t('View poll');
        $actions['back']['#submit'] = array(array($this, 'back'));
        $actions['back']['#weight'] = '0';
      }
    }
    else {
      $actions['#type'] = 'actions';
      $actions['submit']['#type'] = 'submit';
      $actions['submit']['#button_type'] = 'primary';
      $actions['submit']['#value'] = t('Vote');
//      $actions['submit']['#submit'] = array(array($this, 'save'));
      $actions['submit']['#weight'] = '0';

      // view results before voting
      if ($poll->result_vote_allow->value) {
        $actions['result']['#type'] = 'submit';
        $actions['result']['#button_type'] = 'primary';
        $actions['result']['#value'] = t('View results');
        $actions['result']['#submit'] = array(array($this, 'result'));
        $actions['result']['#weight'] = '1';
      }
    }

    return $actions;
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
    $poll->links = array(
      'poll-report' => array(
        'title' => t('Older Polls'),
        'href' => "polls",
        'html' => TRUE,
        //'query' => array('token' => \Drupal::getContainer()->get('csrf_token')->get("node/{$entity->id()}/report")),
      ),
    );

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
        'poll' => $form_state['values']['poll']->id(),
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
    $options['pid'] = $form_state['values']['poll']->id();
    $options['hostname'] = \Drupal::request()->getClientIp();
    $options['timestamp'] = REQUEST_TIME;
    // save vote
    $pollStorage = \Drupal::entityManager()->getStorage($form_state['values']['poll']->getId());
    $pollStorage->saveVote($options);
    // @todo: confirm vote has been saved.
    drupal_set_message($this->t('Your vote has been recorded.'));

    $form_state['redirect'] = $form_state['values']['poll']->url();
  }

  /**
   * {@inheritdoc}
   *
   */
  public function validateForm(array &$form, array &$form_state) {
    if($form_state['values']['op'] == 'Vote') {
      if (!isset($form_state['values']['choice']) || $form_state['values']['choice'] == NULL) {
        $this->setFormError('choice', $form_state, $this->t('Your vote could not be recorded because you did not select any of the choices.'));
      }
    }
  }


}
