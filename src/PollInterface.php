<?php

/**
 * @file
 * Contains \Drupal\poll\Entity\PollInterface.
 */

namespace Drupal\poll;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Provides an interface defining an poll entity.
 */
interface PollInterface extends ContentEntityInterface {

  public function getId();

  /**
   * Sets the question for the poll.
   *
   * @param string $question
   *   The short title of the feed.
   *
   * @return \Drupal\poll\PollInterface
   *   The class instance that this method is called on.
   */
  public function setQuestion($question);

  /**
   * Return when the feed was modified last time.
   *
   * @return int
   *   The timestamp of the last time the feed was modified.
   */
  public function getCreated();

  /**
   * Sets the last modification of the feed.
   *
   * @param int $created
   *   The timestamp when the feed was modified.
   *
   * @return \Drupal\poll\PollInterface
   *   The class instance that this method is called on.
   */
  public function setCreated($created);


  /**
   * Returns the runtimeof the feed in seconds.
   *
   * @return int
   *   The refresh rate of the feed in seconds.
   */
  public function getRuntime();

  /**
   * Sets the runtime of the feed in seconds.
   *
   * @param int $refresh
   *   The refresh rate of the feed in seconds.
   *
   * @return \Drupal\poll\PollInterface
   *   The class instance that this method is called on.
   */
  public function setRuntime($runtime);


  /**
   * Returns the last time where the feed was checked for new items.
   *
   * @return int
   *   The timestamp when new items were last checked for.
   */
  public function getAnonymousVoteAllow();

  /**
   * Sets the time when this feed was queued for refresh, 0 if not queued.
   *
   * @param int $checked
   *   The timestamp of the last refresh.
   *
   * @return \Drupal\poll\PollInterface
   *   The class instance that this method is called on.
   */
  public function setAnonymousVoteAllow($anonymous_vote_allow);

  /**
   * Returns the time when this feed was queued for refresh, 0 if not queued.
   *
   * @return int
   *   The timestamp of the last refresh.
   */
  public function getCancelVoteAllow();

  /**
   * Sets the time when this feed was queued for refresh, 0 if not queued.
   *
   * @param int $queued
   *   The timestamp of the last refresh.
   *
   * @return \Drupal\poll\PollInterface
   *   The class instance that this method is called on.
   */
  public function setCancelVoteAllow($cancel_vote_allow);


  /**
   * Returns the time when this feed was queued for refresh, 0 if not queued.
   *
   * @return int
   *   The timestamp of the last refresh.
   */
  public function getResultVoteAllow();

  /**
   * Sets the time when this feed was queued for refresh, 0 if not queued.
   *
   * @param int $queued
   *   The timestamp of the last refresh.
   *
   * @return \Drupal\poll\PollInterface
   *   The class instance that this method is called on.
   */
  public function setResultVoteAllow($result_vote_allow);

  /**
   * Returns the node published status indicator.
   *
   * Unpublished nodes are only visible to their authors and to administrators.
   *
   * @return bool
   *   TRUE if the node is published.
   */
  public function isActive();

  /**
   * Sets the published status of a node..
   *
   * @param bool $published
   *   TRUE to set this node to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\node\NodeInterface
   *   The called node entity.
   */
  public function setActive($active);


  /**
   * @todo: Refactor - doesn't belong here.
   *
   * @return mixed
   */
  public function hasUserVoted();
  /**
   * Get all options for this poll.
   *
   * @return array
   */
  public function getOptions();
  /**
   * Get the values of each vote option for this poll.
   *
   * @return array
   */
  public function getOptionValues();

  /**
   * Remove 'entity/' from the generted uri for this entity.
   *
   * @return mixed
   */
  public function normaliseUri();

  public function isClosed();

  public function label();
}
