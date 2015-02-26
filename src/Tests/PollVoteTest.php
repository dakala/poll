<?php

/**
 * @file
 * Definition of Drupal\poll\Tests\PollVoteTest.
 */

namespace Drupal\poll\Tests;

/**
 * Tests voting on a poll.
 *
 * @group poll
 */
class PollVoteTest extends PollTestBase {

  /**
   * Tests voting on a poll.
   */
  function testPollVote() {

    $this->drupalLogin($this->web_user);

    // Record a vote for the first choice.
    $edit = array(
      'choice' => '1',
    );
    $this->drupalPostForm('poll/' . $this->poll->id(), $edit, t('Vote'));
    $this->assertText('Your vote has been recorded.', 'Your vote was recorded.');
    $this->assertText('Total votes:  1', 'Vote count updated correctly.');
    $elements = $this->xpath('//input[@value="Cancel vote"]');
    $this->assertTrue(isset($elements[0]), "'Cancel your vote' button appears.");

//    $this->drupalGet('poll/' . $this->poll->id() . '/votes');
//    $this->assertText(t('This table lists all the recorded votes for this poll. If anonymous users are allowed to vote, they will be identified by the IP address of the computer they used when they voted.'), 'Vote table text.');
//    $options = $this->poll->getOptions();
//    debug($options);

   // $this->assertText($this->poll->getOptions()[0], 'Vote recorded');

    // Ensure poll listing page has correct number of votes.
//    $this->drupalGet('poll');
//    $this->assertText($this->poll->label(), 'Poll appears in poll list.');
//    $this->assertText('1 vote', 'Poll has 1 vote.');

    // Cancel a vote.
    $this->drupalPostForm('poll/' . $this->poll->id(), array(), t('Cancel vote'));
    $this->assertText("Are you sure you want to delete this vote for " . $this->poll->label(), 'Vote delete confirm form found.');

    $this->drupalPostForm(NULL, array(), t('Delete'));
    $this->assertText('Your vote was cancelled.', 'Your vote was cancelled.');
    $this->assertNoText('Cancel your vote', "Cancel vote button doesn't appear.");

//    $this->drupalGet('poll/' . $this->poll->id() . '/votes');
//    $this->assertNoText($choices[0], 'Vote cancelled');

    // Ensure poll listing page has correct number of votes.
//    $this->drupalGet('poll');
//    $this->assertText($title, 'Poll appears in poll list.');
//    $this->assertText('0 votes', 'Poll has 0 votes.');

    // Log in as a user who can only vote on polls.
//    $this->drupalLogout();
//    $this->drupalLogin($restricted_vote_user);

    // Vote on a poll.
    $edit = array(
      'choice' => '1',
    );
    $this->drupalPostForm('poll/' . $this->poll->id(), $edit, t('Vote'));
    $this->assertText('Your vote has been recorded.', 'Your vote was recorded.');
    $this->assertText('Total votes:  1', 'Vote count updated correctly.');
    $elements = $this->xpath('//input[@value="Cancel your vote"]');
    $this->assertTrue(empty($elements), "'Cancel your vote' button does not appear.");
  }
}
