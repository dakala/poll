<?php

/**
 * @file
 * Definition of Drupal\poll\Tests\PollDeleteChoiceTest.
 */

namespace Drupal\poll\Tests;

/**
 * Tests the removal of poll choices.
 *
 * @group poll
 */
class PollDeleteChoiceTest extends PollTestBase {

  /**
   * Tests removing a choice from a poll.
   */
  function testChoiceRemoval() {
    // Set up a poll with three choices.
    $choices = array('First choice', 'Second choice', 'Third choice');
    $this->assertTrue($this->poll->id(), 'Poll for choice deletion logic test created.');
    // @TODO need to fix the poll nid and not hard code poll/1

    // Edit the poll, and try to delete first poll choice.
    $this->drupalGet("poll/" . $this->poll->id() . "/edit");
    $edit['choice[0][choice]'] = '';
    $this->drupalPostForm(NULL, $edit, t('Save'));

    // Click on the poll title to go to poll page.
    $this->drupalGet('admin/content/poll');
    $this->clickLink($this->poll->label());

    // Check the first poll choice is deleted, while the others remain.
    $this->assertNoText($this->poll->choice[0]->choice, 'First choice removed.');
    $this->assertText($this->poll->choice[1]->choice, 'Second choice remains.');
    $this->assertText($this->poll->choice[2]->choice, 'Third choice remains.');
  }
}
