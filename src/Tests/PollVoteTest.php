<?php

/**
 * @file
 * Definition of Drupal\poll\Tests\PollVoteTest.
 */

namespace Drupal\poll\Tests;

/**
 * Tests voting on a poll.
 */
class PollVoteTest extends PollTestBase {
  public static function getInfo() {
    return array(
      'name' => 'Poll vote',
      'description' => 'Vote on a poll',
      'group' => 'Poll'
    );
  }

  function tearDown() {
    parent::tearDown();
  }

  /**
   * Tests voting on a poll.
   */
  function testPollVote() {
    $title = $this->randomName();
    $choices = $this->_generateChoices(7);
    $poll_nid = $this->pollCreate($title, $choices, FALSE);
    $this->drupalLogout();

    $vote_user = $this->drupalCreateUser(array('administer polls', 'access polls', 'access content'));
    $restricted_vote_user = $this->drupalCreateUser(array('access polls', 'access content'));

    $this->drupalLogin($vote_user);

    // Record a vote for the first choice.
    $edit = array(
      'choice' => '1',
    );
    $this->drupalPostForm('poll/' . $poll_nid, $edit, t('Vote'));
    $this->assertText('Your vote has been recorded.', 'Your vote has been recorded.');
    $this->assertText('Total votes:  1', 'Vote count updated correctly.');

    //There should be a test for the cancel your vote button but this button is not there right now.




    /*There is no such site at the moment
    $this->drupalGet("poll/$poll_nid/votes");
    $this->assertText(t('This table lists all the recorded votes for this poll. If anonymous users are allowed to vote, they will be identified by the IP address of the computer they used when they voted.'), 'Vote table text.');
    $this->assertText($choices[0], 'Vote recorded');
    */

    // Ensure poll listing page has correct number of votes.
    $this->drupalGet('admin/structure/poll');
    $this->assertText($title, 'Poll appears in poll list.');
    $this->assertText('1', 'Poll has 1 vote.'); //This test is not really usefull!!

    // Cancel a vote.
    $this->drupalGet('poll/' . $poll_nid . '/delete/vote/' . $vote_user->id());
    $this->drupalPostForm(NULL, NULL, t('Delete'));
    $this->assertText('The vote has been deleted.', 'The vote has been deleted.');


    // Ensure poll listing page has correct number of votes.
    $this->drupalGet('admin/structure/poll');
    $this->assertText($title, 'Poll appears in poll list.');
    $this->assertText('0', 'Poll has 0 votes.');//This test is not really usefull!!

    // Log in as a user who can only vote on polls.
    $this->drupalLogout();
    $this->drupalLogin($restricted_vote_user);

    // Vote on a poll.
    $edit = array(
      'choice' => '1',
    );
    $this->drupalPostForm('poll/' . $poll_nid, $edit, t('Vote'));
    $this->assertText('Your vote has been recorded.', 'Your vote has been recorded.');
    $this->assertText('Total votes:  1', 'Vote count updated correctly.');
    /*the button is not there yet anyway.
    $elements = $this->xpath('//input[@value="Cancel your vote"]');
    $this->assertTrue(empty($elements), "'Cancel your vote' button does not appear.");
    */
  }
}
