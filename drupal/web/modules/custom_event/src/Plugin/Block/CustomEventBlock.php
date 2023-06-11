<?php

namespace Drupal\custom_event\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a custom block to display related events.
 *
 * @Block(
 *   id = "custom_event_block",
 *   admin_label = @Translation("Custom Event Block"),
 *   category = @Translation("Custom")
 * )
 */
class CustomEventBlock extends BlockBase implements ContainerFactoryPluginInterface
{

  /**
   * The entity type manager.
   *
   * @var EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected RouteMatchInterface $routeMatch;


  /**
   * Constructs a new CustomEventBlock instance.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin_id for the plugin.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\Query\QueryFactory $entity_query_factory
   *   The entity query factory.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManager $entity_type_Manager, RouteMatchInterface $route_match)
  {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_Manager;
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
  {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array
  {
    $build = [];
    $event = $this->routeMatch->getParameter('node');
    if ($event instanceof NodeInterface) {

      $event_list = [];
      $current_date = new \DateTime('now');
      $current_date = DrupalDateTime::createFromDateTime($current_date);



      $event_type = $event->get('field_event_type')->getString();
      $query_same_event_type = $this->entityTypeManager->getStorage('node')->getQuery()
        ->latestRevision()
        ->condition('type', 'event')
        ->condition('field_event_type', $event_type)
        ->condition('status', TRUE)
        ->condition('field_date_range.end_value', $current_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT), '>=')
        ->accessCheck(TRUE)
        ->sort('field_date_range', 'ASC')
        ->range(0, 3);
      $count = $query_same_event_type->count()->execute();


      if ($count <= 3) {
        $query_events = $this->entityTypeManager->getStorage('node')->getQuery()
          ->latestRevision()
          ->condition('status', TRUE)
          ->condition('field_date_range.end_value', $current_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT), '>=')
          ->accessCheck(TRUE)
          ->sort('field_date_range', 'ASC')
          ->range(0, 3);
        $eventIds = $query_events->execute();
      }
      else {
        $eventIds = $query_same_event_type->execute();
      }

      $events = $this->entityTypeManager->getStorage('node')->loadMultiple($eventIds);
      $builder = $this->entityTypeManager->getViewBuilder('node');
      foreach ($events as $event) {
        $event_list [] = $builder->view($event, 'teaser');
      }
      // Render the list of related events.
      if (!empty($events)) {
        $build['related_events'] = [
          '#theme' => 'item_list',
          '#items' => $event_list,
        ];
      }

    }
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags()
  {
    $event = \Drupal::routeMatch()->getParameter('node');
    if ($event && $event->bundle() == 'event') {
      $termIds = $event->field_event_type->referencedEntities();
      if (!empty($termIds)) {
        $termId = $termIds[0]->id();
        return Cache::mergeTags(parent::getCacheTags(), ['taxonomy_term:' . $termId]);
      }
    }
    return parent::getCacheTags();
  }

}
