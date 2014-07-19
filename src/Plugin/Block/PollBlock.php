<?php

/**
 * @file
 * Contains \Drupal\poll\Plugin\Block\PollBlock.
 */

namespace Drupal\poll\Plugin\Block;

use Drupal\poll\PollInterface;
use Drupal\poll\PollStorageInterface;
use Drupal\block\BlockBase;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Cache\Cache;
use Symfony\Component\HttpFoundation\Request;
use Drupal\poll\Entity\Poll;

/**
 * Provides a 'Poll' block with the latest poll.
 *
 * @Block(
 *   id = "poll_block",
 *   admin_label = @Translation("Most recent poll"),
 *   category = @Translation("Lists (Views)")
 * )
 */
class PollBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return $account->hasPermission('access polls');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $polls = \Drupal::entityManager()->getStorage('poll')->getMostRecentPoll();
    if ($polls) {
      $poll = reset($polls);
      // If we're viewing this poll, don't show this block.
      $page = \Drupal::request()->attributes->get('poll');
      if($page instanceof PollInterface && $page->id() == $poll->id()) {
        return;
      }
      // @todo: new view mode using ajax
      $output = entity_view($poll, 'block');
      $output['#title'] = $poll->label();
      return $output;
    }

  }

}
