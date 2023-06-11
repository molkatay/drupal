<?php

namespace Drupal\Tests\custom_event\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests the display of the custom event block on the event page.
 *
 * @group custom_event_block
 */
class CustomEventBlockTest extends BrowserTestBase{

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['custom_event_block', 'node'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void
  {
    parent::setUp();

    // Create and log in an administrative user.
    $this->drupalCreateUser(['administer blocks']);
    $this->drupalLogin($this->drupalCreateUser([], 'test_user'));
  }

  /**
   * Tests the display of the custom event block on the event page.
   */
  public function testEventBlockDisplay() {
    // Create an event node.
    $node = $this->drupalCreateNode([
      'type' => 'event',
      'title' => 'Test Event',
    ]);

    // Place the custom block in a region.
    $this->drupalPlaceBlock('custom_event_block', [
      'region' => 'content',
      'id' => 'custom_event_block',
      'label' => 'Custom Event Block',
      'visibility[request_path][pages]' => 'node/' . $node->id(),
    ]);

    // Visit the event node page.
    $this->drupalGet('node/' . $node->id());

    // Assert that the custom block is displayed on the event page.
    $this->assertSession()->pageTextContains('Custom Event Block');
  }

}
