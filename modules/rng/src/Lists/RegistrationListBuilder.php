<?php

namespace Drupal\rng\Lists;

use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\rng\Entity\RegistrationInterface;
use Drupal\rng\EventManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Builds a list of registrations.
 */
class RegistrationListBuilder extends EntityListBuilder {

  /**
   * The RNG event manager.
   *
   * @var \Drupal\rng\EventManagerInterface
   */
  protected $eventManager;

  /**
   * Row Counter.
   *
   * @var int
   */
  protected $rowCounter;

  /**
   * The event entity.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $event;

  /**
   * Constructs a new RegistrationListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\rng\EventManagerInterface $event_manager
   *   The RNG event manager.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, EventManagerInterface $event_manager) {
    parent::__construct($entity_type, $storage);
    $this->eventManager = $event_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('rng.event_manager')
    );
  }

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\Core\Entity\EntityInterface $rng_event
   *   The event entity to display registrations.
   */
  public function render(EntityInterface $rng_event = NULL) {
    if (isset($rng_event)) {
      $this->event = $rng_event;
    }
    $render = parent::render();
    $render['table']['#empty'] = t('No registrations found for this event.');
    return $render;
  }

  /**
   * {@inheritdoc}
   */
  public function load() {
    if (isset($this->event)) {
      return $this->eventManager->getMeta($this->event)->getRegistrations();
    }
    return parent::load();
  }

  /**
   * {@inheritdoc}
   */
  protected function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);
    if ($entity->access('view') && $entity->hasLinkTemplate('canonical')) {
      $operations['view'] = [
        'title' => $this->t('View'),
        'weight' => 0,
        'url' => $entity->toUrl('canonical'),
      ];
    }
    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['counter'] = '';
    $header['type'] = $this->t('Type');
    $header['groups'] = $this->t('Groups');
    $header['created'] = $this->t('Created');
    return $header + parent::buildHeader();
  }

  /**
   * Builds a row for an entity in the entity listing.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   A registration entity.
   *
   * @return array
   *   A render array structure of fields for this entity.
   *
   * @throws \InvalidArgumentException
   *   In case the entity is not a registration object.
   */
  public function buildRow(EntityInterface $entity) {
    if (!$entity instanceof RegistrationInterface) {
      throw new \InvalidArgumentException(strtr('The passed entity should implement @interface.', [
        '@interface' => RegistrationInterface::class,
      ]));
    }
    $row['counter'] = ++$this->rowCounter;
    $bundle = $this->storage->load($entity->bundle());
    $row['type'] = $bundle ? $bundle->label() : '';

    $row['groups']['data'] = [
      '#theme' => 'item_list',
      '#items' => [],
      '#attributes' => ['class' => ['inline']],
    ];
    foreach ($entity->getGroups() as $group) {
      if ($group->isUserGenerated()) {
        $row['groups']['data']['#items'][] = $group->label();
      }
      else {
        $row['groups']['data']['#items'][] = '<em>' . $group->label() . '</em>';
      }
    }

    $row['created'] = \Drupal::service('date.formatter')->format($entity->created->value);
    return $row + parent::buildRow($entity);
  }

}
