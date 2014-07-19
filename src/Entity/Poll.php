<?php

/**
 * @file
 * Contains \Drupal\poll\Entity\Poll.
 */

namespace Drupal\poll\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\FieldDefinition;
use Symfony\Component\DependencyInjection\Container;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\poll\PollInterface;
use Drupal\Component\Utility\String;
use Drupal\user\UserInterface;

/**
 * Defines the poll entity class.
 *
 * @ContentEntityType(
 *   id = "poll",
 *   label = @Translation("Poll"),
 *   controllers = {
 *     "storage" = "Drupal\poll\PollStorage",
 *     "list_builder" = "Drupal\poll\PollListBuilder",
 *     "view_builder" = "Drupal\poll\PollViewBuilder",
 *     "form" = {
 *       "default" = "Drupal\poll\PollFormController",
 *       "edit" = "Drupal\poll\PollFormController",
 *       "view" = "Drupal\poll\PollViewFormController",
 *       "delete" = "Drupal\poll\Form\PollDeleteForm",
 *       "delete_vote" = "Drupal\poll\Form\PollVoteDeleteForm",
 *       "delete_items" = "Drupal\poll\Form\PollItemsDeleteForm",
 *     }
 *   },
 *   links = {
 *     "canonical" = "poll.poll_view",
 *     "edit-form" = "poll.poll_edit",
 *     "delete-form" = "poll.poll_delete",
 *     "admin-form" = "poll.poll_list"
 *   },
 *   base_table = "poll_poll",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "question",
 *     "uuid" = "uuid",
 *   }
 * )
 */
class Poll extends ContentEntityBase implements PollInterface {

  public function getId() {
    return $this->getEntityType()->id();
  }

