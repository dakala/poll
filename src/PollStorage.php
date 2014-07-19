<?php

/**
 * @file
 * Contains \Drupal\poll\PollStorage.
 */

namespace Drupal\poll;

use Drupal\Core\Entity\ContentEntityDatabaseStorage;
use Drupal\Core\Session\AccountInterface;

/**
 * Controller class for polls.
 *
 * This extends the Drupal\Core\Entity\ContentEntityDatabaseStorage class,
 * adding required special handling for poll entities.
 */
class PollStorage extends ContentEntityDatabaseStorage implements PollStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function getTotalVotes(PollInterface $poll) {
    $query = $this->database->query("SELECT COUNT(chid) FROM {poll_vote} WHERE pid = :pid", array(':pid' => $poll->id()));
    return $query->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function deleteVotes(PollInterface $poll) {
    return $this->database->delete('poll_vote')->condition('pid', $poll->id())
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function getUserVote(PollInterface $poll) {
    $uid = \Drupal::currentUser()->id();
    if ($uid || $poll->getAnonymousVoteAllow()) {
      if ($uid) {
        $query = $this->database->query("SELECT * FROM {poll_vote} WHERE pid = :pid AND uid = :uid", array(
          ':pid' => $poll->id(),
          ':uid' => $uid
        ));
      }
      else {
        $query = $this->database->query("SELECT * FROM {poll_vote} WHERE pid = :pid AND hostname = :hostname", array(
          ':pid' => $poll->id(),
          ':hostname' => Drupal::request()->getClientIp()
        ));
      }
      return $query->fetchObject();
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function saveVote(array $options) {
    if (!is_array($options)) {
      return FALSE;
    }
    return $this->database->insert('poll_vote')->fields($options)->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function getVotes(PollInterface $poll) {
    $votes = array();
    // Set votes for all options to 0
    $options = $poll->getOptions();
    foreach ($options as $id => $label) {
      $votes[$id] = 0;
    }

    $query = $this->database->query("SELECT chid, COUNT(chid) AS votes FROM {poll_vote} WHERE pid = :pid GROUP BY chid", array(':pid' => $poll->id()));
    $results = $query->fetchAll();
    // Replace the count for options that have recorded votes in the database.
    // Multiply by the vote value for each option.
    $optionValues = $poll->getOptionValues();
    foreach ($results as $result) {
      $votes[$result->chid] = $result->votes * $optionValues[$result->chid];
    }

    return $votes;
  }

  /**
   * {@inheritdoc}
   */
  public function cancelVote(PollInterface $poll, AccountInterface $account = NULL) {
    $this->database->delete('poll_vote')
      ->condition('pid', $poll->id())
      ->condition('uid', (!$account instanceof AccountInterface) ? \Drupal::currentUser()
        ->id() : $account->id())
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function getPollDuplicates(PollInterface $poll) {
    $query = \Drupal::entityQuery('poll');
    $query->condition('question', $poll->label());

    if ($poll->id()) {
      $query->condition('id', $poll->id(), '<>');
    }
    return $this->loadMultiple($query->execute());
  }

  /**
   * {@inheritdoc}
   */
  public function getMostRecentPoll() {
    $query = \Drupal::entityQuery('poll')
      ->condition('status', POLL_PUBLISHED)
      ->sort('created', 'DESC')
      ->pager(1);
    return $this->loadMultiple($query->execute());
  }

  public function getExpiredPolls() {
    $query = $this->database->query("SELECT id FROM {poll_poll} WHERE (UNIX_TIMESTAMP() > (created + runtime)) AND status = 1 AND runtime <> 0");
    return $this->loadMultiple($query->fetchCol());
  }
}
