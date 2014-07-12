<?php

/**
 * @file
 * Definition of Drupal\poll\Tests\PollExpirationTest.
 */

namespace Drupal\poll\Tests;

/**
 * Tests the expiration of polls.
 */
class PollExpirationTest extends PollTestBase {
  public static function getInfo() {
    return array(
      'name' => 'Poll expiration',
      'description' => 'Test the poll auto-expiration logic.',
      'group' => 'Poll',
    );
  }

  /**
   * Tests the expiration of a poll.
   */
  function testAutoExpire() {

    // Set up a poll.
    $poll = $this->poll;
    $this->assertTrue($poll->id(), 'Poll for auto-expire test created.');

    // Visit the poll edit page and verify that by default, expiration
    // is set to unlimited.
    $this->drupalLogin($this->admin_user);
    $this->drupalGet('poll/' . $poll->id(). '/edit');
    $this->assertField('runtime', 'Poll expiration setting found.');
    $elements = $this->xpath('//select[@id="edit-runtime"]/option[@selected="selected"]');
    $this->assertTrue(isset($elements[0]['value']) && $elements[0]['value'] == 0, 'Poll expiration set to unlimited.');

    // Set the expiration to one week.
    $runtime = 604800; // One week.
    $poll->setRuntime($runtime);
    $poll->save();

    // Make sure that the changed expiration settings is kept.
    // here
    $this->drupalGet('poll/' . $poll->id(). '/edit');
    $elements = $this->xpath('//select[@id="edit-runtime"]/option[@selected="selected"]');
    $this->assertTrue(isset($elements[0]['value']) && $elements[0]['value'] == $runtime, 'Poll expiration set to one week.');

    // Force a cron run. Since the expiration date has not yet been reached,
    // the poll should remain active.
    $this->cronRun();
    $this->assertTrue($poll->isActive(), 'Poll remains active after cron.');

    $created = $poll->getCreated();
    $offset = $created - ($runtime * 1.01);
    $poll->setCreated($offset);
    $poll->save();

    // Run cron and verify that the poll is now marked as "closed".
    $this->cronRun();
    $loaded_poll = poll_load($poll->id());
    $this->assertTrue($loaded_poll->isClosed(), 'Poll has expired.');
  }
}
