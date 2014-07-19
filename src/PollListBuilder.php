<?php

/**
 * Definition of Drupal\poll\PollListController.
 */

namespace Drupal\poll;

use Drupal;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Config\Entity\DraggableListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;

/**
 * Defines a class to build a listing of user role entities.
 *
 * @see \Drupal\user\Entity\Role
 */
class PollListBuilder extends DraggableListBuilder {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'poll_list_form';
  }

  /**
   * Overrides Drupal\Core\Entity\EntityListController::buildHeader().
   */
  public function buildHeader() {

    $header['question'] = t('Question');
    $header['author'] = t('Author');
    $header['votes'] = t('Votes');
    $header['status'] = t('Status');
    $header['created'] = t('Created');
    $header['operations'] = t('Operations');
    return $header + parent::buildHeader();
  }

  /**
   * Overrides Drupal\Core\Entity\EntityListController::buildRow().
   */
  public function buildRow(EntityInterface $entity) {
    $pollStorage = \Drupal::entityManager()->getStorage('poll');

    $row['question'] = l($entity->label(), 'poll/' . $entity->id());
    $row['author']['data'] = array(
      '#theme' => 'username',
      '#account' => $entity->getOwner(),
    );
    $row['votes'] = $pollStorage->getTotalVotes($entity);
    $row['status'] = ($entity->isActive()) ? t('Y') : t('N');
    $row['created'] = ($entity->getCreated()) ? Drupal::service('date')
      ->format($entity->getCreated(), 'long') : t('n/a');
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getOperations(EntityInterface $entity) {
    $operations = parent::getOperations($entity);

    if ($entity->hasLinkTemplate('canonical')) {
      $operations['view'] = array(
          'title' => t('View'),
          'weight' => 0,
        ) + $entity->urlInfo('canonical')->toArray();
    }
    if ($entity->hasLinkTemplate('edit-form')) {
      $operations['edit'] = array(
          'title' => t('Edit'),
          'weight' => 1,
        ) + $entity->urlInfo('edit-form')->toArray();
    }
    if ($entity->hasLinkTemplate('delete-form')) {
      $operations['delete'] = array(
          'title' => t('Delete'),
          'weight' => 2,
        ) + $entity->urlInfo('delete-form')->toArray();
    }
    return $operations;
  }

}
