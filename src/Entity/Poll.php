<?php

/**
 * @file
 * Contains \Drupal\poll\Entity\Poll.
 */

namespace Drupal\poll\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
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
 *   handlers = {
 *     "access" = "\Drupal\poll\PollAccessControlHandler",
 *     "storage" = "Drupal\poll\PollStorage",
 *     "translation" = "Drupal\content_translation\ContentTranslationHandler",
 *     "list_builder" = "Drupal\poll\PollListBuilder",
 *     "view_builder" = "Drupal\poll\PollViewBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "default" = "Drupal\poll\Form\PollForm",
 *       "edit" = "Drupal\poll\Form\PollForm",
 *       "delete" = "Drupal\poll\Form\PollDeleteForm",
 *       "delete_vote" = "Drupal\poll\Form\PollVoteDeleteForm",
 *       "delete_items" = "Drupal\poll\Form\PollItemsDeleteForm",
 *     }
 *   },
 *   links = {
 *     "canonical" = "/poll/{poll}",
 *     "edit-form" = "/poll/{poll}/edit",
 *     "delete-form" = "/poll/{poll}/delete"
 *   },
 *   base_table = "poll",
 *   data_table = "poll_field_data",
 *   admin_permission = "administer polls",
 *   field_ui_base_route = "poll.poll_list",
 *   translatable = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "question",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode"
 *   }
 * )
 */
class Poll extends ContentEntityBase implements PollInterface {

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
  public function isOpen() {
    return (bool) $this->get('status')->value;
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
    return (bool) $this->get('status')->value == 0;
  }

  /**
   * {@inheritdoc}
   */
  public function close() {
    return $this->set('status', 0);
  }

  /**
   * {@inheritdoc}
   */
  public function open() {
    return $this->set('status', 1);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Poll ID'))
      ->setDescription(t('The ID of the poll.'))
      ->setReadOnly(TRUE)
      ->setSetting('unsigned', TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('User ID'))
      ->setDescription(t('The user ID of the poll author.'))
      ->setSetting('target_type', 'user')
      ->setTranslatable(TRUE)
      ->setDefaultValueCallback('Drupal\poll\Entity\Poll::getCurrentUserId');

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The poll UUID.'))
      ->setReadOnly(TRUE);

    $fields['question'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Question'))
      ->setDescription(t('The poll question.'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => -100,
      ));

    $fields['langcode'] = BaseFieldDefinition::create('language')
      ->setLabel(t('Language code'))
      ->setDescription(t('The poll language code.'));

    $fields['choice'] = BaseFieldDefinition::create('poll_choice')
      ->setLabel(t('Choice'))
      ->setDescription(t('Enter a poll choice and default vote.'))
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', [
        'type' => 'poll_choice_default',
        'settings' => [],
        'weight' => -10,
      ]);

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

    $period = array(0 => t('Unlimited')) + array_map(array(\Drupal::service('date.formatter'), 'formatInterval'), array_combine($duration, $duration));

    $fields['runtime'] = BaseFieldDefinition::create('list_integer')
      ->setLabel(t('Runtime'))
      ->setDescription(t('The number of seconds after creation during which the poll is active.'))
      ->setSetting('unsigned', TRUE)
      ->setRequired(TRUE)
      ->setSetting('allowed_values', $period)
      ->setDefaultValue(0)
      ->setDisplayOptions('form', array(
        'type' => 'options_select',
        'weight' => 0,
      ));

    $fields['anonymous_vote_allow'] = BaseFieldDefinition::create('list_integer')
      ->setLabel(t('Allow anonymous votes'))
      ->setDescription(t('A flag indicating whether anonymous users are allowed to vote.'))
      ->setSetting('unsigned', TRUE)
      ->setRequired(TRUE)
      ->setSetting('allowed_values', array(0 => t('No'), 1 => t('Yes')))
      ->setDefaultValue(0)
      ->setDisplayOptions('form', array(
        'type' => 'options_select',
        'weight' => 1,
      ));

    $fields['cancel_vote_allow'] = BaseFieldDefinition::create('list_integer')
      ->setLabel(t('Allow cancel votes'))
      ->setDescription(t('A flag indicating whether users may cancel their vote.'))
      ->setSetting('allowed_values', array(0 => t('No'), 1 => t('Yes')))
      ->setDefaultValue(1)
      ->setRequired(TRUE)
      ->setDisplayOptions('form', array(
        'type' => 'options_select',
        'weight' => 2,
      ));

    $fields['result_vote_allow'] = BaseFieldDefinition::create('list_integer')
      ->setLabel(t('Allow view results'))
      ->setDescription(t('A flag indicating whether users may see the results before voting.'))
      ->setSetting('allowed_values', array(0 => t('No'), 1 => t('Yes')))
      ->setDefaultValue(0)
      ->setRequired(TRUE)
      ->setDisplayOptions('form', array(
        'type' => 'options_select',
        'weight' => 3,
      ));

    $fields['status'] = BaseFieldDefinition::create('list_integer')
      ->setLabel(t('Active?'))
      ->setDescription(t('A flag indicating whether the poll is active.'))
      ->setSetting('allowed_values', array(0 => t('No'), 1 => t('Yes')))
      ->setRequired(TRUE)
      ->setDefaultValue(1)
      ->setDisplayOptions('form', array(
        'type' => 'options_select',
        'weight' => 4,
      ));

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('When the poll was created, as a Unix timestamp.'));

    return $fields;
  }

  /**
   * Default value callback for 'uid' base field definition.
   *
   * @see ::baseFieldDefinitions()
   *
   * @return array
   *   An array of default values.
   */
  public static function getCurrentUserId() {
    return array(\Drupal::currentUser()->id());
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
    if (count($this->choice)) {
      foreach ($this->choice as $option) {
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
    if (count($this->choice)) {
      foreach ($this->choice as $option) {
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

}
