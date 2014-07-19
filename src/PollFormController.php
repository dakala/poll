<?php

/**
 * @file
 * Contains \Drupal\poll\PollFormController.
 */

namespace Drupal\poll;

use Drupal\Component\Utility\String;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Language\Language;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for the poll edit forms.
 */
class PollFormController extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, array &$form_state) {
    $poll = $this->entity;
    // @todo: convert to a language selection widget defined in the base field.
    //   Blocked on https://drupal.org/node/2226493 which adds a generic
    //   language widget.
    $form['langcode'] = array(
      '#title' => $this->t('Language'),
      '#type' => 'language_select',
      '#default_value' => $poll->language()->id,
      '#languages' => Language::STATE_ALL,
      '#weight' => -4,
    );

    return parent::form($form, $form_state, $poll);
  }

  public function buildEntity(array $form, array &$form_state) {
    /** @var \Drupal\poll\PollInterface $entity */
    $entity = parent::buildEntity($form, $form_state);

    if ($entity->isNew()) {
      $entity->setCreated(REQUEST_TIME);
    }
    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function validate(array $form, array &$form_state) {
    $poll = $this->buildEntity($form, $form_state);
    // Check for duplicate titles.
    $poll_storage = $this->entityManager->getStorage('poll');
    $result = $poll_storage->getPollDuplicates($poll);
    foreach ($result as $item) {
      if (strcasecmp($item->label(), $poll->label()) == 0) {
        $this->setFormError('question', $form_state, $this->t('A feed named %feed already exists. Enter a unique question.', array('%feed' => $poll->label())));
      }
    }
    parent::validate($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, array &$form_state) {
    $poll = $this->entity;
    $insert = (bool) $poll->id();
    $poll->save();
    if ($insert) {
      drupal_set_message($this->t('The poll %poll has been updated.', array('%poll' => $poll->label())));
    }
    else {
      watchdog('poll', 'Poll %poll added.', array('%poll' => $poll->label()), WATCHDOG_NOTICE, l($this->t('View'), 'admin/config/services/aggregator'));
      drupal_set_message($this->t('The poll %poll has been added.', array('%poll' => $poll->label())));
    }

    $form_state['redirect_route']['route_name'] = 'poll.poll_list';
  }

}