  /**
   * Implements Drupal\Core\Entity\EntityInterface::label().
   */
  public function label() {
    return $this->get('question')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setQuestion($question) {
    $this->set('question', $question);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreated($created) {
    $this->set('created', $created);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreated() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getRuntime() {
    return $this->get('runtime')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setRuntime($runtime) {
    $this->set('runtime', $runtime);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getAnonymousVoteAllow() {
    return $this->get('anonymous_vote_allow')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setAnonymousVoteAllow($anonymous_vote_allow) {
    $this->set('anonymous_vote_allow', $anonymous_vote_allow);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelVoteAllow() {
    return $this->get('cancel_vote_allow')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCancelVoteAllow($cancel_vote_allow) {
    $this->set('cancel_vote_allow', $cancel_vote_allow);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getResultVoteAllow() {
    return $this->get('result_vote_allow')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setResultVoteAllow($result_vote_allow) {
    $this->set('result_vote_allow', $result_vote_allow);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isActive() {
    return (bool) $this->get('status')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setActive($active) {
    $this->set('status', $active ? POLL_PUBLISHED : POLL_NOT_PUBLISHED);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('uid')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('uid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('uid', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isClosed() {
    return $this->get('status')->value == 0;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['id'] = FieldDefinition::create('integer')
      ->setLabel(t('Poll ID'))
      ->setDescription(t('The ID of the poll.'))
      ->setReadOnly(TRUE)
      ->setSetting('unsigned', TRUE);

    $fields['uid'] = FieldDefinition::create('entity_reference')
      ->setLabel(t('User ID'))
      ->setDescription(t('The user ID of the poll author.'))
      ->setSetting('target_type', 'user')
      ->setSetting('default_value', 0);

    $fields['uuid'] = FieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The poll UUID.'))
      ->setReadOnly(TRUE);

    $fields['question'] = FieldDefinition::create('string')
      ->setLabel(t('Question'))
      ->setDescription(t('The poll question.'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', array(
        'type' => 'string',
        'weight' => -100,
      ));

    $fields['langcode'] = FieldDefinition::create('language')
      ->setLabel(t('Language code'))
      ->setDescription(t('The poll language code.'));

    // Poll attributes
    $duration = array(
      // 1-6 days.
      86400,
      2 * 86400,
      3 * 86400,
      4 * 86400,
      5 * 86400,
      6 * 86400,
      // 1-3 weeks (7 days).
      604800,
      2 * 604800,
      3 * 604800,
      // 1-3,6,9 months (30 days).
      2592000,
      2 * 2592000,
      3 * 2592000,
      6 * 2592000,
      9 * 2592000,
      // 1 year (365 days).
      31536000,
    );

    $period = array(0 => t('Unlimited')) + array_map('format_interval', array_combine($duration, $duration));

    $fields['runtime'] = FieldDefinition::create('list_integer')
      ->setLabel(t('Runtime'))
      ->setDescription(t('The number of seconds after creation during which the poll is active.'))
      ->setSetting('unsigned', TRUE)
      ->setRequired(TRUE)
      ->setSetting('allowed_values', $period)
      ->setDisplayOptions('form', array(
        'type' => 'options_select',
        'weight' => 0,
      ));

    $fields['anonymous_vote_allow'] = FieldDefinition::create('list_integer')
      ->setLabel(t('Allow anonymous votes'))
      ->setDescription(t('A flag indicating whether anonymous users are allowed to vote.'))
      ->setSetting('unsigned', TRUE)
      ->setSetting('allowed_values', array(0 => t('No'), 1 => t('Yes')))
      ->setDisplayOptions('form', array(
        'type' => 'options_select',
        'weight' => 1,
      ));

    $fields['cancel_vote_allow'] = FieldDefinition::create('list_integer')
      ->setLabel(t('Allow cancel votes'))
      ->setDescription(t('A flag indicating whether users may cancel their vote.'))
      ->setSetting('allowed_values', array(0 => t('No'), 1 => t('Yes')))
      ->setDisplayOptions('form', array(
        'type' => 'options_select',
        'weight' => 2,
      ));

    $fields['result_vote_allow'] = FieldDefinition::create('list_integer')
      ->setLabel(t('Allow view results'))
      ->setDescription(t('A flag indicating whether users may see the results before voting.'))
      ->setSetting('allowed_values', array(0 => t('No'), 1 => t('Yes')))
      ->setDisplayOptions('form', array(
        'type' => 'options_select',
        'weight' => 3,
      ));

    $fields['status'] = FieldDefinition::create('list_integer')
      ->setLabel(t('Active?'))
      ->setDescription(t('A flag indicating whether the poll is active.'))
      ->setSetting('allowed_values', array(0 => t('No'), 1 => t('Yes')))
      ->setDisplayOptions('form', array(
        'type' => 'options_select',
        'weight' => 4,
      ));

    // This is updated by the fetcher and not when the feed is saved, therefore
    // it's a timestamp and not a changed field.
    $fields['created'] = FieldDefinition::create('timestamp')
      ->setLabel(t('Created'))
      ->setDescription(t('When the poll was created, as a Unix timestamp.'));

    return $fields;
  }

  /**
   *
   * {@inheritdoc}
   */
  public static function sort($a, $b) {
    return strcmp($a->label(), $b->label());
  }


  /**
   * @todo: Refactor - doesn't belong here.
   *
   * @return mixed
   */
  public function hasUserVoted() {
    $poll_storage_controller = \Drupal::entityManager()->getStorage('poll');
    return $poll_storage_controller->getUserVote($this);
  }

  /**
   * Get all options for this poll.
   *
   * @return array
   */
  public function getOptions() {
    $options = array();
    if (count($this->field_choice)) {
      foreach ($this->field_choice as $option) {
        $options[$option->chid] = String::checkPlain($option->choice);
      }
    }
    return $options;
  }

  /**
   * Get the values of each vote option for this poll.
   *
   * @return array
   */
  public function getOptionValues() {
    $options = array();
    if (count($this->field_choice)) {
      foreach ($this->field_choice as $option) {
        $options[$option->chid] = $option->vote;
      }
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public static function postLoad(EntityStorageInterface $storage, array &$entities) {
    foreach ($entities as $entity) {
      $entity->votes = $storage->getVotes($entity);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function postDelete(EntityStorageInterface $storage, array $entities) {
    parent::postDelete($storage, $entities);

    foreach ($entities as $entity) {
      $storage->deleteVotes($entity);
    }
  }

  /**
   * Remove 'entity/' from the generted uri for this entity.
   *
   * @return mixed
   */
  public function normaliseUri() {
    $uri = $this->uri();
    return str_replace('entity/', '', $uri);
  }

}
