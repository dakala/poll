<?php

/**
 * @file
 * Definition of Drupal\poll\Tests\PollCreateTest.
 */

namespace Drupal\poll\Tests;

/**
 * Tests creating a poll.
 */
class PollCreateTest extends PollTestBase {
  public static function getInfo() {
    return array(
      'name' => 'Poll create',
      'description' => 'Adds "more choices", previews and creates a poll.',
      'group' => 'Poll'
    );
  }

  /**
   * Tests creating, listing, editing a new poll.
   */
  function testPollCreate() {
    $title = $this->randomName();
    $choices = $this->_generateChoices(7);
    $poll_nid = $this->pollCreate($title, $choices, TRUE);

    // Verify poll appears on 'poll' page.
    $this->drupalGet('admin/structure/poll');
    $this->assertText($title, 'Poll appears in poll list.');
    $this->assertText('Y', 'Poll is active.');

    // Click on the poll title to go to node page.
    $this->clickLink($title);

    //We do this later!!
    //$this->assertText('Total votes: 0', 'Link to poll correct.');

    // Now add a new option to make sure that when we update the node the
    // option is displayed.
    $this->drupalGet('poll/' . $poll_nid . '/edit');
    $choiceName = $this->randomName();
    $vote_count = '2000';
    //$this->drupalPostForm(NULL, NULL, t('Add another item'));
    $edit['field_choice[6][choice]'] = $choiceName;
    $edit['field_choice[6][vote]'] = $vote_count;
    $edit['field_choice[6][vote]'] = $vote_count;
    $edit['field_choice[6][_weight]'] = -1;
    $this->drupalPostForm(NULL, $edit, t('Save'));
    $this->assertText(t('@type @title has been updated.', array('@type' => 'poll', '@title' => $title)), 'Poll has been updated.');


    $this->drupalGet('poll/' . $poll_nid . '/edit');
    /* Some Questions!!!
    $this->assertFieldByName('field_choice[0][_weight]', -1, format_string('Found field_choice @id with weight @weight.', array(
      '@id' => '0',
      '@weight' => '-1',
    )));
    */
    $this->clickLink($title);
    $this->assertText($choiceName, 'New option found.');
  }

  /**
   * Tests creating, editing, and closing a poll.
   */
  function testPollClose() {
    $content_user = $this->drupalCreateUser(array('administer polls'));
    $vote_user = $this->drupalCreateUser(array('access polls'));

    // Create poll.
    $title = $this->randomName();
    $choices = $this->_generateChoices(7);
    $poll_nid = $this->pollCreate($title, $choices, FALSE);

    $this->drupalLogout();
    $this->drupalLogin($content_user);

    // Edit the poll entity and close the poll.
    $close_edit = array('status' => 0);
    $this->pollUpdate($poll_nid, $title, $close_edit);

    // Verify 'Vote' button no longer appears.
    $this->drupalGet('poll/' . $poll_nid);
    $elements = $this->xpath('//input[@id="edit-vote"]');
    $this->assertTrue(empty($elements), "Vote button doesn't appear.");

    // Verify status on 'poll' page is 'closed'.
    $this->drupalGet('admin/structure/poll');
    $this->assertText($title, 'Poll appears in poll list.');
    $this->assertText('N', 'Poll is closed.');

    // Edit the poll node and re-activate.
    $open_edit = array('status' => 1);
    $this->pollUpdate($poll_nid, $title, $open_edit);

    // Vote on the poll.
    $this->drupalLogout();
    $this->drupalLogin($vote_user);
    $vote_edit = array('choice' => '1');
    $this->drupalPostForm('poll/' . $poll_nid, $vote_edit, t('Vote'));
    $this->assertText('Your vote has been recorded.', 'Your vote has been recorded.');
    /* This button is not there to be checked
    $elements = $this->xpath('//input[@value="Cancel your vote"]');
    $this->assertTrue(isset($elements[0]), "'Cancel your vote' button appears.");
    */

    // Edit the poll node and close the poll.
    $this->drupalLogout();
    $this->drupalLogin($content_user);
    $close_edit = array('status' => 0);
    $this->pollUpdate($poll_nid, $title, $close_edit);

    // Verify 'Cancel your vote' button no longer appears.
    $this->drupalGet('poll/' . $poll_nid);
    $elements = $this->xpath('//input[@value="Cancel your vote"]');
    $this->assertTrue(empty($elements), "'Cancel your vote' button no longer appears.");
  }
}
