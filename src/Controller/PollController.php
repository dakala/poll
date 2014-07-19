<?php

/**
 * @file
 * Contains \Drupal\poll\Controller\PollController.
 */

namespace Drupal\poll\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\poll\PollInterface;

/**
 * Returns responses for aggregator module routes.
 */
class PollController extends ControllerBase {

  /**
   * Presents the poll creation form.
   *
   * @return array
   *   A form array as expected by drupal_render().
   */
  public function pollAdd() {
    $account = $this->currentUser();
    $poll = $this->entityManager()->getStorage('poll')
      ->create(array(
        'uid' => $account->id(),
        'runtime' => 0,
        'anonymous_vote_allow' => 0,
        'cancel_vote_allow' => 0,
        'result_vote_allow' => 0,
        'status' => 1,
      ));
    return $this->entityFormBuilder()->getForm($poll);
  }

  /**
   * Route title callback.
   *
   * @param \Drupal\poll\PollInterface $poll
   *   The poll entity.
   *
   * @return string
   *   The poll label.
   */
  public function pollTitle(PollInterface $poll) {
    return Xss::filter($poll->label());
  }

  public function viewPoll(PollInterface $poll) {
    $output = entity_view($poll, 'default');
    return $output;
  }

}
