<?php

namespace Drupal\Tests\custom_event\Unit;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\custom_event\Plugin\QueueWorker\EventCronQueueWorker;
use Drupal\node\NodeInterface;

/**
 * Tests the Event Cron QueueWorker.
 *
 * @group custom_event
 */
class EventCronQueueWorkerTest extends UnitTestCase {

  /**
   * The Event Cron QueueWorker object.
   *
   * @var \Drupal\custom_event\Plugin\QueueWorker\EventCronQueueWorker
   */
  protected $queueWorker;

  /**
   * The Node entity mock.
   *
   * @var \Drupal\node\NodeInterface|\Prophecy\Prophecy\ObjectProphecy
   */
  protected $node;

  /**
   * The Entity Type Manager mock.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create mocks for the Node entity and EntityTypeManager.
    $this->node = $this->prophesize(NodeInterface::class);
    $this->entityTypeManager = $this->prophesize(EntityTypeManagerInterface::class);

    // Create the EventCronQueueWorker object.
    $this->queueWorker = new EventCronQueueWorker([], 'event_cron_queue_worker', [], $this->entityTypeManager->reveal());
  }

  /**
   * Tests the processItem() method with a Node entity.
   */
  public function testProcessItemWithNodeEntity() {
    // Set up the Node entity mock.
    $this->node->setUnpublished();
    $this->node->save();

    // Call the processItem() method with the Node entity mock.
    $this->queueWorker->processItem($this->node);

  }

  /**
   * Tests the processItem() method with a non-Node entity.
   */
  public function testProcessItemWithNonNodeEntity() {
    // Create a mock object that is not an instance of NodeInterface.
    $nonNodeEntity = $this->prophesize(\stdClass::class);

    // Call the processItem() method with the non-Node entity mock.
    $this->queueWorker->processItem($nonNodeEntity->reveal());
  }

}
