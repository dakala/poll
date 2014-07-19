<?php

/**
 * @file
 * Contains \Drupal\poll\PollAccessController.
 */

namespace Drupal\poll;

use Drupal\Core\Entity\EntityAccessController;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines an access controller for the poll entity.
 *
 * @see \Drupal\poll\Entity\Poll
 */
class PollAccessController extends EntityAccessController {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {
    switch ($operation) {
      case 'view':
        return $account->hasPermission('access polls');
        break;

      case 'update':
        return $account->hasPermission('administer polls');
        break;

      case 'delete':
        return $account->hasPermission('administer polls');
        break;
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return $account->hasPermission('administer polls');
  }

}
