<?php

/**
 * @file
 * Contains \Drupal\poll\PollStorageInterface.
 */

namespace Drupal\poll;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines a common interface for poll entity controller classes.
 */
interface PollStorageInterface extends EntityStorageInterface {

  /**
   * Save a user's vote.
   *
   * @param array $options
   *
   * @return mixed
   */
  public function saveVote(array $options);

  /**
   * Cancel a user's vote.
   *
   * @param PollInterface $poll
   * @param AccountInterface $account
   *
   * @return mixed
   */
  public function cancelVote(PollInterface $poll, AccountInterface $account = NULL);

  /**
   * Get total votes for a poll.
   *
   * @param PollInterface $poll
   *
   * @return mixed
   */
  public function getTotalVotes(PollInterface $poll);

  /**
   * Get all votes for a poll.
   *
   * @param PollInterface $poll
   *
   * @return mixed
   */
  public function getVotes(PollInterface $poll);

  /**
   * Delete a user's votes for a poll.
   *
   * @param PollInterface $poll
   *
   * @return mixed
   */
  public function deleteVotes(PollInterface $poll);

  /**
   * Get a user's votes for a poll.
   *
   * @param PollInterface $poll
   *
   * @return mixed
   */
  public function getUserVote(PollInterface $poll);

  /**
   * Get the most recent poll posted on the site.
   *
   * @return mixed
   */
  public function getMostRecentPoll();

  /**
   * Find all duplicates of a poll by matching the question.
   *
   * @param PollInterface $poll
   *
   * @return mixed
   */
  public function getPollDuplicates(PollInterface $poll);

  /**
   * Returns all expired polls.
   *
   * @return \Drupal\poll\PollInterface[]
   *
   */
  public function getExpiredPolls();

}
