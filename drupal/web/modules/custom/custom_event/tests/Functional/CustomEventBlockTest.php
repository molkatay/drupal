<?php

namespace Drupal\Tests\custom_event\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests the Custom Event Block.
 *
 * @group custom_event
 */
class CustomEventBlockTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'custom_event',
    'node',
    'block',
    'views',
    'taxonomy',
  ];

  protected $defaultTheme = "test_drupal";
  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create and log in an administrative user.
    $admin_user = $this->drupalCreateUser([
      'administer blocks',
      'administer nodes',
      'administer taxonomy',
    ]);
    $this->drupalLogin($admin_user);
  }

  /**
   * Tests the Custom Event Block.
   */
  public function testCustomEventBlock() {
    // Create a test event node.
    $event = $this->drupalCreateNode([
      'type' => 'event',
      'title' => 'Test Event',
      'status' => TRUE,
    ]);

    // Visit the event page.
    $this->drupalGet('node/' . $event->id());

    $this->assertSession()->pageTextContains('Related Events');
    $this->assertSession()->elementExists('css', '.custom-event-block');


  }

}
